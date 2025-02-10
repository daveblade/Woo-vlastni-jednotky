<?php
/**
 * Funkce pro manipulaci s množstvím v WooCommerce Flexible Quantity Pricing
 */

if (!defined('ABSPATH')) {
    exit; // Zamezení přímému přístupu
}

// âœ… Oprava nastavení množství u produktu a jeho variant
add_filter('woocommerce_quantity_input_args', function($args, $product) {
    if (get_post_meta($product->get_id(), '_enable_flexible_pricing', true) === 'yes') {
        $unit_size = get_post_meta($product->get_id(), '_unit_size', true);

        // Pokud jde o variantu, použijeme její jednotkovou velikost
        if ($product->is_type('variation')) {
            $variant_unit_size = get_post_meta($product->get_id(), '_unit_size_var', true);
            if (!empty($variant_unit_size)) {
                $unit_size = $variant_unit_size;
            }
        }

        // âœ… Oprava množství v košíku
        if (is_cart()) {
			foreach (WC()->cart->get_cart() as $cart_item) {
				if ($cart_item['product_id'] == $product->get_id() || $cart_item['variation_id'] == $product->get_id()) {
					$args['input_value'] = $cart_item['quantity']; // 🎯 Skutečné množství
					$args['min_value'] = max(1, floatval($unit_size)); // 🔧 Zabrání nesprávnému přepsání
					$args['step'] = floatval($unit_size); // ✅ Krok podle velikosti balení
					return $args;
				}
			}
		}


        // âœ… Nastavení minimálního množství pro nové produkty
        if (!empty($unit_size) && is_numeric($unit_size)) {
            $args['min_value'] = floatval($unit_size);
            $args['step'] = floatval($unit_size);
            $args['input_value'] = floatval($unit_size);
        }
    }

    return $args;
}, 10, 2);

add_filter('woocommerce_cart_item_quantity', function($product_quantity, $cart_item_key, $cart_item) {
    // Ověříme, zda jde o variantní produkt
    if ($cart_item['variation_id'] > 0) {
        $variation_id = $cart_item['variation_id'];
        $variant_unit_size = get_post_meta($variation_id, '_unit_size_var', true);

        if (!empty($variant_unit_size) && is_numeric($variant_unit_size)) {
            // Oprava načítání množství podle varianty
            $product_quantity = woocommerce_quantity_input(array(
                'input_name'  => "cart[$cart_item_key][qty]",
                'input_value' => $cart_item['quantity'], // Nastavení správného množství
                'max_value'   => $cart_item['data']->get_max_purchase_quantity(),
                'min_value'   => floatval($variant_unit_size),
                'step'        => floatval($variant_unit_size),
            ), $cart_item['data'], false);
        }
    }
    
    return $product_quantity;
}, 10, 3);

// âœ… Oprava zaokrouhlování množství v košíku i pro variantní produkty
add_filter('woocommerce_update_cart_action_cart_updated', function() {
    foreach (WC()->cart->get_cart() as $cart_item_key => $cart_item) {
        $product_id = $cart_item['product_id'];
        $unit_size = get_post_meta($product_id, '_unit_size', true);

        // âœ… Pokud se jedná o variantu, získáme správnou jednotkovou velikost
        if (!empty($cart_item['variation_id'])) {
            $variant_unit_size = get_post_meta($cart_item['variation_id'], '_unit_size_var', true);
            if (!empty($variant_unit_size)) {
                $unit_size = $variant_unit_size;
            }
        }
        // âœ… Oprava množství podle jednotkové velikosti
        if (!empty($unit_size) && is_numeric($unit_size)) {
            $new_qty = ceil($cart_item['quantity'] / floatval($unit_size)) * floatval($unit_size);

            // âœ… Aktualizace množství v košíku pro varianty i bÄ›žné produkty
            WC()->cart->set_quantity($cart_item_key, max($new_qty, floatval($unit_size)));

            error_log("ðŸ›  Opraveno množství pro produkt ID: $product_id, Varianta ID: " . ($cart_item['variation_id'] ?? 'N/A') . ", Nastavené množství: $new_qty");
        }
    }
});

// âœ… Přidání jednotky u produktu
add_action('woocommerce_before_add_to_cart_button', function() {
    global $product;
    if (get_post_meta($product->get_id(), '_enable_flexible_pricing', true) === 'yes') {
        $unit_type = get_post_meta($product->get_id(), '_unit_type', true);
        if (!empty($unit_type)) {
            echo '<div class="unit-display" style="margin-bottom: 10px; font-size: 14px;">Množství <strong>' . esc_html($unit_type) . '</strong></div>';
        }
    }
});

// âœ… Přidání jednotky do košíku
add_filter('woocommerce_cart_item_name', function($product_name, $cart_item, $cart_item_key) {
    $unit_type = get_post_meta($cart_item['product_id'], '_unit_type', true);
    if (!empty($unit_type)) {
        return $product_name . '<div class="cart-unit-label" style="font-size: 14px; margin-top: 5px;">Množství: <strong>' . esc_html($unit_type) . '</strong></div>';
    }
    return $product_name;
}, 10, 3);

// âœ… Přidání jednotky za cenu
add_filter('woocommerce_get_price_html', function($price, $product) {
    if (get_post_meta($product->get_id(), '_enable_flexible_pricing', true) === 'yes') {
        $unit_type = get_post_meta($product->get_id(), '_unit_type', true);
        if (!empty($unit_type)) {
            $price .= ' <span class="jednotka-ceny">/ ' . esc_html($unit_type) . '</span>';
        }
    }
    return $price;
}, 10, 2);
