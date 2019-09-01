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
    
if( ! class_exists( 'SFE_bbp_Metabox' ) ) {

    class SFE_bbp_Metabox {

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

            $current_item = get_post_meta( $forum_id, 'sfe_item_verify_id', true );

            $author_items = SFE_Tools()->get_author_items();
            ?>

            <p>
                <strong><label for="item-verify-id"><?php esc_html_e( 'Support Item:', 'support-forum-for-envato' ); ?></label></strong>
                <select id="item-verify-id" name="sfe_item_verify_id">
                    <option value="0"><?php esc_html_e( 'Choose Item', 'support-forum-for-envato' ); ?></option>
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

            if( isset( $_POST['sfe_item_verify_id'] ) ) {

                $author_items = SFE_Tools()->get_author_items();

                if( array_key_exists( $_POST['sfe_item_verify_id'], $author_items ) ) {

                    update_post_meta( $forum_id, 'sfe_item_verify_id', absint( $_POST['sfe_item_verify_id'] ) );
                }
            }

        }

    }

    new SFE_bbp_Metabox;

}