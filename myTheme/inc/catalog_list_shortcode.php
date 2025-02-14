<?php
function catalog_list_shortcode( $atts ) {
    echo '<form action="" method="get">
		<label for="min_price">Минимальная цена:</label>
		<input type="number" name="min_price" id="min_price" value="' . (isset($_GET['min_price']) ? esc_attr($_GET['min_price']) : '') . '">

		<label for="max_price">Максимальная цена:</label>
		<input type="number" name="max_price" id="max_price" value="' . (isset($_GET['max_price']) ? esc_attr($_GET['max_price']) : '') . '">

		<input type="submit" value="Фильтр">
		<input type="hidden" name="paged" value="1">
		<input type="button" value="Сбросить" onclick="window.location.href=\'' . get_permalink() . '\'">
	</form>';
	
	// Получаем значения цен из GET запроса
	$min_price = isset( $_GET['min_price'] ) ? sanitize_text_field( $_GET['min_price'] ) : '';
	$max_price = isset( $_GET['max_price'] ) ? sanitize_text_field( $_GET['max_price'] ) : '';

	$args = array(
		'post_type' => 'catalog_item',
		'posts_per_page' => 2,
		'paged' => get_query_var( 'paged' ) ? get_query_var( 'paged' ) : 1, // пагинация
	);

	// Добавляем мета-запрос, если есть фильтры по цене
	if ( ! empty( $min_price ) || ! empty( $max_price ) ) {
		$args['meta_query'] = array(
			'relation' => 'AND', // все условия
		);

		if ( ! empty( $min_price ) ) {
			$args['meta_query'][] = array(
				'key' => 'item_price', 
				'value' => $min_price,
				'compare' => '>=', // Больше или равно
				'type' => 'NUMERIC'
			);
		}

		if ( ! empty( $max_price ) ) {
			$args['meta_query'][] = array(
				'key' => 'item_price',
				'value' => $max_price,
				'compare' => '<=', // Меньше или равно
				'type' => 'NUMERIC'
			);
		}
	}

	
	$catalog_query = new WP_Query( $args );

	if ( $catalog_query->have_posts() ) {
		
		echo '<ul>';
		
		while ( $catalog_query->have_posts() ) {
			$catalog_query->the_post();
			$item_price = get_post_meta(get_the_ID(), 'item_price', true);
            $item_articul = get_post_meta(get_the_ID(), 'item_articul', true);
			?>
			<li>
				<a href="<?php the_permalink(); ?>">
					<?php the_title();?>
				</a>
				<p>Цена: <?php echo $item_price; ?></p>
				<p>Артикул: <?php echo $item_articul; ?></p>
				<?php the_excerpt();?>
			</li>
			<?php
		}
		echo '</ul>';

		// Пагинация
		$big = 999999999; 
		echo paginate_links( array(
			'base'    => str_replace( $big, '%#%', esc_url( get_pagenum_link( $big ) ) ),
			'format'  => '?paged=%#%',
			'current' => max( 1, get_query_var( 'paged' ) ),
			'total'   => $catalog_query->max_num_pages,
			'prev_text' => '&laquo; Предыдущая',
			'next_text' => 'Следующая &raquo;',
			'add_args' => array(
				'min_price' => $min_price,
				'max_price' => $max_price,
			)
		) );

		wp_reset_postdata();
	} else {
		echo 'Элементы каталога не найдены.';
	}
}

add_shortcode( 'catalog_list', 'catalog_list_shortcode' );
