<?php

function threew_storefront_official_image_jobs() {
	$range_rover_overview_image = array(
		'url'   => 'https://www.brabus.com/_Resources/Persistent/8/4/f/b/84fbb8e2f941e938ee85ba19d35eb756adda1406/Tuning%C3%BCbersicht%20Range%20Rover%20%283%29-540x360.jpg',
		'title' => 'BRABUS Range Rover official tuning overview',
	);

	$w465_context_images = array(
		array(
			'url'   => 'https://www.brabus.com/_Resources/Persistent/5/7/f/5/57f5dc75f8691bffa5401310900c62d9936e1a2d/465-234-00-2560x1440.jpg',
			'title' => 'BRABUS W465 Widestar official front view',
		),
		array(
			'url'   => 'https://www.brabus.com/_Resources/Persistent/8/f/d/9/8fd9f2929ce2d9f338d0b9a2ca806d440f3c3e93/004_BRABUS_G800_Widestar_3_4_rear_no%20Carbon%20and%20ZM-2560x1440.jpg',
			'title' => 'BRABUS W465 Widestar official rear view',
		),
	);

	return array(
		array(
			'option_name'    => 'threew_w465_widestar_official_images_20260625',
			'priority'       => 20,
			'result_format'  => 'single_product',
			'products'       => array(
				121906 => array(
					'images'                  => array(
						array(
							'url'   => 'https://www.brabus.com/_Resources/Persistent/5/7/f/5/57f5dc75f8691bffa5401310900c62d9936e1a2d/465-234-00-2560x1440.jpg',
							'title' => 'BRABUS W465 Widestar Kit official front view',
						),
						array(
							'url'   => 'https://www.brabus.com/_Resources/Persistent/8/f/d/9/8fd9f2929ce2d9f338d0b9a2ca806d440f3c3e93/004_BRABUS_G800_Widestar_3_4_rear_no%20Carbon%20and%20ZM-2560x1440.jpg',
							'title' => 'BRABUS W465 Widestar Kit official rear view',
						),
					),
					'set_featured_if_missing' => true,
				),
			),
		),
		array(
			'option_name'   => 'threew_range_rover_wheel_official_images_20260625',
			'priority'      => 21,
			'result_format' => 'results',
			'products'      => array(
				97994 => array(
					'images' => array(
						array(
							'url'   => 'https://www.brabus.com/_Resources/Persistent/a/e/a/7/aea72eae083734b56ed1a1b650d25aeb717c6b50/Mono%20ZV%201-2560x1440.jpg',
							'title' => 'BRABUS Range Rover Monoblock ZV official wheel image',
						),
						$range_rover_overview_image,
					),
				),
				97997 => array(
					'images' => array(
						array(
							'url'   => 'https://www.brabus.com/_Resources/Persistent/4/4/b/7/44b764385adec8ff102188f5deff4774b12c64f9/MonoblockZ_seitlich_white-2-2560x1440.jpg',
							'title' => 'BRABUS Range Rover Monoblock Z official wheel image',
						),
						$range_rover_overview_image,
					),
				),
				97991 => array(
					'images' => array(
						array(
							'url'   => 'https://www.brabus.com/_Resources/Persistent/9/7/c/5/97c51393648feff5b9bfb406f7f7948d50192ddb/M12-222-217-1-PE-2560x1440.jpg',
							'title' => 'BRABUS Range Rover Monoblock M official wheel image',
						),
						$range_rover_overview_image,
					),
				),
			),
		),
		array(
			'option_name'   => 'threew_w465_carbon_package_context_images_20260625',
			'priority'      => 22,
			'result_format' => 'results',
			'products'      => array(
				121947 => array( 'images' => $w465_context_images ),
				121950 => array( 'images' => $w465_context_images ),
			),
		),
		array(
			'option_name'   => 'threew_monoblock_f_titanium_official_image_20260625',
			'priority'      => 23,
			'result_format' => 'attachment_ids',
			'products'      => array(
				39952 => array(
					'images' => array(
						array(
							'url'   => 'https://www.brabus.com/_Resources/Persistent/3/9/7/9/3979ca2365327a9efa71f24336ad6e85459bffa3/Monoblock%20F%20Titan-2560x1440.jpg',
							'title' => 'BRABUS Monoblock F Titanium Gunmetal official wheel image',
						),
					),
				),
			),
		),
		array(
			'option_name'   => 'threew_priority_product_official_images_20260625',
			'priority'      => 24,
			'result_format' => 'results',
			'products'      => array(
				121634 => array(
					'images' => array(
						array(
							'url'   => 'https://www.brabus.com/_Resources/Persistent/0/d/3/8/0d3829b4ea7b5b8a7e46b0364fec1b73bcdd28a9/232-678-63-2560x1440.jpg',
							'title' => 'BRABUS SL63 official rear diffuser and exhaust image',
						),
					),
				),
				121968 => array(
					'images' => array(
						array(
							'url'   => 'https://www.brabus.com/_Resources/Persistent/8/7/9/9/879944690836fe4ecac9615639bf0fd230e6dd4e/465-678-63-2%20%281%29_NEU-2560x1440.jpg',
							'title' => 'BRABUS W465 valve controlled exhaust official image',
						),
					),
				),
				121978 => array(
					'images' => array(
						array(
							'url'   => 'https://www.brabus.com/_Resources/Persistent/0/7/6/d/076dc809ff5b41622c7422a62fcda3ce94072a38/465-b40-700-00-powerxtra-2560x1440.jpg',
							'title' => 'BRABUS W465 B40-700 PowerXtra official image',
						),
					),
				),
				98001 => array(
					'images' => array(
						array(
							'url'   => 'https://www.brabus.com/_Resources/Persistent/1/9/9/a/199a458f9967ad4d95bdf761371c3cf57285e354/LK-350-00-W-VL-2560x1440.jpg',
							'title' => 'BRABUS Range Rover carbon entrance panels official image',
						),
					),
				),
			),
		),
	);
}
