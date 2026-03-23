<?php
/**
 * The Template for displaying product archives, including the main shop page which is a post type archive
 *
 * This template overrides the default WooCommerce archive-product.php
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

echo view('woocommerce.archive-product')->render();
