<?php
/**
 *
 * @package Support Forum for Envato
 * @author octagonwebstudio
 * @version 1.0
 * @since 1.0
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}
    
if( ! class_exists( 'SFE_Tools' ) ) {

    class SFE_Tools {

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
         * Return array of data using envato api url
         * 
         * @version  1.0
         * @since  1.0
         */
        public static function run_api_process( $url = '' ) {

            $envato_api_key = get_option( 'sfe_envato_api_key' );

            if( '' == $url || '' == $envato_api_key ) {
                return false;
            }

            $headers = array(
                'Authorization' => 'Bearer '. $envato_api_key
            );

            $args = array(
                'user-agent' => esc_html__( 'Purchase code verification', 'support-forum-for-envato' ),
                'headers'    => $headers
            );

            $response = wp_remote_get( $url, $args );

            $body = wp_remote_retrieve_body( $response );

            $data = json_decode( $body );

            return $data;

        }

        /**
         * Return purchase data
         * 
         * @version  1.0
         * @since  1.0
         */
        public static function get_purchase_data( $purchase_code = '' ) { // '6654844e-2fc1-4b20-b6d0-ec6e48ef12ac'

            $url = 'https://api.envato.com/v3/market/author/sale?code='. $purchase_code;

            $purchase_data = self::run_api_process( $url );

            return $purchase_data;

        }

        /**
         * Return author items based on api tokens
         * 
         * @version  1.0
         * @since  1.0
         */
        public static function get_author_items() {

            $url = 'https://api.envato.com/v1/market/private/user/username.json';

            $username = self::run_api_process( $url );

            if( ! isset( $username['username'] ) ) {
                return false;
            }

            $total_items = array();

            for( $i=1; $i < 10; $i++ ) { 

                $author_items_url = 'https://api.envato.com/v1/discovery/search/search/item?username='. esc_attr( $username['username'] ) .'&page_size=100&page='.$i;

                $author_items = self::run_api_process( $author_items_url );

                $total_items = array_merge( $total_items, $author_items['matches'] );

                if( count( $author_items['matches'] ) < 100 ) {
                    break;
                }
            }

            $author_items = array();

            if( ! empty( $total_items ) ) {
                foreach( $total_items as $key => $item ) {
                    if( isset( $item['id'] ) && isset( $item['name'] ) ) {
                        $author_items[$item['id']] = $item['name'];
                    }
                }
            }

            return ! empty( $author_items ) ? $author_items : false;

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
 * @return SFE_Tools
 */
if( ! function_exists( 'SFE_Tools' ) ) {
    function SFE_Tools() {
        return SFE_Tools::instance();
    }
}

$GLOBALS['sfe-tools'] = SFE_Tools();