<?php

// You can set these statuses 'not-shipped' , 'shipped' , 'partially-shipped'
$order_id = 60; // Order Id
$items = array(
	array(
		'sku'=>'SKU-1',    // You can use 'item_id' or 'sku'
		'type'=>'shipped', //'not-shipped' , 'shipped' , 'partially-shipped'
		'shipped'=>1,     // Quantity what you want to set as shipped
	),
	array(
		'sku'=>'SKU-2',
		'type'=>'shipped',
		'shipped'=>1,
	)
);

$shipment_data = WXP_Partial_Shipment_Init_Pro()->get_wxp_shipment_data($order_id);
if(is_array($items) && !empty($items)){
	foreach($items as $item){
		$wxp_shipment = WXP_Partial_Shipment_Init_Pro()->set_shipment($item,$wxp_shipment);
	}
}
//Trigger this hook at end, so it will queue the partial shipment email.
do_action('add_email_in_wxp_queue',$order_id);