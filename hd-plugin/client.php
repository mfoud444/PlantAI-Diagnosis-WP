<?php
if (!defined('ABSPATH')) {
    exit;
}

// Load Detection AI template
function hd_load_template()
{
    ob_start();
    include_once plugin_dir_path(__FILE__) . 'app_vue.php';
    return ob_get_clean();
}

// Shortcode to load Detection AI template
function hd_detectionai_shortcode()
{
    return hd_load_template();
}
add_shortcode('detectionai_shortcode', 'hd_detectionai_shortcode');

// Add rewrite endpoint for Detection AI
function hd_add_detectionai_endpoint()
{
    add_rewrite_endpoint('detectionai', EP_ROOT);
    flush_rewrite_rules();
}
add_action('init', 'hd_add_detectionai_endpoint');

// Template redirect for Detection AI
function hd_template_redirect()
{
    global $wp_query;
    $detectionai_endpoint = isset($wp_query->query_vars['detectionai']) ? $wp_query->query_vars['detectionai'] : null;

    if ($detectionai_endpoint !== null) {
        get_header();
        echo hd_load_template();
        get_footer();
        exit;
    }
}
add_action('template_redirect', 'hd_template_redirect');
function register_custom_product_category_shortcode_endpoint() {
    register_rest_route('wphd/v2', '/product-category', array(
        'methods' => 'GET',
        'callback' => 'custom_product_category_shortcode',
        'permission_callback' => '__return_true'
    ));
}
add_action('rest_api_init', 'register_custom_product_category_shortcode_endpoint');

function custom_product_category_shortcode($data) {
    $category_id = isset($data['category']) ? sanitize_text_field($data['category']) : '';

    if (!$category_id) {
        return new WP_Error('no_category', 'Category parameter is missing', array('status' => 400));
    }

    $products = new WC_Product_Query(array(
        'category' => array($category_id),
        'limit' => 10,
    ));

    $product_data = array();

    foreach ($products->get_products() as $product) {
        $product_data[] = array(
            'id' => $product->get_id(),
            'name' => $product->get_name(),
            'permalink' => $product->get_permalink(),
            'image' => wp_get_attachment_image_url($product->get_image_id(), 'woocommerce_thumbnail'),
            'price_html' => $product->get_price_html(),
            'on_sale' => $product->is_on_sale(),
            'stock_status' => $product->get_stock_status(),
            'categories' => $product->get_category_ids()
        );
    }

    return rest_ensure_response($product_data);
}



function register_custom_product_disease_shortcode_endpoint() {
    register_rest_route('wphd/v2', '/product-disease', array(
        'methods' => 'GET',
        'callback' => 'custom_product_disease_shortcode',
        'permission_callback' => '__return_true'
    ));
}
add_action('rest_api_init', 'register_custom_product_disease_shortcode_endpoint');

function custom_product_disease_shortcode($data) {
    global $wpdb;

    $disease_id = isset($data['disease_id']) ? intval($data['disease_id']) : 0;

    if (!$disease_id) {
        return new WP_Error('no_disease_id', 'Disease ID parameter is missing', array('status' => 400));
    }

    // Fetch product IDs associated with the given disease ID from the product_disease table
    $table_name_product_disease = $wpdb->prefix . 'product_disease';
    $product_ids = $wpdb->get_col($wpdb->prepare(
        "SELECT product_id FROM $table_name_product_disease WHERE disease_id = %d",
        $disease_id
    ));

    // If no products are associated with the disease_id, get all products
    if (empty($product_ids)) {
        $products = wc_get_products(array(
            'limit' => -1, // Get all products
        ));
    } else {
        // Fetch products based on the retrieved product IDs
        $products = wc_get_products(array(
            'include' => $product_ids,
            'limit' => -1, // Get all products in the list
        ));
    }

    $product_data = array();

    foreach ($products as $product) {
        $product_data[] = array(
            'id' => $product->get_id(),
            'name' => $product->get_name(),
            'permalink' => $product->get_permalink(),
            'image' => wp_get_attachment_image_url($product->get_image_id(), 'woocommerce_thumbnail'),
            'price_html' => $product->get_price_html(),
            'on_sale' => $product->is_on_sale(),
            'stock_status' => $product->get_stock_status(),
            'categories' => $product->get_category_ids()
        );
    }

    return rest_ensure_response($product_data);
}



// Register custom REST API endpoint for product search
function register_custom_product_search_endpoint() {
    register_rest_route('wphd/v2', '/product-search', array(
        'methods' => 'GET',
        'callback' => 'custom_product_search',
        'permission_callback' => '__return_true'
    ));
}
add_action('rest_api_init', 'register_custom_product_search_endpoint');

function custom_product_search($data) {
    $search_query = isset($data['search']) ? sanitize_text_field($data['search']) : '';

    if (!$search_query) {
        return new WP_Error('no_search_query', 'Search query parameter is missing', array('status' => 400));
    }

    $products = new WC_Product_Query(array(
        'limit' => 10,
        's' => $search_query,
        'return' => 'ids'
    ));

    $product_ids = $products->get_products();
    $product_data = array();

    foreach ($product_ids as $product_id) {
        $product = wc_get_product($product_id);
        $product_data[] = array(
            'id' => $product->get_id(),
            'name' => $product->get_name(),
            'image' => wp_get_attachment_image_url($product->get_image_id(), 'woocommerce_thumbnail'),
            'description' => $product->get_short_description()
        );
    }

    return rest_ensure_response($product_data);
}





// Flush rewrite rules on activation
function hd_flush_rewrite_rules()
{
    flush_rewrite_rules();
}
register_activation_hook(__FILE__, 'hd_flush_rewrite_rules');

// Remove rewrite rules on deactivation
function hd_remove_rewrite_rules()
{
    flush_rewrite_rules();
}
register_deactivation_hook(__FILE__, 'hd_remove_rewrite_rules');




