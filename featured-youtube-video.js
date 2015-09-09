( function( $, _ ) {

	var media = wp.media,
		service = mexp.services.youtube,
		pf_prototype = media.view.MediaFrame.Post.prototype,
		$set_link = $('.set-featured-youtube-video'),
		$remove_link = $('.remove-featured-youtube-video'),
		$input = $('#featured-youtube-video-url'),
		$preview = $('.featured-youtube-video-preview');

	/**
	 * Override the MEXP controller to change insert functionality
	 */
	var YoutubeFrameController = media.controller.MEXP.extend({
		mexpInsert: function() {

			var selection = this.frame.content.get().getSelection(),
			urls          = [];

			selection.each( function( model ) {
				urls.push( model.get( 'url' ) );
			}, this );

			var url = urls[0];

			$input.val(url);
			$preview.html( '<iframe style="max-width: 100%; max-height: 300px;" frameborder="0" allowfullscreen src="' + url.replace('watch?v=', 'embed/') + '"></iframe>' );

			$set_link.addClass('hidden');
			$remove_link.removeClass('hidden');

			selection.reset();
			this.frame.close();

		}
	});

	/**
	 * Extend MediaFrame and reuse MEXP logic to setup just the MEXP Youtube router/toolbar/content
	 */
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
				title   : 'Select Featured Youtube Video',
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
				new YoutubeFrameController( controller )
			]);

			// Tabs
			this.on( 'router:create:' + id + '-router', this.createRouter, this );
			this.on( 'router:render:' + id + '-router', _.bind( pf_prototype.mexpRouterRender, this, service ) );

			// Toolbar
			this.on( 'toolbar:create:' + id + '-toolbar', this.toolbarCreate, this );

		},

		/**
		 * Custom toolbar creation method so we can set our own button text
		 */
		toolbarCreate : function( toolbar ) {

			toolbar.view = new media.view.Toolbar.MEXP( {
				controller : this,
				items: {
					inserter     : {
						id       : 'mexp-button',
						style    : 'primary',
						text     : 'Set as featured',
						priority : 80,
						click    : function() {
							this.controller.state().mexpInsert();
						}
					}
				}
			} );

		}

	});

	var frame = new YoutubeFrame();

	$set_link.click(function(e) {

		e.preventDefault();

		frame.open();

	});

	$remove_link.click(function(e) {

		e.preventDefault();

		$set_link.removeClass('hidden');

		$input.val('');

		$preview.html('');

		$remove_link.addClass('hidden');

	});

} )( jQuery, _ );