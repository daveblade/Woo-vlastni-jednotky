<?php
/**
 * Admin Settings for WooCommerce Flexible Quantity Pricing
 */

// Přidání vlastní záložky "Jednotky a balení" k produktům
add_filter('woocommerce_product_data_tabs', function($tabs) {
    $tabs['unit_packaging'] = [
        'label'    => __('Jednotky a balení', 'woocommerce'),
        'target'   => 'unit_packaging_options',
        'class'    => ['show_if_simple', 'show_if_variable'],
        'priority' => 21,
    ];
    return $tabs;
});

// Obsah vlastní záložky "Jednotky a balení"
add_action('woocommerce_product_data_panels', function() {
    global $post;
    $enable_flexible_pricing = get_post_meta($post->ID, '_enable_flexible_pricing', true);
    $unit_type = get_post_meta($post->ID, '_unit_type', true);
    echo '<div id="unit_packaging_options" class="panel woocommerce_options_panel">';
    echo '<div class="options_group">';
    
    woocommerce_wp_checkbox([
        'id'          => '_enable_flexible_pricing',
        'label'       => __('Aktivovat flexibilní jednotkové ceny', 'woocommerce'),
        'description' => __('Povolí nastavení jednotkových cen pro tento produkt.', 'woocommerce'),
        'value'       => $enable_flexible_pricing
    ]);
    
    woocommerce_wp_text_input([
        'id'          => '_unit_size',
        'label'       => __('Velikost balení (' . esc_html($unit_type) . ')', 'woocommerce'),
        'desc_tip'    => true,
        'description' => __('Mění jednotlivě množství v balení, pokud se liší od globálního nastavení.', 'woocommerce'),
        'type'        => 'number',
        'custom_attributes' => [
			'min'  => '0.01',
			'step' => '0.01'
		]
    ]);
    
    woocommerce_wp_select([
        'id'          => '_unit_type',
        'label'       => __('Měrná jednotka', 'woocommerce'),
        'desc_tip'    => true,
        'description' => __('Vyberte jednotku, ve které se produkt prodává.', 'woocommerce'),
        'options'     => [
            'm&sup2;'  => __('m² (metr čtvereční)', 'woocommerce'),
            'm&sup3;'  => __('m³ (metr krychlový)', 'woocommerce'),
            'kg'  => __('kg (kilogram)', 'woocommerce'),
            'l'   => __('l (litr)', 'woocommerce'),
            'pcs' => __('ks (kus)', 'woocommerce')
        ],
        'value' => $unit_type
    ]);
    
    echo '</div>';
    echo '</div>';
});

// Přidání možnosti nastavení velikosti balení pro varianty
add_action('woocommerce_variation_options_pricing', function($loop, $variation_data, $variation) {
    woocommerce_wp_text_input([
        'id'          => '_unit_size_var_' . $variation->ID,
        'name'        => '_unit_size_var[' . $variation->ID . ']',
        'label'       => __('Velikost balení', 'woocommerce'),
        'desc_tip'    => true,
        'description' => __('Mění množství v balení pro tuto variantu, pokud se liší od globálního nastavení.', 'woocommerce'),
        'type'        => 'number',
        'custom_attributes' => [
			'min'  => '0.01',
			'step' => '0.01'
		],
        'value'       => get_post_meta($variation->ID, '_unit_size_var', true)
    ]);
}, 10, 3);

// Uložení hodnoty velikosti balení pro varianty
add_action('woocommerce_save_product_variation', function($variation_id, $i) {
    if (isset($_POST['_unit_size_var'][$variation_id])) {
        update_post_meta($variation_id, '_unit_size_var', sanitize_text_field($_POST['_unit_size_var'][$variation_id]));
    }
}, 10, 2);

add_action('woocommerce_process_product_meta', 'save_unit_packaging_fields');
function save_unit_packaging_fields( $post_id ) {
    // Uložení checkboxu pro flexibilní ceny
    $enable_flexible_pricing = isset( $_POST['_enable_flexible_pricing'] ) ? 'yes' : 'no';
    update_post_meta( $post_id, '_enable_flexible_pricing', $enable_flexible_pricing );
    
    // Uložení velikosti balení pro produkt
    if ( isset( $_POST['_unit_size'] ) ) {
        update_post_meta( $post_id, '_unit_size', sanitize_text_field( $_POST['_unit_size'] ) );
    }
    
    // Uložení měrné jednotky
    if ( isset( $_POST['_unit_type'] ) ) {
        update_post_meta( $post_id, '_unit_type', sanitize_text_field( $_POST['_unit_type'] ) );
    }
}

?>