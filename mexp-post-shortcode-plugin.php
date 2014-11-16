<?php
/**
 * Lovingly borrowed from https://gist.github.com/paulgibbs/c4b50d07d04fd8da9410
 */

/**
 * Create our new service. Everything starts here.
 *
 * @param array $services Associative array of Media Explorer services to load; key is a string, value is a MEXP_Template object.
 * @return array $services Associative array of Media Explorer services to load; key is a string, value is a MEXP_Template object.
 */
function wpslc_add_mexp_post_shortcode_service( array $services ) {
  // This key name is important. You must use the same name for the tabs() and labels() methods in Test_MEXP_New_Service.
	$services['wpslc_post_shortcode_service'] = new WPSLC_MEXP_Post_Shortcode_Service;
	return $services;
}
add_filter( 'mexp_services', 'wpslc_add_mexp_post_shortcode_service' );


/**
 * Backbone templates for various views for your new service
 */
class WPSLC_MEXP_Post_Shortcode_Template extends MEXP_Template {

	/**
	 * Outputs the Backbone template for an item within search results.
	 *
	 * @param string $id  The template ID.
	 * @param string $tab The tab ID.
	 */
	public function item( $id, $tab ) {
	?>
		<div id="mexp-item-<?php echo esc_attr( $tab ); ?>-{{ data.id }}" class="mexp-item-area mexp-item" data-id="{{ data.id }}">
			<div class="mexp-item-container clearfix">
				<div class="mexp-item-thumb">
					<img src="{{ data.thumbnail }}">
				</div>

				<div class="mexp-item-main">
					<div class="mexp-item-content">
						{{ data.content }}
					</div>
					<div class="mexp-item-date">
						{{ data.date }}
					</div>
				</div>

			</div>
		</div>

		<a href="#" id="mexp-check-{{ data.id }}" data-id="{{ data.id }}" class="check" title="<?php esc_attr_e( 'Deselect', 'mexp' ); ?>">
			<div class="media-modal-icon"></div>
		</a>
	<?php
	}

	/**
	 * Outputs the Backbone template for a select item's thumbnail in the footer toolbar.
	 *
	 * @param string $id The template ID.
	 */
	public function thumbnail( $id ) {
	}

	/**
	 * Outputs the Backbone template for a tab's search fields.
	 *
	 * @param string $id  The template ID.
	 * @param string $tab The tab ID.
	 */
	public function search( $id, $tab ) {
	?>
		<form action="#" class="mexp-toolbar-container clearfix tab-all">
			<input
				type="text"
				name="q"
				value="{{ data.params.q }}"
				class="mexp-input-text mexp-input-search"
				size="40"
				placeholder="<?php esc_attr_e( 'Search for posts', 'mexp' ); ?>"
			>
			<input class="button button-large" type="submit" value="<?php esc_attr_e( 'Search', 'mexp' ); ?>">

			<div class="spinner"></div>
		</form>
	<?php
	}
}

/**
 * Your new service.
 *
 */
class WPSLC_MEXP_Post_Shortcode_Service extends MEXP_Service {

	/**
	 * Constructor.
	 *
	 * Creates the Backbone view template.
	 */
	public function __construct() {
		$this->set_template( new WPSLC_MEXP_Post_Shortcode_Template );
	}

	/**
	 * Fired when the service is loaded.
	 *
	 * Allows the service to enqueue JS/CSS only when it's required. Akin to WordPress' load action.
	 */
	public function load() {
		add_filter( 'mexp_tabs',   array( $this, 'tabs' ),   10, 1 );
		add_filter( 'mexp_labels', array( $this, 'labels' ), 10, 1 );
	}

	/**
	 * Handles the AJAX request and returns an appropriate response. This should be used, for example, to perform an API request to the service provider and return the results.
	 *
	 * @param array $request The request parameters.
	 * @return MEXP_Response|bool|WP_Error A MEXP_Response object should be returned on success, boolean false should be returned if there are no results to show, and a WP_Error should be returned if there is an error.
	 */
	public function request( array $request ) {
		// You'll want to handle connection errors to your service here. Look at the Twitter and YouTube implementations for how you could do this.

		// Create the response for the API
		$response = new MEXP_Response();

		$item = new MEXP_Response_Item();
		$item->set_content( 'WordPress 3.6 â€œOscarâ€' );
		$item->set_date( strtotime( '01-08-2013' ) );
		$item->set_date_format( 'g:i A - j M y' );
		$item->set_id( 117 );
		$item->set_thumbnail( 'https://i.ytimg.com/vi/pAHn9f6At_g/mqdefault.jpg' );
		$item->set_url( esc_url_raw( 'http://www.youtube.com/watch?v=0kzhpkam7t8' ) );

		$response->add_item( $item );
		return $response;
	}

	/**
	 * Returns an array of tabs (routers) for the service's media manager panel.
	 *
	 * @param array $tabs Associative array of default tab items.
	 * @return array Associative array of tabs. The key is the tab ID and the value is an array of tab attributes.
	 */
	public function tabs( array $tabs ) {
		$tabs['wpslc_post_shortcode_service'] = array(
			'all' => array(
				'defaultTab' => true,
				'text'       => _x( 'All', 'Tab title', 'mexp' ),
			),
		);

		return $tabs;
	}

	/**
	 * Returns an array of custom text labels for this service.
	 *
	 * @param array $labels Associative array of default labels.
	 * @return array Associative array of labels.
	 */
	 public function labels( array $labels ) {
	 	$labels['wpslc_post_shortcode_service'] = array(
			'insert'    => __( 'Insert', 'mexp' ),
			'noresults' => __( 'No posts matched your search query.', 'mexp' ),
			'title'     => __( 'Insert Post Shortcode', 'mexp' ),
		);

	 	return $labels;
	}
}