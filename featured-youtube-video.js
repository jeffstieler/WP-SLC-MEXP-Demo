( function( $, _ ) {

	var media = wp.media,
		service = mexp.services.youtube,
		pf_prototype = media.view.MediaFrame.Post.prototype;

	var YoutubeFrame = media.view.MediaFrame.extend({
		initialize: function() {
			/**
			 * call 'initialize' directly on the parent class
			 */
			media.view.MediaFrame.prototype.initialize.apply( this, arguments );

			_.defaults( this.options, {
				selection: [],
				library:   {},
				multiple:  false,
				state:    'mexp-service-youtube'
			});

			var id = 'mexp-service-' + service.id;
			var controller = {
				id      : id,
				router  : id + '-router',
				toolbar : id + '-toolbar',
				title   : service.labels.title,
				tabs    : service.tabs
			};

			for ( var tab in service.tabs ) {

				// Content
				this.on( 'content:render:' + id + '-content-' + tab, _.bind( pf_prototype.mexpContentRender, this, service, tab ) );

				// Set the default tab
				if ( service.tabs[tab].defaultTab )
					controller.content = id + '-content-' + tab;

			}

			this.states.add([
				new media.controller.MEXP( controller )
			]);

			// Tabs
			this.on( 'router:create:' + id + '-router', this.createRouter, this );
			this.on( 'router:render:' + id + '-router', _.bind( pf_prototype.mexpRouterRender, this, service ) );

			// Toolbar
			this.on( 'toolbar:create:' + id + '-toolbar', pf_prototype.mexpToolbarCreate, this );

		}

	});

	var frame = new YoutubeFrame();

	$('.set-featured-youtube-video').click(function(e) {

		e.preventDefault();

		frame.open();

	});

} )( jQuery, _ );