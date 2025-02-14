<?php 
	add_action( 'rest_api_init', 'register_catalog_endpoint' );

	function register_catalog_endpoint() {
	register_rest_route(
		'custom/v1',
		'/catalog',
		array(
		'methods'  => 'GET',
		'callback' => 'get_catalog_items',
		)
	);
	}

	function get_catalog_items( WP_REST_Request $request ) {
		$args = array(
		'post_type'      => 'catalog_item',
		'posts_per_page' => -1,
		);
	
		$catalog_query = new WP_Query( $args );
	
		$data = array();
	
		if ( $catalog_query->have_posts() ) {
		while ( $catalog_query->have_posts() ) {
			$catalog_query->the_post();
			
			$item_price = get_post_meta(get_the_ID(), 'item_price', true);
            $item_articul = get_post_meta(get_the_ID(), 'item_articul', true);

			$item = array(
			'id'    => get_the_ID(),
			'title' => get_the_title(),
			'link'  => get_permalink(),
			'price'   => $item_price,
			'articul' => $item_articul,
			);
	
			$data[] = $item;
		}
		wp_reset_postdata();
		}
	
		return rest_ensure_response( $data );
	}
