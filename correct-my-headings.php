<?php
/* 
Plugin Name: Correct My Headings 
Plugin URI: http://vileworks.com/correct-my-headings
Description: Turns subheadings down a notch on archive pages: H2's become H3's, H3's become H4's, ..., H6's become paragraphs 
Version: 1.0 
Author: Stefan Matei
Author URI: http://vileworks.com 
*/

/*  Copyright 2012  Stefan Matei  (email : stefan@vileworks.com)

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License, version 2, as 
    published by the Free Software Foundation.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

// ------------------------------------------------------------------------
// REQUIRE MINIMUM VERSION OF WORDPRESS:                                               
// ------------------------------------------------------------------------
// THIS IS USEFUL IF YOU REQUIRE A MINIMUM VERSION OF WORDPRESS TO RUN YOUR
// PLUGIN. IN THIS PLUGIN THE WP_EDITOR() FUNCTION REQUIRES WORDPRESS 3.3 
// OR ABOVE. ANYTHING LESS SHOWS A WARNING AND THE PLUGIN IS DEACTIVATED.                    
// ------------------------------------------------------------------------

function requires_wordpress_version() {
	global $wp_version;
	$plugin = plugin_basename( __FILE__ );
	$plugin_data = get_plugin_data( __FILE__, false );

	if ( version_compare($wp_version, "2.7", "<" ) ) {
		if( is_plugin_active($plugin) ) {
			deactivate_plugins( $plugin );
			wp_die( "'".$plugin_data['Name']."' requires WordPress 2.7 or higher, and has been deactivated! Please upgrade WordPress and try again.<br /><br />Back to <a href='".admin_url()."'>WordPress admin</a>." );
		}
	}
}
add_action( 'admin_init', 'requires_wordpress_version' );

// ------------------------------------------------------------------------
// PLUGIN PREFIX:                                                          
// ------------------------------------------------------------------------
// A PREFIX IS USED TO AVOID CONFLICTS WITH EXISTING PLUGIN FUNCTION NAMES.
// ------------------------------------------------------------------------

// 'cmh_' prefix is derived from [c]orrect [m]y [h]eadings

// ------------------------------------------------------------------------
// REGISTER HOOKS & CALLBACK FUNCTIONS:
// ------------------------------------------------------------------------
// HOOKS TO SETUP DEFAULT PLUGIN OPTIONS, HANDLE CLEAN-UP OF OPTIONS WHEN
// PLUGIN IS DEACTIVATED AND DELETED, INITIALISE PLUGIN, ADD OPTIONS PAGE.
// ------------------------------------------------------------------------

// Set-up Action and Filter Hooks
register_activation_hook(__FILE__, 'cmh_add_defaults');
register_uninstall_hook(__FILE__, 'cmh_delete_plugin_options');
add_action('admin_init', 'cmh_init' );
add_action('admin_menu', 'cmh_add_options_page');
add_filter( 'plugin_action_links', 'cmh_plugin_action_links', 10, 2 );

// --------------------------------------------------------------------------------------
// CALLBACK FUNCTION FOR: register_uninstall_hook(__FILE__, 'cmh_delete_plugin_options')
// --------------------------------------------------------------------------------------
// THIS FUNCTION RUNS WHEN THE USER DEACTIVATES AND DELETES THE PLUGIN. IT SIMPLY DELETES
// THE PLUGIN OPTIONS DB ENTRY (WHICH IS AN ARRAY STORING ALL THE PLUGIN OPTIONS).
// --------------------------------------------------------------------------------------

// Delete options table entries ONLY when plugin deactivated AND deleted
function cmh_delete_plugin_options() {
	delete_option('cmh_options');
}

// ------------------------------------------------------------------------------
// CALLBACK FUNCTION FOR: register_activation_hook(__FILE__, 'cmh_add_defaults')
// ------------------------------------------------------------------------------
// THIS FUNCTION RUNS WHEN THE PLUGIN IS ACTIVATED. IF THERE ARE NO THEME OPTIONS
// CURRENTLY SET, OR THE USER HAS SELECTED THE CHECKBOX TO RESET OPTIONS TO THEIR
// DEFAULTS THEN THE OPTIONS ARE SET/RESET.
//
// OTHERWISE, THE PLUGIN OPTIONS REMAIN UNCHANGED.
// ------------------------------------------------------------------------------

// Define default option settings
function cmh_add_defaults() {
	$tmp = get_option('cmh_options');
    if( !is_array($tmp) ) {
		delete_option('cmh_options'); // so we don't have to reset all the 'off' checkboxes too! (don't think this is needed but leave for now)
		$arr = array(	
			"rdo_group_one" => "h2"
		);
		update_option('cmh_options', $arr);
	}
}

// ------------------------------------------------------------------------------
// CALLBACK FUNCTION FOR: add_action('admin_init', 'cmh_init' )
// ------------------------------------------------------------------------------
// THIS FUNCTION RUNS WHEN THE 'admin_init' HOOK FIRES, AND REGISTERS YOUR PLUGIN
// SETTING WITH THE WORDPRESS SETTINGS API. YOU WON'T BE ABLE TO USE THE SETTINGS
// API UNTIL YOU DO.
// ------------------------------------------------------------------------------

// Init plugin options to white list our options
function cmh_init(){
	register_setting( 'cmh_plugin_options', 'cmh_options', 'cmh_validate_options' );
}

// ------------------------------------------------------------------------------
// CALLBACK FUNCTION FOR: add_action('admin_menu', 'cmh_add_options_page');
// ------------------------------------------------------------------------------
// THIS FUNCTION RUNS WHEN THE 'admin_menu' HOOK FIRES, AND ADDS A NEW OPTIONS
// PAGE FOR YOUR PLUGIN TO THE SETTINGS MENU.
// ------------------------------------------------------------------------------

// Add menu page
function cmh_add_options_page() {
	add_options_page('Correct My Headings Options Page', 'Correct My Headings', 'manage_options', __FILE__, 'cmh_render_form');
}

// ------------------------------------------------------------------------------
// CALLBACK FUNCTION SPECIFIED IN: add_options_page()
// ------------------------------------------------------------------------------
// THIS FUNCTION IS SPECIFIED IN add_options_page() AS THE CALLBACK FUNCTION THAT
// ACTUALLY RENDER THE PLUGIN OPTIONS FORM AS A SUB-MENU UNDER THE EXISTING
// SETTINGS ADMIN MENU.
// ------------------------------------------------------------------------------

// Render the Plugin options form
function cmh_render_form() {
	?>
	<div class="wrap">
		
		<!-- Display Plugin Icon, Header, and Description -->
		<div class="icon32" id="icon-options-general"><br></div>
		<h2>Correct My Headings</h2>
		<p>If your subheadings appear on archive pages, they need to start from <strong>H3</strong> (because <strong>H2</strong> tags are used by the post titles on archive pages).</p>
		<p>This plugin dynamically corrects subheadings before they are displayed on your site.</p>

		<!-- Beginning of the Plugin Options Form -->
		<form method="post" action="options.php">
			<?php settings_fields('cmh_plugin_options'); ?>
			<?php $options = get_option('cmh_options'); ?>

			<!-- Table Structure Containing Form Controls -->
			<!-- Each Plugin Option Defined on a New Table Row -->
			<table class="form-table">

				<!-- Radio Button Group -->
				<tr valign="top">
					<th scope="row">How are you using the subheadings in your posts?</th>
					<td>
						<label>
							<input name="cmh_options[rdo_group_one]" type="radio" value="h2" <?php checked('h2', $options['rdo_group_one']); ?> />
							My subheadings start from the H2 level: <strong>turn them down</strong> a level on archive pages <br />
							<span style="color:#666666;">H2's become H3's, H3's become H4's, H4's become H5's, H5's become H6's and H6's become paragraphs</span>
						</label>
						<br /><br />

						<label>
							<input name="cmh_options[rdo_group_one]" type="radio" value="h3" <?php checked('h3', $options['rdo_group_one']); ?> />
							My subheadings start from the H3 level: <strong>turn them up</strong> a level on single posts or pages<br />
							<span style="color:#666666;">H3's become H2's, H4's become H3's, H5's become H4's and H6's become H5's</span>
						</label>
						<br /><br />

						<label>
							<input name="cmh_options[rdo_group_one]" type="radio" value="dont" <?php checked('dont', $options['rdo_group_one']); ?> />
							Disable this functionality:
							<span style="color:#666666;">don't change my headings</span>
						</label>
					</td>
				</tr>


			</table>
			<p class="submit">
			<input type="submit" class="button-primary" value="<?php _e('Save Changes') ?>" />
			</p>

			<p>This plugin does not make any changes to your database: the subheadings will only be displayed differently on the front-end site.
			<br />If you disable its functionality (with the option above), disable the plugin or decide to uninstall it everything will be back to normal. 
			</p>
		</form>

		<p style="margin-top:15px;">
			<p style="font-weight: bold;color: #26779a;">Correct My Headings was developed by <a href="http://stefanmatei.com" title="Stefan Matei">Stefan Matei</a> for <a href="http://www.vileworks.com">VileWorks.com</a>.</p>
			<span><a href="http://fb.me/VileWorks" title="Our Facebook page" target="_blank"><img style="border:1px #ccc solid;" src="<?php echo plugins_url(); ?>/correct-my-headings/images/facebook-icon.png" /></a></span>
			&nbsp;&nbsp;<span><a href="http://twitter.com/VileWorks" title="Follow on Twitter" target="_blank"><img style="border:1px #ccc solid;" src="<?php echo plugins_url(); ?>/correct-my-headings/images/twitter-icon.png" /></a></span>
		</p>

	</div>
	<?php	
}

// Sanitize and validate input. Accepts an array, return a sanitized array.
function cmh_validate_options($input) {
	 // strip html from textboxes
	$input['textarea_one'] =  wp_filter_nohtml_kses($input['textarea_one']); // Sanitize textarea input (strip html tags, and escape characters)
	$input['txt_one'] =  wp_filter_nohtml_kses($input['txt_one']); // Sanitize textbox input (strip html tags, and escape characters)
	return $input;
}

// Display a Settings link on the main Plugins page
function cmh_plugin_action_links( $links, $file ) {

	if ( $file == plugin_basename( __FILE__ ) ) {
		$cmh_links = '<a href="'.get_admin_url().'options-general.php?page=correct-my-headings/correct-my-headings.php">'.__('Settings').'</a>';
		// make the 'Settings' link appear first
		array_unshift( $links, $cmh_links );
	}

	return $links;
}

// ------------------------------------------------------------------------------
// USAGE FUNCTIONS:
// ------------------------------------------------------------------------------
// THE FOLLOWING FUNCTIONS USE THE PLUGINS OPTIONS DEFINED ABOVE.
// ------------------------------------------------------------------------------

add_filter ('the_content', 'correct_my_headings');
function correct_my_headings($content) {

	$options = get_option('cmh_options');
	$option = $options['rdo_group_one'];

	 if ($option=='dont') return $content;

	// 	My subheadings start from the H2 level: turn them down a level on archive pages 
	//	H2's become H3's, H3's become H4's, ..., H6's become paragraphs 
    if ($option=='h2') {
	   	if(!is_singular()) {
			$up   = array('<h6','</h6','<h5','</h5','<h4','</h4','<h3','</h3','<h2','</h2');
			$down = array( '<p', '</p','<h6','</h6','<h5','</h5','<h4','</h4','<h3','</h3');
			$content = str_replace($up,$down,$content);
	    }
    }
    
 	//  My subheadings start from the H3 level: turn them up a level on single posts or pages
	//  H3's become H2's, H4's become H3's, ..., H6's become H5's 
    if ($option=='h3') {
	    if(is_singular()) {
			$down = array( '<h2','</h2','<h3','</h3','<h4','</h4','<h5','</h5','<h6','</h6');
			$up   = array( '<h1','</h1','<h2','</h2','<h3','</h3','<h4','</h4','<h5','</h5');
			$content = str_replace($down,$up,$content);
	    }
	}

    return $content;
}
