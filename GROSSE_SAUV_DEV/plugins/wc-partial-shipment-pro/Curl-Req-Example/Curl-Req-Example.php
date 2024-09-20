<?php

function sc_curl_post_req($url,$data){
    $curl = curl_init();
    curl_setopt($curl,CURLOPT_URL,$url);
    curl_setopt($curl,CURLOPT_POST,true);
    curl_setopt($curl,CURLOPT_POSTFIELDS,http_build_query($data));
    curl_setopt($curl,CURLOPT_RETURNTRANSFER,true);
    $result = curl_exec($curl);
    curl_close($curl);
    return json_decode($result,true);
}

//You can get this url with key from  Woocommerce >> Settings >> Partial Shipment Setting Tab
$url = 'https://your-site.com/wp-json/wxp-shipment-data/wxp-data/?key=your-key';

// Get Partial Shipment Data using CURL Example:
$data_get = array(
    'order-id'=>10,
    'data'=>'get'
);
$json = sc_curl_post_req($url,$data_get);
echo '<pre>'; print_r($json); echo '</pre>';

// Set Partial Shipment Data using CURL Example:
$data_set = array(
    'order-id'=>10,
    'data'=>'set',
    'items'=>array(
        array(
            'sku'=>'PRO-7',
            'type'=>'shipped',
            'shipped'=>1,
        )
    )
);
$json = sc_curl_post_req($url,$data_set);
echo '<pre>'; print_r($json); echo '</pre>';



?>