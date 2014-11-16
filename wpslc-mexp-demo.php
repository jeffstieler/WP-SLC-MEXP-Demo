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

foreach ( glob( dirname( __FILE__ ) . '/services/*/service.php' ) as $service ) {

	require( $service );

}

require( 'featured-youtube-video.php' );
