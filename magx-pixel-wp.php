<?php
/*
 * Plugin Name:       MAGX Pixel WP
 * Plugin URI:        https://themagxgroup.om
 * Description:       A simple plugin to implement the MAGX Retargeting Pixel from The Trade Desk
 * Version:           0.0.1
 * Requires at least: 5.2
 * Requires PHP:      5.6
 * Author:            MAGX
 * Author URI:        https://theMAGXgroup.com
 * License:           MIT
 * License URI:       https://opensource.org/license/mit/
 * Text Domain:       magx
 * Support:      	  digital-ads@magxgroup.com
 */	

add_action( 'admin_init', 'magx_pixel_settings_init' );
add_action( 'admin_menu', 'magx_pixel_options_page' );
add_filter( 'plugin_action_links_' . plugin_basename(__FILE__), 'magx_pixel_add_plugin_action_links' );

register_deactivation_hook( __FILE__, 'magx_pixel_deactivate' );

function magx_pixel_deactivate(){
	
}

function magx_pixel_get_support_email(){
	return "digital-ads@magxgroup.com";
}

function magx_pixel_add_plugin_action_links( $links ){
	$support_link = array( '<a href="mailto:'. magx_pixel_get_support_email() .'">Email Support</a>');
	return array_merge( $links, $support_link );

}

/**
 * @internal never define functions inside callbacks.
 * these functions could be run multiple times; this would result in a fatal error.
 */

/**
 * custom option and settings
 */
function magx_pixel_settings_init() {
	// Register a new setting for "magx_pixel" page
	register_setting( 'magx_pixel', 'magx_pixel' );

	// Register a new section
	add_settings_section(
		'magx_ttd_pixel_section',
		__( 'MAGX Pixel Settings for The Trade Desk Pixel', 'magx' ), 'magx_ttd_pixel_section_callback',
		'magx_pixel'
	);

	// Register a new field in the "magx_ttd_pixel_section" section, inside the "magx_pixel" page.
	add_settings_field(
		'magx_pixel_id', // As of WP 4.6 this value is used only internally.
		__( 'MAGX Pixel ID', 'magx' ),
		'magx_pixel_id_callback',
		'magx_pixel', //page
		'magx_ttd_pixel_section',
		array(
			'label_for'         => 'magx_pixel_id',
			'class'             => 'regular-text',
		)
	);
}


/**
 * Section callback function.
 *
 * @param array $args  The settings array, defining title, id, callback.
 */
function magx_ttd_pixel_section_callback( $args ) {
	?>
	<p id="<?php echo esc_attr( $args['id'] ); ?>">
	Before enabling this setting, please consider if you
	Find your pixel ID in the .txt file, as highlighted below.
	<code><br/>
	...<br/>
	universalPixelApi.init("eac6kka", ["<strong>xxxxxxx</strong>"], "https://insight.adsrvr.org/track/up");<br/>
	...
	</code>
	</p>
	<?php
}

/**
 * 
 *
 * WordPress has magic interaction with the following keys: label_for, class.
 * - the "label_for" key value is used for the "for" attribute of the <label>.
 * - the "class" key value is used for the "class" attribute of the <tr> containing the field.
 * Note: you can add custom key value pairs to be used inside your callbacks.
 *
 * @param array $args
 */
