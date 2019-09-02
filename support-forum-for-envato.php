<?php
/**
 * Plugin Name: Support Forum for Envato
 * Plugin URI: https://octagonwebstudio.com
 * Description: Support Forum for Envato is allows you to set different user permissions for certain threads according to user envato purchase codes.
 * Version: 1.0.1
 * Author: octagonwebstudio
 * Text Domain: support-forum-for-envato
 * Requires WP:   5.0
 * Requires PHP:  5.6
 * Domain Path: /languages/
*/

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}
    
if( ! class_exists( 'SFE' ) ) {

    class SFE {

        /**
         * Core Version.
         *
         */
        public $version = '1.0.1';

        /**
         * The single instance of the class.
         *
         * @since 1.0
         */
        protected static $_instance = null;

        /**
         * Plugin Core Instance.
         *
         * Ensures only one instance of Core is loaded or can be loaded.
         *
         * @since 1.0
         * @static
         * @return Core - Main instance.
         */
        public static function instance() {
            if( is_null( self::$_instance ) ) {
                self::$_instance = new self();
            }
            return self::$_instance;
        }

        /**
         * Constructor.
         */
        public function __construct() {

            $this->define_constants();
            $this->includes();
            $this->hooks();

            do_action( 'sfe_loaded' );
            
        }

        /**
         * Define Constants.
         */
        private function define_constants() {
            $this->define( 'SFE_VERSION', $this->version );
            $this->define( 'SFE_FILE', __FILE__ );
            $this->define( 'SFE_BASENAME', plugin_basename( __FILE__ ) );
            $this->define( 'SFE_PATH', plugin_dir_path( __FILE__ ) );
            $this->define( 'SFE_URL', plugin_dir_url( __FILE__ ) );
        }

        /**
         * Define constant if not set.
         *
         * @param string      $name  Constant name.
         * @param string|bool $value Constant value.
         */
        private function define( $name, $value ) {
            if ( ! defined( $name ) ) {
                define( $name, $value );
            }
        }

        /**
         * Include required files
         * 
         * @since  1.0
         */
        public function includes() {

            include_once SFE_PATH . '/includes/class-tools.php';
            include_once SFE_PATH . '/includes/class-admin-hooks.php';
            include_once SFE_PATH . '/includes/class-support-forum.php';
            include_once SFE_PATH . '/includes/class-bbp-settings.php';
            include_once SFE_PATH . '/includes/class-bbp-metabox.php';
            include_once SFE_PATH . '/includes/class-enqueue-scripts.php';
            
        }

        /**
         * Hook into actions and filters.
         *
         * @since 1.0
         */
        private function hooks() {
            register_activation_hook( SFE_FILE, array( 'SFE_Init_Forum', 'init_support_forum' ) );
            
            add_action( 'plugins_loaded', array( $this, 'load_plugin_textdomain' ) );
        }

        /**
         * Plugin localisation
         * 
         * @since  1.0
         */
        public function load_plugin_textdomain() {
            load_plugin_textdomain( 'support-forum-for-envato', false, plugin_basename( dirname( __FILE__ ) ) . '/languages' );
        }   
                
    }
}

/**
 * Main instance.
 *
 * Returns the main instance of Core.
 *
 * @version 1.0
 * @since  1.0
 * @return SFE
 */
if( ! function_exists( 'SFE' ) ) {
    function SFE() {
        return SFE::instance();
    }
}

$GLOBALS['sfe'] = SFE();