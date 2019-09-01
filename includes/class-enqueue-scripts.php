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

if( ! class_exists( 'SFE_Enqueue_scripts' ) ) {

	class SFE_Enqueue_scripts {

		public function __construct() {

			add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_scripts' ), 99 );
			
		}

		/**
		 * Enqueue Scripts
		 * 
		 * @since  1.0
		 */
		public function enqueue_scripts() {

			/* ---------------------------------------------------------------------------
			 * CSS
			------------------------------------------------------------------------------ */

			wp_enqueue_style( 'sfe-support-forum', SFE_URL .'assets/css/support-forum.css', array(), '1.0', 'all' );
		}

	}

	new SFE_Enqueue_scripts;

}