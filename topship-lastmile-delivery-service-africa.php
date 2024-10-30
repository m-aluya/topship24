<?php
/**
 * Plugin name: Topship
 * Description: Send and Receive items from your doorstep to any location in the world.
 * Author : Topship
 * Author URI: https://topship.africa
 * Plugin URI: https://topship.africa
 * Version: 1.0.10
 */


 if(!defined("ABSPATH")){
    exit;
 }
 require_once plugin_dir_path(__FILE__) . 'includes/class.topship-delivery-service-africa.php';

 class topshipLastMileDeliveryServiceAfrica {
     public function __construct() {
         add_action('admin_menu', [$this, 'topship_admin_page_plugin_menu']);
         add_action('admin_enqueue_scripts', [$this, 'enqueue_admin_scripts']);
     }
 
     public function topship_admin_page_plugin_menu() {
         add_menu_page(
             'Create your Topship account',
             'Topship',
             'manage_options',
             Class_topship_delivery_service_africa::topshipLink(),
             [$this, 'topship_admin_page_content']
         );
 
         add_submenu_page(
             Class_topship_delivery_service_africa::topshipLink(),
             'Contact Us',
             'Contact Us',
             'manage_options',
             Class_topship_delivery_service_africa::topshipLink() . '-contact-us',
             [$this, 'topship_contact_us_page_content']
         );
     }
 
     public function enqueue_admin_scripts($hook) {
         // Load Bootstrap on all plugin admin pages
         if (
             strpos($hook, Class_topship_delivery_service_africa::topshipLink()) !== false ||
             strpos($hook, Class_topship_delivery_service_africa::topshipLink() . '-contact-us') !== false ||
             strpos($hook, Class_topship_delivery_service_africa::topshipLink() . '-guide') !== false
         ) {
             // Enqueue Bootstrap CSS and JS
             wp_enqueue_style('bootstrap-css', 'https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css');
             wp_enqueue_script('bootstrap-js', 'https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.bundle.min.js', ['jquery'], null, true);
         }
     }
 
     public function topship_contact_us_page_content() {
         Class_topship_delivery_service_africa::topship_contact_us_page();
     }
 
     public function topship_admin_page_content() {
         if (!current_user_can('manage_options')) {
             return;
         }
 
         if (isset($_POST['my_admin_form_submitted'])) {
             $my_field_value = sanitize_text_field($_POST['my_field_name']);
             update_option('my_field_name', $my_field_value);
             echo '<div class="updated"><p>Settings saved.</p></div>';
         }
 
         $stored_value = get_option('my_field_name', '');
         Class_topship_delivery_service_africa::topship_register();
     }
 }
 
 new topshipLastMileDeliveryServiceAfrica();