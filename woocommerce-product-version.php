<?php
/**
 * Plugin Name: WooCommerce Product Version
 * Plugin URI: https://github.com/mfisher31/woocommerce-product-version
 * Description: Adds a version number field to WooCommerce products and exposes it via a REST API endpoint.
 * Version: 1.0.0
 * Requires at least: 5.8
 * Requires PHP: 7.4
 * Author: Michael Fisher
 * Author URI: mailto:mfisher31@protonmail.com
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: woocommerce-product-version
 * Domain Path: /languages
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * API DOCUMENTATION
 * 
 * Endpoint: /wp-json/product-versioning/v1/products/{id}
 * Method: GET
 * Authentication: None (public, read-only)
 * 
 * Success Response (200):
 * {
 *     "status": "success",
 *     "product_id": 42,
 *     "product_name": "Pro App Desktop Edition",
 *     "product_version": "2.1.5",
 *     "product_url": "https://your-store.com/product/pro-app-desktop-edition/"
 * }
 * 
 * Error Response - Product Not Found (200):
 * {
 *     "status": "error",
 *     "code": "product_not_found",
 *     "message": "No WooCommerce product found for the specified product."
 * }
 * 
 * Error Response - Missing ID (200):
 * {
 *     "status": "error",
 *     "code": "product_id_missing",
 *     "message": "Invalid Request. No product ID provided."
 * }
 */

// Ensure WooCommerce is active before initializing plugin functionality
add_action( 'plugins_loaded', 'wcpv_init_plugin' );

function wcpv_init_plugin() {
    if ( ! class_exists( 'WooCommerce' ) ) {
        return;
    }

    // Add custom version field to product general settings
    add_action( 'woocommerce_product_options_general_product_data', 'wcpv_add_version_field' );
    
    // Save custom version field data
    add_action( 'woocommerce_process_product_meta', 'wcpv_save_version_field' );
    
    // Register REST API endpoint
    add_action( 'rest_api_init', 'wcpv_register_rest_endpoint' );
}

/**
 * Add version number field to product general settings tab
 */
function wcpv_add_version_field() {
    woocommerce_wp_text_input( array(
        'id'          => '_product_version_number',
        'label'       => __( 'Version Number', 'woocommerce' ),
        'placeholder' => '1.0.0',
        'desc_tip'    => true,
        'description' => __( 'Enter the product version number (e.g., 1.0.0, 2.1.5).', 'woocommerce' ),
        'type'        => 'text',
    ) );
}

/**
 * Save version number field data
 *
 * @param int $post_id Product post ID
 */
function wcpv_save_version_field( $post_id ) {
    // Get the version number from POST data
    $version_number = isset( $_POST['_product_version_number'] ) ? $_POST['_product_version_number'] : '';
    
    // Sanitize the input
    $version_number = sanitize_text_field( $version_number );
    
    // Update or delete the meta field
    if ( ! empty( $version_number ) ) {
        update_post_meta( $post_id, '_product_version_number', $version_number );
    } else {
        delete_post_meta( $post_id, '_product_version_number' );
    }
}

/**
 * Register REST API endpoint
 */
function wcpv_register_rest_endpoint() {
    register_rest_route( 'product-versioning/v1', '/products/(?P<id>\d+)', array(
        'methods'             => 'GET',
        'callback'            => 'wcpv_get_product_version',
        'permission_callback' => '__return_true', // Public, read-only access
        'args'                => array(
            'id' => array(
                'validate_callback' => function( $param, $request, $key ) {
                    return is_numeric( $param );
                }
            ),
        ),
    ) );
}

/**
 * REST API callback to retrieve product version information
 *
 * @param WP_REST_Request $request Request object
 * @return WP_REST_Response Response object
 */
function wcpv_get_product_version( $request ) {
    try {
        // Get product ID from request
        $product_id = $request->get_param( 'id' );
        
        // Validate product ID
        if ( empty( $product_id ) ) {
            return new WP_REST_Response( array(
                'status'  => 'error',
                'code'    => 'product_id_missing',
                'message' => 'Invalid Request. No product ID provided.',
            ), 200 );
        }
        
        // Get product object
        $product = wc_get_product( $product_id );
        
        // Check if product exists
        if ( ! $product || ! is_a( $product, 'WC_Product' ) ) {
            return new WP_REST_Response( array(
                'status'  => 'error',
                'code'    => 'product_not_found',
                'message' => 'No WooCommerce product found for the specified product.',
            ), 200 );
        }
        
        // Get version number
        $version_number = $product->get_meta( '_product_version_number', true );
        
        // Get product details
        $product_name = $product->get_name();
        $product_url  = $product->get_permalink();
        
        // Prepare success response
        $response_data = array(
            'status'          => 'success',
            'product_id'      => absint( $product_id ),
            'product_name'    => $product_name,
            'product_version' => ! empty( $version_number ) ? $version_number : '',
            'product_url'     => $product_url,
        );
        
        return new WP_REST_Response( $response_data, 200 );
        
    } catch ( Exception $e ) {
        // Handle any unexpected errors
        return new WP_REST_Response( array(
            'status'  => 'error',
            'code'    => 'server_error',
            'message' => 'An unexpected error occurred while retrieving product information.',
        ), 200 );
    }
}
