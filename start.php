<?php
/**
 * Plugin Name: CRM - Scam Report Management
 * Description: Plugin for CRM to manage scam reports.
 * Version: 1.0
 * Author: Developer
 */

define("CRM_DIR_PATH",plugin_dir_path(__FILE__));
define("CRM_URl",plugins_url(__FILE__)); 
define("CRM_VER","1.0");

/*Activation and Deactivation functions*/
register_activation_hook(__FILE__,'activate_crm_plugin_function');
register_deactivation_hook(__FILE__,'deactivate_crm_plugin_function');

function activate_crm_plugin_function(){

}
function deactivate_crm_plugin_function(){

}

/*
* Creating a function to create our CPT
*/
function custom_crm_post_type() {
    // Set UI labels for Custom Post Type
        $labels = array(
            'name'                => _x( 'Scams', 'Post Type General Name', 'twentytwenty' ),
            'singular_name'       => _x( 'Scam', 'Post Type Singular Name', 'twentytwenty' ),
            'menu_name'           => __( 'Scams', 'twentytwenty' ),
            'parent_item_colon'   => __( 'Parent Scam', 'twentytwenty' ),
            'all_items'           => __( 'All Scams', 'twentytwenty' ),
            'view_item'           => __( 'View Scam', 'twentytwenty' ),
            'add_new_item'        => __( 'Add New Scam', 'twentytwenty' ),
            'add_new'             => __( 'Add New', 'twentytwenty' ),
            'edit_item'           => __( 'Edit Scam', 'twentytwenty' ),
            'update_item'         => __( 'Update Scam', 'twentytwenty' ),
            'search_items'        => __( 'Search Scam', 'twentytwenty' ),
            'not_found'           => __( 'Not Found', 'twentytwenty' ),
            'not_found_in_trash'  => __( 'Not found in Trash', 'twentytwenty' ),
        );
// Set other options for Custom Post Type
        $args = array(
            'label'               => __( 'scams', 'twentytwenty' ),
            'description'         => __( 'Scams news and reviews', 'twentytwenty' ),
            'labels'              => $labels,
            // Features this CPT supports in Post Editor
            'supports'            => array( 'title', 'editor', 'excerpt', 'author', 'thumbnail', 'comments', 'revisions', 'custom-fields', ),
            // You can associate this CPT with a taxonomy or custom taxonomy. 
            'taxonomies'          => array( 'genres' ),
            /* A hierarchical CPT is like Pages and can have
            * Parent and child items. A non-hierarchical CPT
            * is like Posts.
            */ 
            'hierarchical'        => false,
            'public'              => true,
            'show_ui'             => true,
            'show_in_menu'        => true,
            'show_in_nav_menus'   => true,
            'show_in_admin_bar'   => true,
            'menu_position'       => 5,
            'can_export'          => true,
            'has_archive'         => true,
            'exclude_from_search' => false,
            'publicly_queryable'  => true,
            'capability_type'     => 'post',
            'show_in_rest' => true,
            'menu_icon' => 'dashicons-format-aside',
        );
        register_post_type( 'scams', $args ); 
    }
    add_action( 'init', 'custom_crm_post_type', 0 );

/**
 * Created shortcode to display form on frontend
 */
add_shortcode('submit-scam-form', 'submit_scam_form_function');
function submit_scam_form_function(){
    if(file_exists(CRM_DIR_PATH."/view/scam-submit-form.php")){
        include CRM_DIR_PATH."/view/scam-submit-form.php";
    }else{
        //CRM_DIR_PATH."/view/scam-submit-form.php" file not found
    }
}

/**
 * enqueue scripts
 */
function add_crm_asset($hook){
    wp_enqueue_style("crm-style",plugins_url( 'src/css/style.css' , __FILE__ ));
    wp_enqueue_script('crm-form-script',plugins_url( 'src/js/crm-form.js' , __FILE__ ), array('jquery'), false, true);      
}
add_action( 'wp_enqueue_scripts', 'add_crm_asset' );

/**
 * ajax action
 */
add_action('wp_ajax_submit_crm_form', 'submit_crm_form');
function submit_crm_form(){
    if(isset($_POST['crm_data'])){
        $data = $_POST['crm_data'];
        if(save_data_on_scam_post($data)){
            wp_send_json_success($data);
        }
    }
}
/**
 * save data on scam post
 */
