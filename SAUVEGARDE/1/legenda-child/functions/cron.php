<?php

if( !defined( 'ABSPATH' ) )
die( 'Cheatin\' uh?' );
/**
 *************
 * CRON SYSTEM
 *************
 */

if ( wp_doing_cron() ) {

    /**
     * Ajouter les taches cron personnalisées
     * >>> "every_two_days" 172800 seconds
     */
    add_action( 'wp', 'add_custom_cron_job' );
    function add_custom_cron_job() {
        if ( ! wp_next_scheduled( 'invoice_woocommerce_send_email_trigger' ) ) {
            wp_schedule_event( time(), 'every_two_days', 'invoice_woocommerce_send_email_trigger' );
        }
        if ( ! wp_next_scheduled( 'reviews_woocommerce_send_email_trigger' ) ) {
            wp_schedule_event( time(), 'weekly', 'reviews_woocommerce_send_email_trigger' );
        }
    }

    /**
     * Envoyer la facture qd commande terminée +2 jours
     */
    add_action( 'invoice_woocommerce_send_email_trigger', 'invoice_woocommerce_send_email' );
    function invoice_woocommerce_send_email() {
        if ( is_staging() ) {
            return;
        }
        global $wpdb;
        $range = 2; // 2 days
        $completed_orders = $wpdb->get_col(
            $wpdb->prepare("SELECT posts.ID
             FROM {$wpdb->prefix}posts AS posts
             WHERE posts.post_type = 'shop_order'
             AND posts.post_status = 'wc-completed'
             AND posts.post_modified >= '%s'
             AND posts.post_modified <= '%s'",
                date( 'Y/m/d H:i:s', absint( strtotime( '-' . absint( $range ) . ' DAYS', current_time( 'timestamp' ) ) ) ),
                date( 'Y/m/d H:i:s', absint( current_time( 'timestamp' ) ) )
            )
        );
        if ( $completed_orders && class_exists('VosfacturesDatabase') ) {
            $db = new VosfacturesDatabase();
            foreach ( $completed_orders as $order_id ) {
                $invoice = $db->get_last_invoice( $order_id );
                if ( ! empty( $invoice ) && ! empty( $invoice->external_id ) && empty( $invoice->error ) ) {
                    $order = wc_get_order( $order_id );
                    $subject = '[Armoire PLUS] Votre facture disponible sur Armoireplus.fr';
                    $order_client = $order->get_shipping_first_name() .' '. $order->get_shipping_last_name();
                    $order_number = get_post_meta( $order->get_id(), '_ywson_custom_number_order_complete', true );
                    $message = sprintf( __( 'Bonjour %s,' ), $order_client ) . "<br><br>";
                    $message .= sprintf( __( 'Nous avons le plaisir de vous informer que la facture de la commande %s est disponible dans votre espace client sur <a href="https://www.armoireplus.fr">armoireplus.fr</a>' ), $order_number ) . "<br><br>";
                    $message .= "Toute l'équipe d'Armoire PLUS vous remercie pour votre achat.<br><br>";
                    $message .= "À votre disposition,<br><br>";
                    $message .= "<div style='float:left;'>";
                    $message .= "<img src='https://www.armoireplus.fr/wp-content/uploads/signature/signature-armoireplus.png' alt='Logo Armoire PLUS' width='198' height='55' style='margin:0 10px 0 0;'>";
                    $message .= "</div>";
                    $message .= "<div style='float:left;float:left;border-left: 1px solid #dadada;padding-left:15px;margin-top:5px;'>";
                    $message .= "<span style='font-size:10pt'><strong style='font-weight:600;'>Jean-Baptiste - <span style='color:#ce1427'>Armoire PLUS</span></strong></span><br>";
                    $message .= "<span style='font-size:8pt'>Tel. : 05.31.61.98.32 / Fax : 05.17.47.54.02</span><br>";
                    $message .= "<span style='font-size:8pt'>Email : <a href='mailto:contact@armoireplus.fr'>contact@armoireplus.fr</a> / Site : <a href='http://www.armoireplus.fr/'>www.armoireplus.fr</a></span>";
                    $message .= "</div>";
                    $headers[] = 'From: Armoire PLUS <contact@armoireplus.fr>';
                    $headers[] = 'Bcc: compta@armoireplus.fr';
                    wp_mail( $order->get_billing_email(), $subject, $message, $headers );
                }
            }
        }
    }
function send_order_status_report() {
    global $wpdb;
    $range = 2; // 2 days
    error_log("Début de la fonction send_order_status_report");

    // Récupération des commandes terminées dans les 2 derniers jours
    $completed_orders = $wpdb->get_col(
        $wpdb->prepare(
            "SELECT posts.ID
             FROM {$wpdb->prefix}posts AS posts
             WHERE posts.post_type = 'shop_order'
             AND posts.post_status = 'wc-completed'
             AND posts.post_modified >= '%s'
             AND posts.post_modified <= '%s'",
            date('Y/m/d H:i:s', absint(strtotime('-' . $range . ' DAYS', current_time('timestamp')))),
            date('Y/m/d H:i:s', absint(current_time('timestamp')))
        )
    );

    error_log("Commandes complétées récupérées : " . print_r($completed_orders, true));
    $sent_emails = [];
    $unsent_emails = [];
    if (class_exists('VosfacturesDatabase')) {
        $db = new VosfacturesDatabase();
        foreach ($completed_orders as $order_id) {
            error_log("Traitement de la commande ID : " . $order_id);
            $invoice = $db->get_last_invoice($order_id);
            if (!empty($invoice) && !empty($invoice->external_id) && empty($invoice->error)) {
                // Ajouter à la liste des e-mails envoyés
                $sent_emails[] = $order_id;
                error_log("Email envoyé pour la commande ID : " . $order_id);
            } else {
                // Ajouter à la liste des e-mails non envoyés
                $unsent_emails[] = $order_id;
                error_log("Email non envoyé pour la commande ID : " . $order_id);
            }
        }
    } else {
        error_log("La classe VosfacturesDatabase n'est pas disponible.");
    }
    // Préparation de l'email
    $subject = 'Rapport d\'envoi des factures - Armoire PLUS';
    $message = "Rapport des commandes :\n\n";
    $message .= "Mail factures envoyées:\n" . implode(', ', $sent_emails) . "\n\n";
    $message .= "Mail factures non envoyées:\n" . implode(', ', $unsent_emails) . "\n";
    // Envoi de l'email
    wp_mail('ndrouet@armoireplus.fr', $subject, $message);
    error_log("Email de rapport envoyé.");
}
// Planifier l'exécution de la fonction
if (!wp_next_scheduled('send_order_status_report_cron_job')) {
    wp_schedule_event(time(), 'daily', 'send_order_status_report_cron_job');
}
add_action('send_order_status_report_cron_job', 'send_order_status_report');
}