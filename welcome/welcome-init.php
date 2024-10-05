<?php
/**
 * Welcome Page Init
 *
 * Welcome page initializer.
 *
 * @since 	2.3.0
 * @package BPWP
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Add the transient on plugin activation.
if ( ! function_exists( 'bpwp_welcome_page' ) ) {
	// Hook that runs on plugin activation.
	register_activation_hook( BPWP_PLUGIN_FILE, 'bpwp_welcome_activate' );

	/**
	 * Add the transient.
	 *
	 * Add the welcome page transient.
	 *
	 * @since 2.3.0
	 */
	function bpwp_welcome_activate() {
		// Transient max age is 60 seconds.
		set_transient( '_welcome_redirect_bpwp', true, 60 );
	}
}

// Delete the Transient on plugin deactivation.
if ( ! function_exists( 'bpwp_welcome_page' ) ) {
	// Hook that runs on plugin deactivation.
	register_deactivation_hook( BPWP_PLUGIN_FILE, 'bpwp_welcome_deactivate' );

	/**
	 * Delete the Transient on plugin deactivation.
	 *
	 * Delete the welcome page transient.
	 *
	 * @since   2.3.0
	 */
	function bpwp_welcome_deactivate() {
	  delete_transient( '_welcome_redirect_bpwp' );
	}
}

// Safe Welcome Page Redirect.
if ( ! function_exists( 'bpwp_safe_welcome_redirect' ) ) {
	// Add to `admin_init`.
	add_action( 'admin_init', 'bpwp_safe_welcome_redirect' );

	/**
	 * Safe Welcome Page Redirect.
	 *
	 * Safe welcome page redirect which happens only
	 * once and if the site is not a network or MU.
	 *
	 * @since 	2.3.0
	 */
	function bpwp_safe_welcome_redirect() {
		// Bail if no activation redirect transient is present. (if ! true).
		if ( ! get_transient( '_welcome_redirect_bpwp' ) ) {
			return;
		}

		// Delete the redirect transient.
		delete_transient( '_welcome_redirect_bpwp' );

		// Bail if activating from network or bulk sites.
		if ( is_network_admin() || isset( $_GET['activate-multi'] ) ) {
			return;
		}

		// Redirects to Welcome Page.
		// Redirects to `your-domain.com/wp-admin/plugin.php?page=bpwp_welcome_page`.
		wp_safe_redirect( add_query_arg(
			array(
				'page' => 'bpwp_welcome_page'
				),
			admin_url( 'plugins.php' )
		) );
	}
}
