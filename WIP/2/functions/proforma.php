<?php
function generate_pdf_proforma( $order_id, $preview = false ) {

    remove_action( 'yith_ywraq_quote_template_header', array( YITH_Request_Quote_Premium(), 'pdf_header' ), 10, 1 );
    remove_action( 'yith_ywraq_quote_template_footer', array( YITH_Request_Quote_Premium(), 'pdf_footer' ), 10, 1 );
    remove_action( 'yith_ywraq_quote_template_content', array( YITH_Request_Quote_Premium(), 'pdf_content' ), 10, 1 );

    add_action( 'yith_ywraq_quote_template_header', 'pdf_header_proforma', 11, 1 );
    add_action( 'yith_ywraq_quote_template_footer', 'pdf_footer_proforma', 11, 1 );
    add_action( 'yith_ywraq_quote_template_content', 'pdf_content_proforma', 11, 1 );

    ob_start();
    wc_get_template( 'pdf/quote.php', array( 'order_id' => $order_id ), '', YITH_YWRAQ_TEMPLATE_PATH . '/' );
    $html = ob_get_contents();
    ob_end_clean();

    require_once YITH_YWRAQ_DIR . 'lib/mpdf/autoload.php';

    $mpdf_args = apply_filters(
        'ywraq_mpdf_args',
        array(
            'autoScriptToLang'  => true,
            'autoLangToFont'    => true,
            'default_font'      => 'dejavusans',
            'default_font_size' => 12,
        )
    );

    // to debug the pdf file.
    if ( isset( $_GET['ywraq_debug_pdf'] ) ) {
        echo $html;
        die;
    }

    global $mpdf;

    if ( is_array( $mpdf_args ) ) {
        $mpdf = new \Mpdf\Mpdf( $mpdf_args );
    } else {
        $mpdf = new \Mpdf\Mpdf();
    }

    $direction                  = is_rtl() ? 'rtl' : 'ltr';
    $mpdf->directionality       = apply_filters( 'ywraq_mpdf_directionality', $direction );
    $mpdf->shrink_tables_to_fit = 1;

    $mpdf->WriteHTML( $html );

    $pdf = $mpdf->Output( 'document', 'S' );
    $file_path = get_pdf_file_path_proforma( $order_id, true );

    if ( ! file_exists( $file_path ) ) {
        $file_path = get_pdf_file_path_proforma( $order_id, false );
    } else {
        unlink( $file_path );
    }

    $file = fopen($file_path, "a"); //phpcs:ignore
    fwrite( $file, $pdf ); //phpcs:ignore
    fclose( $file ); //phpcs:ignore

    return $file_path;
}

function get_pdf_file_path_proforma( $order_id, $delete_file = false ) {
    $path = apply_filters( 'ywraq_pdf_file_path', YITH_Request_Quote_Premium()->create_storing_folder( $order_id ), $order_id );
    $file = YITH_YWRAQ_DOCUMENT_SAVE_DIR . $path . get_pdf_file_name_proforma( $order_id );
    // delete the document if exists.
    if ( file_exists( $file ) && $delete_file ) {
        @unlink( $file ); //phpcs:ignore
    }
    return $file;
}

function get_pdf_file_name_proforma( $order_id ) {
    $pdf_file_name = '';
    $order         = wc_get_order( $order_id );
    if ( $order ) {
        $format               = 'proforma_%rand%';
        $ywraq_customer_email = $order->get_meta( 'ywraq_customer_email' );
        $quote_number         = $order->get_order_number();
        $pdf_file_name = str_replace( '%rand%', md5( $order_id . $ywraq_customer_email ), $format );
        $pdf_file_name = str_replace( '%quote_number%', $quote_number, $pdf_file_name );
        $pdf_file_name = $pdf_file_name . '.pdf';
    }
    return apply_filters( 'ywraq_pdf_file_name', $pdf_file_name, $order_id );
}

function pdf_header_proforma( $order_id ) {
    $order = wc_get_order( $order_id );
    wc_get_template( 'pdf/proforma-header.php', array( 'order' => $order ), '', YITH_YWRAQ_TEMPLATE_PATH . '/' );
}

function pdf_footer_proforma( $order_id ) {
    $footer_content  = get_option( 'ywraq_pdf_footer_content' );
    $show_pagination = get_option( 'ywraq_pdf_pagination' );
    wc_get_template(
        'pdf/proforma-footer.php',
        array(
            'footer'     => $footer_content,
            'pagination' => $show_pagination,
            'order_id'   => $order_id,
        ),
        '',
        YITH_YWRAQ_TEMPLATE_PATH . '/'
    );
}

function pdf_content_proforma( $order_id ) {
    $order    = wc_get_order( $order_id );
    $template = get_option( 'ywraq_pdf_template', 'table' );
    wc_get_template( 'pdf/proforma-table.php', array( 'order' => $order ), '', YITH_YWRAQ_TEMPLATE_PATH . '/' );
}