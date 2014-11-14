<?php
/**
 * Lovingly borrowed from https://gist.github.com/paulgibbs/c4b50d07d04fd8da9410
 */

/**
 * Add a stylesheet for our new service.
 */
function wpslc_add_mexp_meetup_style() {

	wp_enqueue_style( 'wpslc_mexp_meetup', plugins_url( 'mexp-meetup.css', __FILE__ ) );

}
add_action( 'mexp_enqueue', 'wpslc_add_mexp_meetup_style' );

/**
 * Create our new service. Everything starts here.
 *
 * @param array $services Associative array of Media Explorer services to load; key is a string, value is a MEXP_Template object.
 * @return array $services Associative array of Media Explorer services to load; key is a string, value is a MEXP_Template object.
 */
function wpslc_add_mexp_meetup_service( array $services ) {
  // This key name is important. You must use the same name for the tabs() and labels() methods in Test_MEXP_New_Service.
	$services['wpslc_meetup_service'] = new WPSLC_MEXP_Meetup_Service;
	return $services;
}
add_filter( 'mexp_services', 'wpslc_add_mexp_meetup_service' );


/**
 * Backbone templates for various views for your new service
 */
class WPSLC_MEXP_Meetup_Template extends MEXP_Template {

	/**
	 * Outputs the Backbone template for an item within search results.
	 *
	 * @param string $id  The template ID.
	 * @param string $tab The tab ID.
	 */
	public function item( $id, $tab ) {
	?>
		<div id="mexp-item-<?php echo esc_attr( $tab ); ?>-{{ data.id }}" class="mexp-item-area" data-id="{{ data.id }}">
			<div class="mexp-item-container clearfix">

				<div class="mexp-item-main">
					<div class="mexp-item-content">
						<h3>{{ data.content }}</h3>
						<span class="meetup-group">{{ data.meta.group.name }}</span>
						<span class="event-time">{{ data.date }}</span>
						{{{ data.meta.description }}}
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
				placeholder="<?php esc_attr_e( 'Search Meetup', 'mexp' ); ?>"
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
class WPSLC_MEXP_Meetup_Service extends MEXP_Service {

	/**
	 * Constructor.
	 *
	 * Creates the Backbone view template.
	 */
	public function __construct() {
		$this->set_template( new WPSLC_MEXP_Meetup_Template );
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

		// it's a good idea to keep our API key out of the code
		$api_key = (string) apply_filters( 'mexp_meetup_api_key', '' );

		// exit with an error if the API key value wasn't filtered in
		if ( empty( $api_key ) ) {

			return new WP_Error( 'missing_meetup_api_key', 'Missing API key for Meetup' );

		}

		// build the Meetup API request URL (hitting /open_events/ endpoint)
		$api_params = array(
			'key'  => $api_key,
			'text' => $request['params']['q'],
			'page' => 5
		);

		$meetup_request_url = add_query_arg( $api_params, 'https://api.meetup.com/2/open_events' );

		// actually make the request, setting a timeout of 10 seconds in case the network is slow
		$meetup_response    = wp_remote_get( $meetup_request_url, array( 'timeout' => 10 ) );

		// exit with an error if something went wrong with the request (usually a timeout)
		if ( is_wp_error( $meetup_response ) ) {

			return $meetup_response;

		}

		// successful responses have an HTTP response code of 200 "OK"
		$meetup_response_code = wp_remote_retrieve_response_code( $meetup_response );

		if ( 200 !== $meetup_response_code ) {

			return new WP_Error( $meetup_response_code, 'Non 200 HTTP Response from Meetup API' );

		}

		// the Meetup API responds in JSON by default
		$response_data = json_decode( wp_remote_retrieve_body( $meetup_response ) );

		// exit with an error if the JSON decode fails
		if ( is_null( $response_data ) ) {

			return new WP_Error( 'json_parse_error', 'Error parsing JSON response from Meetup API' );

		}

		// the Meetup API will populate "problem", "code", and "details" if a required
		// field is missing, or some other API error occurred
		if ( isset( $response_data->problem ) ) {

			return new WP_Error( $response_data->code, $response_data->problem, $response_data->details );

		}

		// *wipes brow* assume we're good from here - create the response for MEXP
		$mexp_response = new MEXP_Response();

		foreach ( $response_data->results as $event ) {

			$item = new MEXP_Response_Item();

			$item->set_id( $event->id );
			$item->set_url( $event->event_url );
			$item->set_content( $event->name );
			$item->set_date( 1416448800 - 25200 );
			$item->set_date( ( $event->time / 1000 ) + ( $event->utc_offset / 1000 ) );
			$item->set_date_format( 'l, M j, Y, g:i A' );

			$item->add_meta( 'description', isset( $event->description ) ? $event->description : '' );
			$item->add_meta( 'group', (array) $event->group );

			$mexp_response->add_item( $item );

		}

		return $mexp_response;
	}

	/**
	 * Returns an array of tabs (routers) for the service's media manager panel.
	 *
	 * @param array $tabs Associative array of default tab items.
	 * @return array Associative array of tabs. The key is the tab ID and the value is an array of tab attributes.
	 */
	public function tabs( array $tabs ) {
		$tabs['wpslc_meetup_service'] = array(
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
	 	$labels['wpslc_meetup_service'] = array(
			'insert'    => __( 'Insert', 'mexp' ),
			'noresults' => __( 'No events matched your search query.', 'mexp' ),
			'title'     => __( 'Insert Meetup', 'mexp' ),
		);

	 	return $labels;
	}
}