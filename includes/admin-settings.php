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
    $unit_type               = get_post_meta($post->ID, '_unit_type', true);
    
    echo '<div id="unit_packaging_options" class="panel woocommerce_options_panel">';
    echo '<div class="options_group">';
    
    // Checkbox pro aktivaci flexibilních jednotkových cen
    woocommerce_wp_checkbox([
        'id'          => '_enable_flexible_pricing',
        'label'       => __('Aktivovat flexibilní jednotkové ceny', 'woocommerce'),
        'description' => __('Povolí nastavení jednotkových cen pro tento produkt.', 'woocommerce'),
        'value'       => $enable_flexible_pricing
    ]);

    // ---- Přidání checkboxu pro zakázání editace množství ----
    woocommerce_wp_checkbox([
        'id'          => '_disable_quantity_edit',
        'label'       => __('Zakázat editaci množství', 'woocommerce'),
        'description' => __('Pole množství bude read-only a bude možné ho měnit pouze pomocí tlačítek +/–.', 'woocommerce'),
        'value'       => get_post_meta($post->ID, '_disable_quantity_edit', true)
    ]);
    // -----------------------------------------------------------

    // Textové pole pro velikost balení
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
    
    // Select box pro měrnou jednotku
    woocommerce_wp_select([
        'id'          => '_unit_type',
        'label'       => __('Měrná jednotka', 'woocommerce'),
        'desc_tip'    => true,
        'description' => __('Vyberte jednotku, ve které se produkt prodává.', 'woocommerce'),
        'options'     => [
            'm²'  => __('m² (metr čtvereční)', 'woocommerce'),
            'm³'  => __('m³ (metr krychlový)', 'woocommerce'),
            'kg'  => __('kg (kilogram)', 'woocommerce'),
            'l'   => __('l (litr)', 'woocommerce'),
            'pcs' => __('ks (kus)', 'woocommerce')
        ],
        'value'       => $unit_type
    ]);
    
    echo '</div>';
    echo '</div>';
});

// Uložení hodnoty nastavení
add_action('woocommerce_process_product_meta', 'save_unit_packaging_fields');
function save_unit_packaging_fields($post_id) {
    // Uložení checkboxu pro flexibilní ceny
    $enable_flexible_pricing = isset($_POST['_enable_flexible_pricing']) ? 'yes' : 'no';
    update_post_meta($post_id, '_enable_flexible_pricing', $enable_flexible_pricing);

    // ---- Uložení checkboxu pro zakázání editace množství ----
    $disable_quantity_edit = isset($_POST['_disable_quantity_edit']) ? 'yes' : 'no';
    update_post_meta($post_id, '_disable_quantity_edit', $disable_quantity_edit);
    // -----------------------------------------------------------

    // Uložení velikosti balení
    if (isset($_POST['_unit_size'])) {
        update_post_meta($post_id, '_unit_size', sanitize_text_field($_POST['_unit_size']));
    }

    // Uložení měrné jednotky
    if (isset($_POST['_unit_type'])) {
        update_post_meta($post_id, '_unit_type', sanitize_text_field($_POST['_unit_type']));
    }
}
?>
