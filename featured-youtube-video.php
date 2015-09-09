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

		add_action( 'save_post', array( $this, 'save_featured_video_url' ) );

	}

	/**
	 * Enqueue javascript dependency
	 *
	 * @param string $hook_suffix
	 */
	function enqueue_script( $hook_suffix ) {

		if ( in_array( $hook_suffix, array( 'post.php', 'post-new.php' ) ) && ( 'post' === get_post_type() ) ) {

			wp_enqueue_script( 'featured-youtube-video', plugins_url( 'featured-youtube-video.js', __FILE__ ), array( 'jquery', 'underscore' ), false, true );

		}

	}

	/**
	 * Add "Featured Youtube" meta box to posts
	 */
	function register_meta_box() {

		add_meta_box( 'featured-youtube-video', 'Featured Youtube Video', array( $this, 'display_meta_box' ), 'post', 'normal' );

	}

	/**
	 * Save the featured video URL on post save
	 *
	 * @param WP_Post $post
	 */
	function save_featured_video_url( $post_id ) {

		if ( wp_is_post_autosave( $post_id ) || wp_is_post_revision( $post_id ) || ( 'post' !== get_post_type( $post_id ) ) ) {

			return $post_id;

		}

		$featured_video_url = isset( $_POST['featured-youtube-video'] ) ? esc_url( $_POST['featured-youtube-video'] ) : '';

		update_post_meta( $post_id, 'featured-youtube-video', $featured_video_url );

	}

	/**
	 * Meta box display callback
	 *
	 * @param object $post WP_Post
	 * @param array $box metabox data
	 */
	function display_meta_box( $post, $box ) {

		$featured_video      = get_post_meta( $post->ID, 'featured-youtube-video', true );
		$set_link_classes    = array( 'set-featured-youtube-video' );
		$remove_link_classes = array( 'remove-featured-youtube-video' );

		if ( $featured_video ) {

			$set_link_classes[] = 'hidden';

		} else {

			$remove_link_classes[] = 'hidden';

		}

		?>
		<input type="hidden" name="featured-youtube-video" id="featured-youtube-video-url" value="<?php echo esc_url( $featured_video ); ?>" />
		<div class="featured-youtube-video-preview">
		<?php if ( $featured_video ) : ?>
			<iframe style="max-width: 100%; max-height: 300px;" frameborder="0" allowfullscreen src="<?php echo str_replace( 'watch?v=', 'embed/', esc_url( $featured_video ) ); ?>"></iframe>
		<?php endif; ?>
		</div>
		<a href="#" class="<?php echo implode( ' ', $set_link_classes ); ?>">Set featured Youtube video</a>
		<a href="#" class="<?php echo implode( ' ', $remove_link_classes ); ?>">Remove featured Youtube video</a>
		<?php

	}

}

add_action( 'wp_loaded', array( new Featured_Youtube_Video(), 'init' ) );
