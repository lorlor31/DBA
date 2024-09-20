<?php
$ecommerce = 76;
$args     = array(
	'name'        => 'Footer bottom',
	'post_type'   => 'staticblocks',
	'post_status' => 'publish',
	'numberposts' => 1
);

$my_posts = get_posts( $args );
if ( $my_posts ) :
	$ecommerce = $my_posts[0]->ID;
endif;

return array(
	'ecommerce' => array(
		'footer2' => array(
			'flush' => true,
			'create' => false,
			'widgets' => array(
				'etheme-static-block' => array(
					'block_id' => $ecommerce
				),
			)
		),
		'prefooter' => array(
			'flush' => true,
			'create' => false,
			'widgets' => array(
				'etheme-static-block' => array(
					'block_id' => 77
				),
			)
		),
		'top-panel-sidebar' => array(
			'flush' => true,
			'create' => false,
			'widgets' => array(
				'etheme-static-block' => array(
					'block_id' => 78
				),
			)
		),
	),
	'dark' => array(
		'footer2' => array(
			'flush' => true,
			'create' => false,
			'widgets' => array(
				'etheme-static-block' => array(
					'block_id' => 76
				),
			)
		),
		'prefooter' => array(
			'flush' => true,
			'create' => false,
			'widgets' => array(
				'etheme-static-block' => array(
					'block_id' => 77
				),
			)
		),
		'top-panel-sidebar' => array(
			'flush' => true,
			'create' => false,
			'widgets' => array(
				'etheme-static-block' => array(
					'block_id' => 78
				),
			)
		),
	),
	'corporate' => array(
		'footer2' => array(
			'flush' => true,
			'create' => false,
			'widgets' => array(
				'etheme-static-block' => array(
					'block_id' => 77
				),
			)
		),
		'prefooter' => array(
			'flush' => true,
			'create' => false,
			'widgets' => array(
				'etheme-static-block' => array(
					'block_id' => 76
				),
			)
		),
		'top-panel-sidebar' => array(
			'flush' => true,
			'create' => false,
			'widgets' => array(
				'etheme-static-block' => array(
					'block_id' => 78
				),
			)
		),
	),
	'onepage' => array(
		'footer1' => array(
			'flush' => true,
			'create' => false,
			'widgets' => array(				
			)
		),
		'footer2' => array(
			'flush' => true,
			'create' => false,
			'widgets' => array(
				'etheme-static-block' => array(
					'block_id' => 9520
				),
			)
		),
		'prefooter' => array(
			'flush' => true,
			'create' => false,
			'widgets' => array(
			)
		),
		'top-panel-sidebar' => array(
			'flush' => true,
			'create' => false,
			'widgets' => array(
				'etheme-static-block' => array(
					'block_id' => 78
				),
			)
		),
	),
	'parallax' => array(
		'footer1' => array(
			'flush' => true,
			'create' => false,
			'widgets' => array(				
			)
		),
		'footer2' => array(
			'flush' => true,
			'create' => false,
			'widgets' => array(
				'etheme-static-block' => array(
					'block_id' => 9557
				),
			)
		),
		'prefooter' => array(
			'flush' => true,
			'create' => false,
			'widgets' => array(
			)
		),
		'top-panel-sidebar' => array(
			'flush' => true,
			'create' => false,
			'widgets' => array(
				'etheme-static-block' => array(
					'block_id' => 78
				),
			)
		),
	),
	'left_sidebar' => array(
		'footer1' => array(
			'flush' => true,
			'create' => false,
			'widgets' => array(				
			)
		),
		'footer2' => array(
			'flush' => true,
			'create' => false,
			'widgets' => array(
			)
		),
		'prefooter' => array(
			'flush' => true,
			'create' => false,
			'widgets' => array(
			)
		),
		'top-panel-sidebar' => array(
			'flush' => true,
			'create' => false,
			'widgets' => array(
			)
		),
	),

);