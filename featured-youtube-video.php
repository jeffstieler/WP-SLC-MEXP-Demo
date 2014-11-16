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

		$featured_video      = get_post_meta( $post->id, 'featured-youtube-video', true );
		$set_link_classes    = array( 'set-featured-youtube-video' );
		$remove_link_classes = array( 'remove-featured-youtube-video' );

		if ( $featured_video ) {

			$set_link_classes[] = 'hidden';

		} else {

			$remove_link_classes[] = 'hidden';

		}

		?>
		<input type="hidden" name="featured-youtube-video" id="featured-youtube-video-url" value="<?php echo esc_url( $featured_video ); ?>" />
		<pre class="featured-youtube-video-preview"><?php echo esc_url( $featured_video ); ?></pre>
		<a href="#" class="<?php echo implode( ' ', $set_link_classes ); ?>">Set featured Youtube video</a>
		<a href="#" class="<?php echo implode( ' ', $remove_link_classes ); ?>">Remove featured Youtube video</a>
		<?php

	}

}

add_action( 'wp_loaded', array( new Featured_Youtube_Video(), 'init' ) );
