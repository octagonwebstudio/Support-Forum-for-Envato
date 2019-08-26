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
    
if( ! class_exists( 'OWS_ESF_bbp_Metabox' ) ) {

    class OWS_ESF_bbp_Metabox {

        public function __construct() {

            add_action( 'bbp_forum_metabox', array( $this, 'bbp_forum_metabox' ) );
            add_action( 'bbp_forum_attributes_metabox_save', array( $this, 'bbp_forum_attributes_metabox_save' ) );
            
        }

        /**
         * bbPress forum metabox
         * 
         * @version  1.0
         * @since  1.0
         */
        public function bbp_forum_metabox( $forum_id ) {

            $current_item = get_post_meta( $forum_id, 'ows_esf_item_verify_id', true );

            $author_items = OWS_ESF_Tools()->get_author_items();
            ?>

            <p>
                <strong><label for="item-verify-id"><?php esc_html_e( 'Support Item:', 'ows-envato-support-forum' ); ?></label></strong>
                <select id="item-verify-id" name="ows_esf_item_verify_id">
                    <option value="0"><?php esc_html_e( 'Choose Item', 'ows-envato-support-forum' ); ?></option>
                    <?php
                    if( $author_items ) :
                        foreach( $author_items as $id => $name ) :
                            ?>
                            <option value="<?php echo esc_attr( $id ); ?>" <?php selected( $current_item, $id, true ); ?>><?php echo esc_html( $name ); ?></option>
                            <?php
                        endforeach;
                    endif;
                    ?>
                </select>
            </p>
            <?php

        }

        /**
         * bbPress forum save metabox
         * 
         * @version  1.0
         * @since  1.0
         */
        public function bbp_forum_attributes_metabox_save( $forum_id ) {

            if( empty( $forum_id ) ) {
                return;
            }

            if( isset( $_POST['ows_esf_item_verify_id'] ) ) {

                $author_items = OWS_ESF_Tools()->get_author_items();

                if( array_key_exists( $_POST['ows_esf_item_verify_id'], $author_items ) ) {

                    update_post_meta( $forum_id, 'ows_esf_item_verify_id', absint( $_POST['ows_esf_item_verify_id'] ) );
                }
            }

        }

    }

    new OWS_ESF_bbp_Metabox;

}