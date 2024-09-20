<?php
/***********
* Recup avis
************/
function sag_recup_avis() {
    //Define SAG url
    $sagUrl = "https://www.societe-des-avis-garantis.fr/";
    //Get vars to send to SAG API
    $productID = null !== 'productID' ? $_GET['productID'] : false;
    $idSAG = null !== 'idSAG' ? $_GET['idSAG'] : false;
    $minDate = null !== 'minDate' ? $_GET['minDate'] : false ;
    $maxDate = null !== 'maxDate' ? $_GET['maxDate'] : false ;
    $maxResults = null !== 'maxR' ? $_GET['maxR'] : false;
    $token = null !== 'token' ? $_GET['token'] : false;
    $from = null !== 'from' ? $_GET['from'] : false;
    $update = null !== 'update' ? $_GET['update'] : false;
    //Build URL (as we pass datas through GET method)
    $productID = $productID ? '&productID='.$productID : ''; //Filter on product ID
    $idSAG = $idSAG ? '&idSAG='.$idSAG : ''; //Filter on SAG unique review ID
    $apiKey="9257/fr/e47d02180e0af593c7cc311cc05be1d4c9a13627d5ff20badf1382163a902096";
    $from = $from ? '&from='.$from : ''; //If from = 1, we only get reviews starting from idSAG
    $minDate = $minDate ? '&minDate='.$minDate : ''; //Filter on review date
    $maxDate = $maxDate ? '&maxDate='.$maxDate : ''; //Filter on review date
    $maxResults = $maxResults ? '&maxR='.$maxResults : ''; //Max results to display
    $token = $token ? '&token='.$token : ''; //token to check if SAG asked product reviews update
    $apiUrl= (string)$sagUrl."wp-content/plugins/ag-core/api/reviews.php?apiPost=1" . $productID . $idSAG . $from . $minDate .
    $maxDate . $maxResults . $token;
    //Send reviews request to SAG API
    $ch = curl_init();
    $timeout = 10; //Timeout in seconds
    curl_setopt ($ch, CURLOPT_URL, $apiUrl);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, "apiKey=".urlencode($apiKey));
    curl_setopt ($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
    curl_setopt ($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
    $reviews = curl_exec($ch);
    curl_close($ch);
    //Cleaning datas
    $file_contentsWithoutBom=removeBOM($reviews);
    if ($file_contentsWithoutBom) $reviews=$file_contentsWithoutBom;
    //Decoding datas
    $reviews=json_decode($reviews, true); //Décodage du contenu JSON récupéré
    //Checking potential errors
    if (json_last_error()) {
        var_dump(json_last_error());
    }
    //For each imported review
    foreach ($reviews as $review) {
        //Initialize var to know if we will have to update review's average rating
        $updateAverage = 0;
        //If review status is 0 (awaiting approval) or 2 (moderated)
        if ($review["review_status"]==0 or $review["review_status"]==2) {
            //If we have it in our DB we have to delete it
            if ($idSAG = wcsag_get_review_id( $review['idSAG'] )) {
                // Supprimer le post avec l'ID $idSAG
                wp_delete_post( $review['idSAG'], true );
                $updateAverage = 1; //Set this var to 1 to update review's average rating
            }
        }
        //Else, if review status is 1 (validated)
        elseif ($review["review_status"]==1) {
            //If we have it in our DB and update is true
            if ($idSAG = wcsag_get_review_id( $review['idSAG'] ) and $update) {
                wp_update_post( array_merge( array( 'ID' => $review['idSAG'] ), $review ) );
                $updateAverage = 1; //Set this var to 1 to update review's average rating
            }
            //Else if review doesn't exists
            elseif (!$idSAG = wcsag_get_review_id( $review['idSAG'] )) {
                wp_insert_post( $review );
                $updateAverage = 1; //Set this var to 1 to update review's average rating
            }
        }
        if ($updateAverage) {
            wcsag_update_average_ratings();
        }
    }
}

//permet de verif si il existe dj un avis avec le mm id
function wcsag_get_review_id( $review_id ) {
    $args = array(
       'fields'      => 'ids',
       'post_type'   => 'wcsag_review',
       'post_status' => 'any',
       'meta_query'  => array(
            array(
                'key'   => '_wcsag_id',
                'value' => $review_id
            )
        )
    );
    $query = new WP_Query( $args );
    return $query->have_posts() ? $query->posts[0] : false;
}

// je penses = yourFunctionToUpdateCacheAverageRating
function wcsag_update_average_ratings() {
    global $wpdb;
    // Compute average in SQL
    $sql = "SELECT $wpdb->posts.post_parent as product_id, AVG($wpdb->postmeta.meta_value) as average
            FROM $wpdb->posts
            LEFT JOIN $wpdb->postmeta
            ON $wpdb->posts.ID = $wpdb->postmeta.post_id
            AND $wpdb->postmeta.meta_key = '_wcsag_rating'
            WHERE $wpdb->posts.post_status = 'publish'
            AND $wpdb->posts.post_type = 'wcsag_review'
            GROUP BY $wpdb->posts.post_parent";
    $ratings = $wpdb->get_results( $sql );
    $updated_product_ids = array();
    foreach ( $ratings as $rating ) {
        $distributions = array( 1 => 0, 2 => 0, 3 => 0, 4 => 0, 5 => 0 );
        $count = 0;
        // Compute rating disribution per product
        $sql = "SELECT FLOOR($wpdb->postmeta.meta_value) as note, COUNT(*) as count
                FROM $wpdb->posts
                LEFT JOIN $wpdb->postmeta
                ON $wpdb->posts.ID = $wpdb->postmeta.post_id
                AND $wpdb->postmeta.meta_key = '_wcsag_rating'
                WHERE $wpdb->posts.post_status = 'publish'
                AND $wpdb->posts.post_type = 'wcsag_review'
                AND $wpdb->posts.post_parent = %d
                GROUP BY 1";
        $results = $wpdb->get_results( $wpdb->prepare( $sql, $rating->product_id ) );
        foreach ( $results as $result ) {
            $distributions[$result->note] = (int)$result->count;
            $count += (int)$result->count;
        }
        update_post_meta( $rating->product_id, '_wcsag_rating', array( 'average' => $rating->average, 'distribution' => $distributions, 'count' => $count ) );
        $updated_product_ids[] = $rating->product_id;
    }
    if ( count( $updated_product_ids ) > 0 ) {
        $ids_placeholder = implode( ',', array_fill( 0, count( $updated_product_ids ), '%d' ) );
        // Delete all previous average ratings data
        $sql = "DELETE $wpdb->postmeta
                FROM $wpdb->postmeta
                LEFT JOIN $wpdb->posts
                ON $wpdb->posts.ID = $wpdb->postmeta.post_id
                WHERE $wpdb->postmeta.meta_key = '_wcsag_rating'
                AND $wpdb->posts.post_type = 'product'
                AND $wpdb->postmeta.post_id NOT IN ($ids_placeholder)";
        $wpdb->query( $wpdb->prepare( $sql, $updated_product_ids ) );
    }
}

