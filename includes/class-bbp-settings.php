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
    
if( ! class_exists( 'SFE_Settings' ) ) {

    class SFE_Settings {

        public function __construct() {

            add_filter( 'bbp_admin_get_settings_sections', array( $this, 'admin_settings_sections' ), 99, 1);
            add_filter( 'bbp_admin_get_settings_fields', array( $this, 'admin_settings_field' ) );
            add_filter( 'bbp_map_settings_meta_caps', array( $this, 'bbp_meta_caps' ), 10, 4 );
            
        }

        /**
         * bbPress Setting Sections
         * 
         * @version  1.0
         * @since  1.0
         */
        public function admin_settings_sections( $sections ) {

            $sections['bbp_settings_envato_support_forum'] = array(
                'title' => esc_html__( 'Support Forum for Envato', 'support-forum-for-envato' ),
                'callback' => call_user_func( array( $this, 'bbp_settings_envato_support_forum_callback' ) ),
                'page'  => 'bbpress'
            );

            return $sections;

        }

        public function bbp_settings_envato_support_forum_callback() {
        }

        /**
         * bbPress Setting Fields
         * 
         * @version  1.0
         * @since  1.0
         */
        public function admin_settings_field( $settings ) {

            $settings['bbp_settings_envato_support_forum'] = array(
                'sfe_envato_api_key' => array(
                    'title'             => esc_html__( 'Envato API Token', 'support-forum-for-envato' ),
                    'callback'          => array( $this, 'settings_callback_envato_api_token' ),
                    'sanitize_callback' => 'sanitize_text_field',
                    'args'              => array()
                ),
                'sfe_whitelist' => array(
                    'title'             => esc_html__( 'User Whitelist', 'support-forum-for-envato' ),
                    'callback'          => array( $this, 'settings_callback_whitelist' ),
                    'sanitize_callback' => 'sanitize_text_field',
                    'args'              => array()
                )
            );

            return $settings;

        }

        public function settings_callback_envato_api_token() {
            ?>
            <input name="sfe_envato_api_key" id="sfe_envato_api_key" type="text" value="<?php bbp_form_option( 'sfe_envato_api_key', '' ); ?>" class="regular-text code" />
            <?php
        }

        public function settings_callback_whitelist() {
            ?>
            <input name="sfe_whitelist" id="sfe_whitelist" type="text" value="<?php bbp_form_option( 'sfe_whitelist', '' ); ?>" class="regular-text code" /><span><?php esc_html_e( 'Enter User ID separated with commas.', 'support-forum-for-envato' ); ?></span>
            <?php
        }

        /**
         * bbPress setting section extends cap
         * 
         * @version  1.0
         * @since  1.0
         */
        public function bbp_meta_caps( $caps, $cap, $user_id, $args ) {

            if( 'bbp_settings_envato_support_forum' == $cap ) {
                $caps = array( 'keep_gate' );
            }

            return $caps;
        }

    }

    new SFE_Settings;

}