<?php 
	$post_types = array(
		'portfolio',
		'brands',
		'staticblocks'
	);

	foreach ($post_types as $key) {
		require_once( 'post-types/'.$key.'.php' );
	}
?>