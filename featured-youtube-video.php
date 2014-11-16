<?php

/**
 * Reuse the Media Explorer's Youtube search for a custom meta field
 */
class Featured_Youtube_Video {

	/**
	 * Initialize plugin
	 */
	function init() {

		add_action( 'add_meta_boxes_post', array( $this, 'register_meta_box' ) );

		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_script' ) );

	}

	/**
	 * Enqueue javascript dependency
	 */
	function enqueue_script() {

		wp_enqueue_script( 'featured-youtube-video', plugins_url( 'featured-youtube-video.js', __FILE__ ), array( 'jquery', 'underscore' ), false, true );

	}

	/**
	 * Add "Featured Youtube" meta box to posts
	 */
	function register_meta_box() {

		add_meta_box( 'featured-youtube-video', 'Featured Youtube Video', array( $this, 'display_meta_box' ), 'post', 'normal' );

	}

	/**
	 * Meta box display callback
	 *
	 * @param object $post WP_Post
	 * @param array $box metabox data
	 */
	function display_meta_box( $post, $box ) {

		?>
		<a href="#" class="set-featured-youtube-video">Set featured Youtube video</a>
		<?php

	}

}

add_action( 'wp_loaded', array( new Featured_Youtube_Video(), 'init' ) );
