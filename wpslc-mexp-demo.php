<?php

/**
 * Plugin Name: WP-SLC Meetup Media Explorer Demo
 * Description: An example implementation of a new service for the Media Explorer.
 * Author: Jeff Stieler
 * Author URI: http://voceplatforms.com/
 * License: GPLv2 or later
 * Requires at least: 3.6
 * Version: 0.1
 */

function wp_slc_mexp_demo_register_services() {

	foreach ( glob( dirname( __FILE__ ) . '/services/*/service.php' ) as $service ) {

		require( $service );

	}

	require( 'featured-youtube-video.php' );

}

add_action( 'mexp_init', 'wp_slc_mexp_demo_register_services' );
