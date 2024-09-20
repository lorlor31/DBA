<?php

class WC_SAG_API_Review_Import extends WC_SAG_API_Abstract_Route {
    /** @var string Route slug */
    protected $route = '/reviews/import';

    /** @var string Query var */
    protected $query_var = 'wcsag_reviews_import';

    /**
     * Run the endpoint
     */
    protected function run() {
        // Get parameters
        $params = $this->validate_request();

        // Build full URL
        $url = add_query_arg( array(
            'token'      => $params['token'],
            'apiPost'    => 1,
            'productID'  => $params['product_id'],
            'idSAG'      => $params['sag_id'],
            'from'       => $params['from'],
            'minDate'    => $params['min_date'],
            'maxDate'    => $params['max_date'],
            'maxR'       => $params['max_results'],
            'update'     => $params['update'],
            'lang'       => $params['lang'],

        ), $this->settings->get_sag_api_url( $params['lang'] ) . 'reviews.php' );

        // Request SAG to get reviews
        $response = wp_remote_post( esc_url_raw( $url ), array(
            'body'    => 'apiKey=' .$this->settings->guess_api_key_for_language( $params['lang'] ),
            'timeout' => 30,
        ) );

        // Decode response
        if ( $reviews = json_decode( wp_remote_retrieve_body( $response ), true ) ) {
            
            // Sync received reviews
            foreach ( $reviews as $review ) {
                $this->sync_review( $review, $params['update'] );
            }
            // We need to update average rating on products
            wcsag_update_average_ratings();
        }
    }

    /**
     * Validate and sanitize request 
     */
    protected function validate_request() {
        // Parameters default values
        return array(
            'token'       => isset( $_GET['token'] ) ? $_GET['token'] : '',
            'product_id'  => isset( $_GET['productID'] ) ? $_GET['productID'] : '',
            'sag_id'      => isset( $_GET['idSAG'] ) ? $_GET['idSAG'] : '',
            'from'        => isset( $_GET['from'] ) ? $_GET['from'] : '',
            'min_date'    => isset( $_GET['minDate'] ) ? $_GET['minDate'] : '',
            'max_date'    => isset( $_GET['maxDate'] ) ? $_GET['maxDate'] : '',
            'max_results' => isset( $_GET['maxR'] ) ? $_GET['maxR'] : '',
            'update'      => isset( $_GET['update'] ) ? $_GET['update'] : '',
            'lang'        => isset( $_GET['lang'] ) ? $_GET['lang'] : '',
        );
    }

    /**
     * Synchronize reviews with local reviews
     */
    protected function sync_review( $review_data, $force_update = false ) {
        $post_data = array(
            'post_type'    => 'wcsag_review',
            'post_name'    => "Comment #{$review_data['idSAG']}",
            'post_title'   => "Comment #{$review_data['idSAG']}",
            'post_content' => $review_data['review_text'],
            'post_date'    => $review_data['date_time'],
            'post_status'  => $review_data['review_status'] == '1' ? 'publish' : ( $review_data['review_status'] == '0' ? 'pending' : 'trash' ),
            'post_parent'  => $review_data['idProduct']
        );
        $post_meta = array(
            '_wcsag_id'          => $review_data['idSAG'],
            '_wcsag_rating'      => $review_data['review_rating'],
            '_wcsag_firstname'   => $review_data['reviewer_name'],
            '_wcsag_lastname'    => $review_data['lastname'],
            '_wcsag_answer_text' => $review_data['answer_text'],
            '_wcsag_answer_date' => $review_data['answer_date_time'],
            '_wcsag_order_date'  => $review_data['order_date'],
            '_wcsag_lang'        => $review_data['lang']
        );
        // Update post if already exists
        if ( $post_id = wcsag_get_review_id( $review_data['idSAG'] ) ) {
            // Only update if requested
            if ( $force_update ) {
                wp_update_post( array_merge( array( 'ID' => $post_id ), $post_data ) );
            }
        }
        // Otherwise create it
        else {
            $post_id = wp_insert_post( $post_data );
        }

        foreach ( $post_meta as $meta_key => $meta_value ) {
            update_post_meta( $post_id, $meta_key, $meta_value );
        }
    }
}