function save_data_on_scam_post($data){
    $post_args = array(
        'post_type' => 'scams',
        'post_title' => utf8_encode($data['crm_title']),
        'post_status'   => 'publish',  
        'post_author'   => get_current_user_id(),
        'post_content'  => utf8_encode($data['description']),
        'filter' => true  
    );
    $postid = wp_insert_post($post_args,true);  
    update_post_meta($postid,'scam_username', $data['name']);
    update_post_meta($postid,'scam_email', $data['namemaile']);
    update_post_meta($postid,'scam_phone', $data['phone']);
    update_post_meta($postid,'scam_country', $data['country']);
    update_post_meta($postid,'first_deposit', $data['first_deposit']);
    update_post_meta($postid,'currency', $data['currency']);
    update_post_meta($postid,'sale_amount', $data['sale_amount']);
    update_post_meta($postid,'deposit_method', $data['deposit_method']);
    update_post_meta($postid,'total_amount', $data['total_amount']);
    update_post_meta($postid,'privacy_policy', $data['privacy_policy']);  
    return true;
}
/**
 * define ajax url on head.
 */
add_action('wp_head', 'crm_plugin_ajaxurl');
function crm_plugin_ajaxurl() {
   echo '<script type="text/javascript">
           var crm_ajaxurl = "' . admin_url('admin-ajax.php') . '";
         </script>';
}


/**
 * Add meta box on scam post
 */
function wporg_add_custom_box()
{
    $screens = ['scams'];
    foreach ($screens as $screen) {
        add_meta_box(
            'scam_details_fields',
            'Scam Details',
            'scam_details_fields',
            $screen
        );
    }
}

/**
 * Display field on meta post 
 */
add_action('add_meta_boxes', 'wporg_add_custom_box',1);
function scam_details_fields($post){
    $scam_details = array(
        'scam_username' => get_post_meta($post->ID,'scam_username',true),
        'scam_email' => get_post_meta($post->ID,'scam_email',true),
        'scam_phone' => get_post_meta($post->ID,'scam_phone',true),
        'scam_country' => get_post_meta($post->ID,'scam_country',true),
        'first_deposit' => get_post_meta($post->ID,'first_deposit',true),
        'currency' => get_post_meta($post->ID,'currency',true),
        'sale_amount' =>  get_post_meta($post->ID,'sale_amount',true),
        'deposit_method' => get_post_meta($post->ID,'deposit_method',true),
        'total_amount' => get_post_meta($post->ID,'total_amount',true),
        'privacy_policy' => get_post_meta($post->ID,'privacy_policy',true),
    );
    if(file_exists(CRM_DIR_PATH."/view/meta_box.php")){
        include CRM_DIR_PATH."/view/meta_box.php";
    }else{
        //CRM_DIR_PATH."/view/scam-submit-form.php" file not found
    }
}

/**
 * save while update
 */
function save_scams_post_meta_by_wp_admin($postid){
    if(isset($_POST['scam_username'])){
        update_post_meta($postid,'scam_username', isset($_POST['scam_username']))?$_POST['scam_username']:'';
        update_post_meta($postid,'scam_email', isset($_POST['scam_email'])?$_POST['scam_email']:'');
        update_post_meta($postid,'scam_phone', isset($_POST['scam_phone'])?$_POST['scam_phone']:'');
        update_post_meta($postid,'scam_country', isset($_POST['scam_country'])?$_POST['scam_country']:'');
        update_post_meta($postid,'first_deposit', isset($_POST['scam_deposit'])?$_POST['scam_deposit']:'');
        update_post_meta($postid,'currency', isset($_POST['scam_currency'])?$_POST['scam_currency']:'');
        update_post_meta($postid,'sale_amount', isset($_POST['scam_lost_amount'])?$_POST['scam_lost_amount']:'');
        update_post_meta($postid,'deposit_method', isset($_POST['scam_deposit_method'])?$_POST['scam_deposit_method']:'');
        update_post_meta($postid,'total_amount', isset($_POST['scam_total_amount'])?$_POST['scam_total_amount']:'');
        update_post_meta($postid,'privacy_policy', isset($_POST['scam_privacy'])?$_POST['scam_privacy']:'');  
    }
}
add_action('save_post','save_scams_post_meta_by_wp_admin');


?>