function magx_pixel_id_callback( $args ) {
	// Get the value of the setting we've registered with register_setting()
	$options = get_option( 'magx_pixel' );
	if(!defined("MAGX_PIXEL_PIXEL_ID")){
		
	?>
	<input type="text" id="<?php echo esc_attr( $args['label_for'] ); ?>"
			name="magx_pixel[<?php echo esc_attr( $args['label_for'] ); ?>]" value="<?php echo esc_attr( trim( $options['magx_pixel_id'] ) ) ;?> ">
	<?php
	} else {
		?>
		<input type="text" id="<?php echo esc_attr( $args['label_for'] ); ?>" disabled readonly
			name="magx_pixel[<?php echo esc_attr( $args['label_for'] ); ?>]" value="<?php echo esc_attr( MAGX_PIXEL_PIXEL_ID ) ;?> ">
			<p class="description">
		<?php echo __('This field is currently disabled because the constant <code>MAGX_PIXEL_PIXEL_ID</code> has been defined.', 'magx' ); ?>
	</p>
		<?php
	}
	?>
	<p class="description">
		<?php echo __('This plugin was provided by <a href="https://themagxgroup.com" target="_blank">The MAGX Group</a> as part of a retargeting campaign to retarget site visits and track conversions. You may disable this plugin if you no longer have a campaign running with MAGX or no longer want to track conversions or use site retargeting. Disabling this plugin with an active campaign may affect our ability to serve all contracted impressions.', 'magx' ); ?>
	</p>
	<p class="description">
		<?php
		esc_html_e( 'Need support? Email '); 
		echo __('<a href="mailto:'. magx_pixel_get_support_email() .'?sublect='. urlencode('Pixel Support for '. get_site_url() ) .' " target="_blank">'. magx_pixel_get_support_email() .'</a>', 'magx' ); 
		?>
	</p>
	<?php
}

/**
 * Add the top level menu page.
 */
function magx_pixel_options_page() {
	add_submenu_page(
		'options-general.php',
		__('MAGX Pixel','magx'),
		__('MAGX Pixel','magx'),
		'manage_options',
		'magx-pixel',
		'magx_pixel_display_options_page'
	);
}


function magx_pixel_display_options_page() {
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}

	// add error/update messages

	// check if the user have submitted the settings
	// WordPress will add the "settings-updated" $_GET parameter to the url
	if ( isset( $_GET['settings-updated'] ) ) {
		// add settings saved message with the class of "updated"
		add_settings_error( 'magx_pixel_messages', 'magx_pixel_messages', __( 'Settings Saved', 'magx' ), 'updated' );
	}

	// show error/update messages
	settings_errors( 'magx_pixel_messages' );
	?>
	<div class="wrap">
		<h1><?php echo esc_html( get_admin_page_title() ); ?></h1>
		<form action="options.php" method="post">
			<?php
			settings_fields( 'magx_pixel' );
			do_settings_sections( 'magx_pixel' );
			// output save settings button
			submit_button( 'Save Settings' );
			?>
		</form>
	</div>
	<?php
}

add_action( 'wp_footer', 'MAGX_enqueue_universal_pixel', 1000 );
function MAGX_enqueue_universal_pixel(){
	?>
	<!--
	*
	* This code block is the Universal Pixel from The Trade Desk.
	* The pixel ID needs to be set in order to work. The block will be empty if this is not set.
	*
	-->
	<?php
		if(defined("MAGX_PIXEL_PIXEL_ID")){
			$magx_pixel_id = MAGX_PIXEL_PIXEL_ID;
		} else {
			$magx_pixel = get_option( 'magx_pixel' );
			if( !empty( $magx_pixel ) ){
				$magx_pixel_id = $magx_pixel['magx_pixel_id'];
			}
		}
		
		if( isset( $magx_pixel_id ) ){
	?>
	<script src="https://js.adsrvr.org/up_loader.1.1.0.js" type="text/javascript"></script>
        <script type="text/javascript">
            ttd_dom_ready( function() {
                if (typeof TTDUniversalPixelApi === 'function') {
                    var universalPixelApi = new TTDUniversalPixelApi();
                    universalPixelApi.init("eac6kka", ["<?php echo esc_html($magx_pixel_id ); ?>"], "https://insight.adsrvr.org/track/up",<?php do_action( 'magx_pixel_dymamic_parameters' ); ?>);
                }
            });
        </script>
	<?php
		}
		echo PHP_EOL;
	?>
	<!--
	*
	* End code block
	*
	-->
	<?php
	
	
}

?>