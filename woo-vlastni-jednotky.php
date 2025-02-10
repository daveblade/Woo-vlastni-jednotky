<?php
/**
 * Plugin Name: Woo vlastní jednotky
 * Description: Umožňuje dynamický výpočet ceny podle měrné jednotky a balení v WooCommerce.
 * Version: 1.0
 * Author: DaveBLADE
 */


if (!defined('ABSPATH')) {
    exit; // Zamezení přímému přístupu
}

// Definování konstanty pro cestu k pluginu
define('WC_FLEXIBLE_QUANTITY_PATH', plugin_dir_path(__FILE__));

// Načtení dalších souborů pluginu
require_once WC_FLEXIBLE_QUANTITY_PATH . 'includes/admin-settings.php';
require_once WC_FLEXIBLE_QUANTITY_PATH . 'includes/quantity-functions.php';
require_once WC_FLEXIBLE_QUANTITY_PATH . 'includes/frontend-scripts.php';


// Aktivace pluginu
function wc_flexible_quantity_activate() {
    // Zde můžeme přidat kód pro inicializaci při aktivaci pluginu
}
register_activation_hook(__FILE__, 'wc_flexible_quantity_activate');

// Deaktivace pluginu
function wc_flexible_quantity_deactivate() {
    // Zde můžeme přidat kód pro úklid při deaktivaci pluginu
}
register_deactivation_hook(__FILE__, 'wc_flexible_quantity_deactivate');

?>