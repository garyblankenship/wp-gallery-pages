<?php

class Gallery_Settings {
	/**
	 * Holds the values to be used in the fields callbacks
	 */
	private $options;

	/**
	 * Start up
	 */
	public function __construct() {
		add_action( 'admin_menu', array( $this, 'add_plugin_page' ) );
		add_action( 'admin_init', array( $this, 'page_init' ) );
	}

	/**
	 * Add options page
	 */
	public function add_plugin_page() {
		add_media_page(
			'Gallery Pages Settings',
			'Gallery Pages',
			'manage_options',
			'gallery-pages-admin',
			array( $this, 'create_admin_page' )
		);
	}

	/**
	 * Options page callback
	 */
	public function create_admin_page() {

		$this->options = get_option( 'gallery_pages' );
		?>
		<div class="wrap">
			<h2>Gallery Pages</h2>

			<form method="post" action="options.php">
				<?php
				// This prints out all hidden setting fields
				settings_fields( 'gallery_pages' );
				do_settings_sections( 'gallery-pages-admin' );
				submit_button();
				?>
			</form>
		</div>
		<?php
	}

	/**
	 * Register and add settings
	 */
	public function page_init() {
		register_setting(
			'gallery_pages', // Option group
			'gallery_pages', // Option name
			array( $this, 'sanitize' ) // Sanitize
		);

		add_settings_section(
			'gallery_pages_id', // ID
			'Gallery Pages Settings', // Title
			array( $this, 'print_section_info' ), // Callback
			'gallery-pages-admin' // Page
		);

		add_settings_field(
			'per_page', // ID
			'Number of Items Per Page', // Title
			array( $this, 'per_page_callback' ), // Callback
			'gallery-pages-admin', // Page
			'gallery_pages_id' // Section
		);
	}

	/**
	 * Sanitize each setting field as needed
	 *
	 * @param array $input Contains all settings fields as array keys
	 *
	 * @return array
	 */
	public function sanitize( $input ) {
		$new_input = array();
		if ( isset( $input['per_page'] ) ) {
			$new_input['per_page'] = absint( $input['per_page'] );
		}

		return $new_input;
	}

	/**
	 * Print the Section text
	 */
	public function print_section_info() {
		print 'Enter your settings below:';
	}

	/**
	 * Get the settings option array and print one of its values
	 */
	public function per_page_callback() {
		printf(
			'<input type="text" id="per_page" name="gallery_pages[per_page]" value="%s" />',
			isset( $this->options['per_page'] ) ? esc_attr( $this->options['per_page'] ) : ''
		);
	}

}

if ( is_admin() ) {
	$gallery_pages = new Gallery_Settings();
}
