<?php

/**
 * Fired during plugin activation
 *
 * @link       http://wpgenie.org
 * @since      1.0.0
 *
 * @package    wc_lottery
 * @subpackage wc_lottery/includes
 */

/**
 * Fired during plugin activation.
 *
 * This class defines all code necessary to run during the plugin's activation.
 *
 * @since      1.0.0
 * @package    wc_lottery
 * @subpackage wc_lottery/includes
 * @author     wpgenie <info@wpgenie.org>
 */
class wc_lottery_Activator {
    /**
     * Activation function
     *
     * Create tables for WC lottery plugin
     *
     * @since    1.0.0
     */
    public static function activate() {

        global $wpdb;
        global $wp_version;

        $flag = false;
        $wp = '4.0';    // min WordPress version
        $php = '5.5';   // min PHP version

        if ( version_compare( PHP_VERSION, $php, '<' ) ) {
            $flag = 'PHP';
        } elseif ( version_compare( $wp_version, $wp, '<' ) ) {
            $flag = 'WordPress';
        } 

        if($flag){       
          $version = $php;
          if ('WordPress'==$flag) {
              $version = $wp;
          }

   				deactivate_plugins( basename( __FILE__ ) );
   				wp_die('<p>The <strong>WooCommerce Lottery</strong> plugin requires '.$flag.'  version '.$version.' or greater. If you need secure hosting with all requirements for this plugin contact us at <a href="mailto:info@wpgenie.org">info@wpgenie.org</a></p>','Plugin Activation Error',  array( 'response'=>200, 'back_link'=>TRUE ) );
        }  


        $data_table = $wpdb->prefix."wc_lottery_log";

        $sql = " CREATE TABLE IF NOT EXISTS $data_table (
                `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
                  `userid` bigint(20) unsigned NOT NULL,
                  `orderid` bigint(20) unsigned NOT NULL,
                  `lottery_id` bigint(20) unsigned DEFAULT NULL,
                  `date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                  PRIMARY KEY (`id`)
                );";

        require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
        dbDelta( $sql );
        wp_insert_term( 'lottery', 'product_type' );
    }
}
