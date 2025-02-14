<?php
function wptp_create_post_type() {
    $labels = array(
        'name' => __( 'Элементы каталога' ),
        'singular_name' => __( 'Элемент каталога' )
    );
    $args = array(
        'labels' => $labels,
        'has_archive' => true,
        'public' => true,
        'hierarchical' => false,
        'menu_position' => 5,
        'supports' => array( 'title', 'editor', 'excerpt', 'thumbnail' ),
        'taxonomies' => array('category')
    );
    register_post_type( 'catalog_item', $args );
}
add_action( 'init', 'wptp_create_post_type' );

// Регистрация произвольных полей
function register_catalog_item_meta() {
    register_post_meta( 'catalog_item', 'item_price', array(
        'type'          => 'number', 
        'description'   => 'item_price элемента каталога',
        'single'        => true, 
        'show_in_rest'  => true, 
        'sanitize_callback' => 'sanitize_text_field',
        'auth_callback' => function() { return current_user_can( 'edit_posts' ); } 
    ) );

    register_post_meta( 'catalog_item', 'item_articul', array(
        'type'          => 'string',
        'description'   => 'item_articul элемента каталога',
        'single'        => true,
        'show_in_rest'  => true,
        'sanitize_callback' => 'sanitize_text_field',
        'auth_callback' => function() { return current_user_can( 'edit_posts' ); } 
    ) );
}
add_action( 'init', 'register_catalog_item_meta' );

// Метабоксы
function catalog_item_add_meta_box() {
    add_meta_box(
        'catalog_item_meta',
        'Информация о товаре',
        'catalog_item_meta_callback',
        'catalog_item',
        'normal',
        'high'
    );
}
add_action( 'add_meta_boxes', 'catalog_item_add_meta_box' );

function catalog_item_meta_callback( $post ) {
    wp_nonce_field( basename( __FILE__ ), 'catalog_item_meta_nonce' );
    $item_price = get_post_meta( $post->ID, 'item_price', true );
    $item_articul = get_post_meta( $post->ID, 'item_articul', true );

    ?>
    <p>
        <label for="item_price">Цена:</label>
        <input type="number" id="item_price" name="item_price" value="<?php echo esc_attr( $item_price ); ?>" class="widefat">
    </p>
    <p>
        <label for="item_articul">Артиул:</label>
        <input type="text" id="item_articul" name="item_articul" value="<?php echo esc_attr( $item_articul ); ?>" class="widefat">
    </p>
    <?php
}

function catalog_item_save_meta( $post_id ) {
    if ( ! current_user_can( 'edit_post', $post_id ) ) {
        return;
    }

    if ( ! isset( $_POST['catalog_item_meta_nonce'] ) || ! wp_verify_nonce( $_POST['catalog_item_meta_nonce'], basename( __FILE__ ) ) ) {
        return;
    }

    if ( isset( $_POST['item_price'] ) ) {
        $item_price = sanitize_text_field( $_POST['item_price'] );
        update_post_meta( $post_id, 'item_price', $item_price );
    }

    if ( isset( $_POST['item_articul'] ) ) {
        $item_articul = sanitize_text_field( $_POST['item_articul'] );
        update_post_meta( $post_id, 'item_articul', $item_articul );
    }
}
add_action( 'save_post_catalog_item', 'catalog_item_save_meta' );
