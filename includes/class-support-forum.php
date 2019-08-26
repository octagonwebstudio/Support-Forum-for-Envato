<?php
/**
 *
 * @package Envato Support Forum
 * @author octagonwebstudio
 * @version 1.0
 * @since 1.0
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}
    
if( ! class_exists( 'OWS_ESF_Init_Forum' ) ) {

    class OWS_ESF_Init_Forum {

        /**
         * Returns Post ID
         * 
         * @access  private
         * @return  int
         */
        private $id = 0;

        /**
         * Returns post link
         * 
         * @access  private
         * @return  string
         */
        private $permalink = '';

        /**
         * Returns current user ID
         * 
         * @access  private
         * @return  int
         */
        private $user_id = 0;

        /**
         * Returns envato item ID
         * 
         * @access  private
         * @return  int
         */
        private $item_verify_id = 0;


        /**
         * Returns purchase form page ID
         * 
         * @access  private
         * @return  int
         */
        private $purchase_form_page_id = 0;

        /**
         * Returns purchase form page URL
         * 
         * @access  private
         * @return  int
         */
        private $purchase_form_page_url = '';
        

        /**
         * Append error notices list into this array
         * 
         * @access  private
         * @return  array
         */
        private $notices = array();

        public function __construct() {

            add_action( 'admin_notices', array( $this, 'required_plugin_admin_notice' ) );
            add_shortcode( 'ows-esf-purchase-form', array( $this, 'purchase_form_shortcode' ) );

            add_filter( 'use_block_editor_for_post_type', '__return_false' ); // remove on submission
            add_action( 'template_redirect', array( $this, 'process_forum' ) );
        }

        public function required_plugin_admin_notice() {

            if( ! class_exists( 'bbPress' ) ) {
                ?>
                <div class="error">
                    <p><?php esc_html_e( 'Envato Support Forum is enabled but not effective. It requires bbPress in order to work.', 'octagon-kc-elements' ); ?></p>
                </div>
                <?php
            }
        }

        /**
         * Return post ID
         * 
         * @version  1.0
         * @since  1.0
         *
         * @return  int
         */
        private function get_id() {

            $this->id = get_the_ID();

            return $this->id;
        }

        /**
         * Return post link
         * 
         * @version  1.0
         * @since  1.0
         *
         * @return  string
         */
        private function permalink() {

            $this->permalink = get_permalink( $this->get_id() );

            return $this->permalink;
        }


        /**
         * Return envato item ID
         * 
         * @version  1.0
         * @since  1.0
         *
         * @return  int
         */
        private function item_verify_id() {

            $this->item_verify_id = get_post_meta( $this->get_id(), 'ows_esf_item_verify_id', true );

            return $this->item_verify_id;
        }


        /**
         * Return user envato purchased items
         * 
         * @version  1.0
         * @since  1.0
         *
         * @return  array
         */
        private function user_envato_items() {

            $this->user_item = get_user_meta( $this->current_user_id(), 'ows_esf_user_item', true );

            return $this->user_item;
        }


        /**
         * Return current user ID
         * 
         * @version  1.0
         * @since  1.0
         *
         * @return  int
         */
        private function current_user_id() {

            $this->user_id = get_current_user_id();

            return $this->user_id;
        }


        /**
         * Return purchase form page ID
         * 
         * @version  1.0
         * @since  1.0
         *
         * @return  int
         */
        private function purchase_form_page_id() {

            $this->purchase_form_page_id = get_option( 'ows_esf_purchase_form_page_id', false );

            return $this->purchase_form_page_id;
        }


        /**
         * Return purchase form page URL
         * 
         * @version  1.0
         * @since  1.0
         *
         * @return  string
         */
        private function purchase_form_page_url() {

            $this->purchase_form_page_url = get_permalink( $this->purchase_form_page_id() );

            return $this->purchase_form_page_url;
        }


        /**
         * bbPress envato support forum initialize
         * 
         * @version  1.0
         * @since  1.0
         */
        public static function init_support_forum() {

            $page = array(
                'post_title'   => esc_html__( 'License Verification', 'ows-envato-support-forum' ),
                'post_content' => '[ows-esf-purchase-form]',
                'post_status'  => 'publish',
                'post_author'  => 1,
                'post_type'    => 'page'
            );

            $id = wp_insert_post( $page );

            update_option( 'ows_esf_purchase_form_page_id', absint( $id ) );

        }

        /**
         * bbPress check purchase code and process
         * 
         * @version  1.0
         * @since  1.0
         */
        public function process_forum() {

            global $wpdb;

            $forum_id               = $this->get_id();
            $post_url               = $this->permalink();
            $user_id                = $this->current_user_id();
            $item_verify_id         = $this->item_verify_id();
            $user_item              = $this->user_envato_items();
            $whitelist              = $this->get_whitelist_users();
            $purchase_form_page_url = $this->purchase_form_page_url();

            if( bbp_is_single_forum() || bbp_is_single_topic() ) {

                if( ! $user_id ) { // Must login to access the forum

                    $this->add_notice( 'Please login to access the forum.', 'ows-envato-support-forum' );

                    wp_redirect( wp_login_url( $post_url ) );
                    exit;
                }
                else {

                    if( empty( $item_verify_id ) || in_array( $user_id, $whitelist ) ) { // It's not a premium forum or you could be a trustworthy, you can dig it.
                        return;
                    }

                    $found = array_search( $item_verify_id, array_column( $user_item, 'id' ) );

                    if( false !== $found && null !== $found ) { // Customer purchased this item

                        $found_items = array_column( $user_item, 'id', 'supported_until' );

                        foreach( $found_items as $supported_until => $item_verify_id ) {
                            $timestamp = strtotime( $supported_until );

                            if( time() <= $timestamp ) {
                                $support_found = true;
                                break;
                            }
                            else { // Customer purchased but support expired
                                $support_found = false;
                                $this->add_notice( 'All of your Purchase code in this forum are expired. Please renew the support.', 'ows-envato-support-forum' );
                            }
                        }

                        if( ! $support_found ) { // If support expired, not allow to create a topic or reply
                            add_filter( 'bbp_current_user_can_access_create_topic_form', '__return_false' );
                            add_filter( 'bbp_current_user_can_access_create_reply_form', '__return_false' );
                        }

                    }
                    else { // Purchase code require to access the forum

                        $this->add_notice( 'Please add purchase code to access the forum.', 'ows-envato-support-forum' );

                        if( ! $purchase_form_page_url ) {
                            return;
                        }

                        wp_redirect( $purchase_form_page_url );
                        exit();
                    }
                }                
            }

            if( isset( $_POST['ows-envato-submit'] ) ) {

                $license = isset( $_POST['ows-envato-license'] ) ? sanitize_key( $_POST['ows-envato-license'] ) : '';

                if( empty( $license ) ) { // Purchase code is empty
                    $this->add_notice( 'Purchase code is required.', 'ows-envato-support-forum' );
                    return;
                }

                $purchase_data = OWS_ESF_Tools()->get_purchase_data( $license );

                if( isset( $purchase_data['error'] ) ) { // Please check the purchase code

                    if( isset( $purchase_data['description'] ) ) {
                        $this->add_notice( $purchase_data['description'], 'ows-envato-support-forum' );
                    }
                    else {
                        $this->add_notice( $purchase_data['error'], 'ows-envato-support-forum' );
                    }
                    
                }
                else {

                    if( isset( $purchase_data['item']['id'] ) ) {

                        $exist = (int) $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(purchase_code) FROM {$wpdb->prefix}ows_envato_license WHERE purchase_code = %s", $license ) );

                        if( $exist ) { // Already you added this purchase code
                            $this->add_notice( 'Purchase Code already exists.', 'ows-envato-support-forum' );
                        }
                        else {
                            $item['id']              = isset( $purchase_data['item']['id'] ) ? absint( $purchase_data['item']['id'] ) : '';
                            $item['name']            = isset( $purchase_data['item']['name'] ) ? sanitize_text_field( $purchase_data['item']['name'] ) : '';
                            $item['supported_until'] = isset( $purchase_data['supported_until'] ) ? sanitize_text_field( $purchase_data['supported_until'] ) : '';
                            $item['license']         = isset( $purchase_data['license'] ) ? sanitize_text_field( $purchase_data['license'] ) : '';
                            $item['buyer']           = isset( $purchase_data['buyer'] ) ? sanitize_text_field( $purchase_data['buyer'] ) : '';

                            if( empty( $user_item ) ) {

                                $new_item[$license] = $item;

                                update_user_meta( $user_id, 'ows_esf_user_item', array_map( 'sanitize_text_field', wp_unslash( $new_item ) ) );
                            }
                            else {

                                $new_item[$license] = $item;

                                $user_item = array_merge( $user_item, $new_item );

                                update_user_meta( $user_id, 'ows_esf_user_item', array_map( 'sanitize_text_field', wp_unslash( $user_item ) ) );
                            }

                            // Insert the item details and user details to 'ows_envato_license' table, Manage the details in License menu
                            $insert = $wpdb->insert(
                                $wpdb->prefix .'ows_envato_license',
                                array(
                                    'item_id'         => $item['id'],
                                    'item_name'       => $item['name'],
                                    'purchase_code'   => $license,
                                    'supported_until' => $item['supported_until'],
                                    'license_type'    => $item['license'],
                                    'buyer'           => $item['buyer'],
                                    'user'            => $user_id                                    
                                ),
                                array( '%d', '%s', '%s', '%s', '%s', '%s', '%d' )
                            );

                        }                        

                    }                    

                }

            }

        }

        /**
         * bbPress check purchase validation
         * 
         * @version  1.0
         * @since  1.0
         */
        public function purchase_form_shortcode() {

            $notices_html = '';

            if( $this->notices && null != $this->notices ) {
                foreach( $this->notices as $key => $notice ) {
                    if( ! empty( $notice ) ) {
                        $notices_html .= '<p>'. esc_html( $notice ) .'</p>';
                    }                    
                }
            }            

            $form = '
            <div class="envato-verify-form-wrap">
            <div class="notices">'. wp_kses( $notices_html, array( 'p' => array() ) ) .'</div>
            <form action="" method="post" class="envato-verify-form">        
                <h3 class="title">'. esc_html__( 'Purchase Validation', 'ows-envato-support-forum' ).'</h3>
                <p class="desc">'. esc_html__( 'Once you enter the valid purchase code it redirects you to the forum.', 'ows-envato-support-forum' ).'</p>
                <p class="field"><input type="text" id="ows-envato-license" name="ows-envato-license" placeholder="'. esc_attr__( 'Envato Purchase Code', 'ows-envato-support-forum' ).'" class="text" /></p>
                <p class="field"><input name="ows-envato-submit" type="submit" value="Submit" class="btn" /></p>
            </form>
            </div>
            ';

            return $form;

        }

        /**
         * Add notice to global access through this class
         * 
         * @version  1.0
         * @since  1.0
         */
        private function add_notice( $notice = '' ) {

            $this->notices[] = $notice;

            return array_filter( $this->notices );

        }


        /**
         * bbPress envato support forum whitelist users
         * 
         * @version  1.0
         * @since  1.0
         */
        public static function get_whitelist_users() {

            $whitelist = get_option( 'ows_esf_whitelist' );

            $users = ! empty( $whitelist ) ? explode( ',', $whitelist ) : array();

            return $users;

        }  

    }

    new OWS_ESF_Init_Forum;

}