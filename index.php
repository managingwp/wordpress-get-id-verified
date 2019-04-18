<?php
/**
* Plugin Name: Get ID Verified
* Plugin URI: https://github.com/jordantrizz/wordpress-get-id-verified
* Description: This plugin contains all of my awesome custom functions.
* Author: Jordan
* Version: 0.1
*
* Code taken from https://www.shift8web.ca/2018/06/how-to-implement-a-government-id-verification-system-with-woocommerce-and-wordpress/ and cleaned up to work properly.
*/

/* Created Menu Item in WooCommerce for "Get ID Verified" */
function account_menu_items( $items ) { 
        $items['idverify'] = __( 'Get ID Verified', 'idverify' );
            return $items;
} 
add_filter( 'woocommerce_account_menu_items', 'account_menu_items', 10, 1 );

function add_my_account_endpoint() {
        add_rewrite_endpoint( 'idverify', EP_PAGES );
}
add_action( 'init', 'add_my_account_endpoint' );

function idverify_endpoint_content() {
    $current_user = get_current_user_id();
    $user_verified = get_field('government_id_verified', 'user_' . $current_user);
    if ($user_verified && $user_verified == 'yes') {
        echo 'You are already verified and no longer need to upload your ID';
    } else {
        echo 'Your account is not verified. If you believe this is in error, please <a href="/contact">contact us.</a><br><br>';
        acf_form_head();
        $form_options = array(
            'fields' => array(
                'attach_valid_government_id',
            ),
            'submit_value' => __("Save changes", 'acf'),
            'updated_message' => __("Government ID submitted. Please allow 1-2 business days for verification to be complete.", 'acf'),
            'post_id' => 'user_' . $current_user,
        );
        acf_form($form_options);
    }
}

add_action( 'woocommerce_account_idverify_endpoint', 'idverify_endpoint_content' );

function idverify_order_column( $columns ) {
    $columns['idverify_column'] = 'ID Verified';
    return $columns;
}
add_filter( 'manage_edit-shop_order_columns', 'idverify_order_column' );


function add_idverify_column_header( $columns )  {
 
    $new_columns = array();
 
    foreach ( $columns as $column_name => $column_info ) {
        $new_columns[ $column_name ] = $column_info;
        if ( 'idverify_column' === $column_name ) {
            $new_columns['idverify_column'] = __( 'ID Verified', 'my-textdomain' );
        }
    }
    return $new_columns;
}
add_filter( 'manage_edit-shop_order_columns', 'add_idverify_column_header', 20 );

function add_order_idverify_column_content( $column ) {
    global $post;
    if ( 'idverify_column' === $column ) {
        $order    = wc_get_order( $post->ID );
        $user_id = $order->user_id;
        $verified_status = get_field('government_id_verified', 'user_' . $user_id);
        if ($verified_status && $verified_status == 'yes') {
            echo '';
        } else {
            echo ' <a href="' . get_edit_user_link($user_id) . '#government_id_verified" target="_new">Verify User</a>';
        }
    }
}
add_action( 'manage_shop_order_posts_custom_column', 'add_order_idverify_column_content' );


function add_notice_for_verified() {
    $current_user = get_current_user_id();
    $user_verified = get_field('government_id_verified', 'user_' . $current_user);
    if (!$user_verified || $user_verified = 'no') {
        wc_add_notice( '<center>Your account is not verified<br><br>You can proceed with your order, however please visit the <a href="/my-account/idverify/">Get ID Verified</a> page from My Account page to verify your account.<br><br>If you\'re an existing verified customer, this is a new feature, we\'ve recently rolled out. Please <a href="/contact">contact us</a> if you\'re an existing verified customer', 'error' );
    }
}
add_action( 'woocommerce_before_checkout_form', 'add_notice_for_verified' );

?>