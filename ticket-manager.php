<?php
/*
Plugin Name: Ticket Manager for CF7
Description: Zarządzanie biletami dla formularzy Contact Form 7.
Version: 1.8
Author: WPPoland
Author URI: https://www.wppoland.com/
*/

function manage_tickets_menu()
{
    add_menu_page(
        'Zarządzanie biletami',
        'Zarządzanie biletami',
        'manage_options',
        'manage-tickets',
        'manage_tickets_page',
        'dashicons-tickets-alt',
        6
    );
}
add_action('admin_menu', 'manage_tickets_menu');

function manage_tickets_page()
{
    $tickets_left_form1 = get_option('tickets_left_form1', 100);
    $tickets_left_form2 = get_option('tickets_left_form2', 600);

    if (isset($_POST['submit'])) {
        $tickets_left_form1 = intval($_POST['tickets_left_form1']);
        $tickets_left_form2 = intval($_POST['tickets_left_form2']);

        update_option('tickets_left_form1', $tickets_left_form1);
        update_option('tickets_left_form2', $tickets_left_form2);

        echo '<div id="message" class="updated notice is-dismissible"><p>Zapisano zmiany.</p></div>';
    }

    ?>
    <div class="wrap">
        <h1>Zarządzanie biletami</h1>
        <form method="POST">
            <label for="tickets_left_form1">Dostępne bilety - Formularz 100 biletów:</label>
            <input type="number" name="tickets_left_form1" value="<?php echo esc_attr($tickets_left_form1); ?>" /><br>

            <label for="tickets_left_form2">Dostępne bilety - Formularz 600 biletów:</label>
            <input type="number" name="tickets_left_form2" value="<?php echo esc_attr($tickets_left_form2); ?>" /><br>

            <input type="submit" name="submit" value="Zapisz zmiany" class="button button-primary" />
        </form>
    </div>
    <?php
}

function update_ticket_count($contact_form)
{
    $form_id = $contact_form->id();
    $adult_tickets = isset($_POST['adult_tickets']) ? intval($_POST['adult_tickets']) : 0;
    $minor_tickets = isset($_POST['minor_tickets']) ? intval($_POST['minor_tickets']) : 0;
    $total_tickets = $adult_tickets + $minor_tickets;

    error_log("Form ID: $form_id, Adult Tickets: $adult_tickets, Minor Tickets: $minor_tickets, Total Tickets: $total_tickets");

    if ($total_tickets > 0) {
        if ($form_id == '5') { // Formularz 100 biletów (ID 5)
            $tickets_left = get_option('tickets_left_form1', 100);
            error_log("Current tickets_left_form1: $tickets_left");
            if ($tickets_left >= $total_tickets) {
                $new_tickets_left = $tickets_left - $total_tickets;
                update_option('tickets_left_form1', $new_tickets_left);
                error_log("Updated tickets_left_form1: $new_tickets_left");
            } else {
                error_log("Not enough tickets left for form1");
            }
        } elseif ($form_id == '835') { // Formularz 600 biletów (ID 835)
            $tickets_left = get_option('tickets_left_form2', 600);
            error_log("Current tickets_left_form2: $tickets_left");
            if ($tickets_left >= $total_tickets) {
                $new_tickets_left = $tickets_left - $total_tickets;
                update_option('tickets_left_form2', $new_tickets_left);
                error_log("Updated tickets_left_form2: $new_tickets_left");
            } else {
                error_log("Not enough tickets left for form2");
            }
        }
    } else {
        error_log("No tickets were selected.");
    }
}

add_action('wpcf7_mail_sent', 'update_ticket_count');

function enqueue_ticket_selection_script()
{
    ?>
    <script type="text/javascript">
        document.addEventListener('DOMContentLoaded', function () {
            var adultTickets = document.getElementById('adult-tickets');
            var minorTickets = document.getElementById('minor-tickets');

            function updateMinorTicketOptions() {
                var selectedAdults = parseInt(adultTickets.value);
                var maxMinors = 5 - selectedAdults;

                for (var i = 0; i <= 5; i++) {
                    minorTickets.options[i].disabled = (i > maxMinors);
                }

                if (minorTickets.value > maxMinors) {
                    minorTickets.value = maxMinors;
                }
            }

            adultTickets.addEventListener('change', updateMinorTicketOptions);
            updateMinorTicketOptions();
        });
    </script>
    <?php
}
add_action('wp_footer', 'enqueue_ticket_selection_script');
?>
