<?php
/**
 * The Template for displaying all single products
 *
 * This template overrides the default WooCommerce single-product.php
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

echo view('woocommerce.single-product')->render();
