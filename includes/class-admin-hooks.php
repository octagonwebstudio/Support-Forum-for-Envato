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
	
if( ! class_exists( 'OWS_ESF_Admin_Hooks' ) ) {

	class OWS_ESF_Admin_Hooks {

		public function __construct() {

            add_action( 'init', array( $this, 'install_forum' ) );

			add_filter( 'manage_users_columns', array( $this, 'manage_users_columns' ), 10, 1 );
			add_filter( 'manage_users_custom_column', array( $this, 'manage_users_custom_column' ), 10, 3 );			
		}

        /**
         * Install Forum
         * 
         * @version  1.0
         * @since  1.0
         */
        public function install_forum( $columns ) {

            $this->create_roles();
            $this->create_table();

        }

        /**
         * Create roles
         * 
         * @version  1.0
         * @since  1.0
         */
        public static function create_roles() {
            global $wp_roles;

            add_role(
                'purchaser',
                esc_html__( 'Purchaser', 'support-forum-for-envato' ),
                array(
                    'read' => true
                )
            );

        }

        /**
         * Create table
         * 
         * @version  1.0
         * @since  1.0
         */
        public static function create_table() {

            require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );

            dbDelta( esc_sql( self::get_schema() ) );
        }

        /**
         * Get Table schema.
         * 
         * @version  1.0
         * @since  1.0
         */
        public static function get_schema() {            

            global $wpdb;

            $collate = '';

            if( $wpdb->has_cap( 'collation' ) ) {
                $collate = $wpdb->get_charset_collate();
            }

            $tables = "
            CREATE TABLE {$wpdb->prefix}ows_envato_license (
              id INT(11) NOT NULL AUTO_INCREMENT,
              item_id VARCHAR(55) NOT NULL,
              item_name VARCHAR(100) NOT NULL,
              purchase_code VARCHAR(55) NOT NULL,
              supported_until VARCHAR(55) NOT NULL,
              license_type VARCHAR(55) NOT NULL,
              buyer VARCHAR(55) NOT NULL,
              user VARCHAR(55) NOT NULL,
              PRIMARY KEY  (id)
            ) $collate;
            ";

            return $tables;
        }

        /**
         * User custom column
         * 
         * @version  1.0
         * @since  1.0
         */
        public function manage_users_columns( $columns ) {

            $columns['purchase_codes'] = esc_html__( 'Purchase Codes', 'support-forum-for-envato' );
            $columns['items']          = esc_html__( 'Items', 'support-forum-for-envato' );

            return $columns;

        }

        /**
         * User custom column
         * 
         * @version  1.0
         * @since  1.0
         */
        public function manage_users_custom_column( $retval = '', $column = '', $user_id = 0 ) {

        	$user_item = get_user_meta( $user_id, 'ows_esf_user_item', true );

        	if( 'purchase_codes' ==  $column ) {        		
        		return is_array( $user_item ) ? count( $user_item ) : 0;
        	}
        	else if( 'items' ==  $column ) {

        		$items = is_array( $user_item ) ? array_filter( array_column( $user_item, 'name', 'id' ) ) : array();

        		$item_html = '';
        		foreach( $items as $id => $name ) {
        			$item_html .= sprintf( '<p>%s ( %d )</p>', $name, $id );
        		}
        		return $item_html;
        	}

        }

	}

	new OWS_ESF_Admin_Hooks;

}