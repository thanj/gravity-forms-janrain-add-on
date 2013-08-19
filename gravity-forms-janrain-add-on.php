<?php
/*
Plugin Name: Gravity Forms Janrain Add-on
Description: Integrate Gravity Forms with Janrain Engage social login to pre-fill forms.
Author: janrain, goldenapples
Author URI: http://janrain.com
Version: 0.3
License: GPL V2 or higher

================================================================================

Copyright 2012-2013 Janrain, Inc.

This program is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 3 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program.  If not, see <http://www.gnu.org/licenses/>.

*/

// This is a hack to get around symlink resolving issues, see
// http://wordpress.stackexchange.com/questions/15202/plugins-in-symlinked-directories
// Hopefully a better solution will be found in future versions of WordPress.
if ( isset( $plugin ) )
	define( 'JANRAIN_GFORMS_DIRECTORY', plugin_dir_url( $plugin ) );
else define( 'JANRAIN_GFORMS_DIRECTORY', plugin_dir_url( __FILE__ ) );



/**
 * Output of the Engage signin input field
 *
 *
 */
function janrain_social_signin_gfield( $content, $field, $value, $lead_id, $form_id ) {

	if ( IS_ADMIN && rgar( $field, 'socialPrefill' ) )
		$content = str_replace(
			"value=''",
			"value='{Prefill with: ". esc_attr( rgar( $field, 'socialPrefillWith' ) ) . "}'",
			$content
		);

	if ( rgar( $field, 'type' ) !== 'janrain_engage' )
		return $content;

	$mode = empty( $_POST['screen_mode'] ) ? 'view' : $_POST['screen_mode'];

	if ( $mode == "view" ){

		if ( IS_ADMIN ) {

			// Compile the available provider settings, show them instead of the default input field
			$app_settings = get_option( 'janrain_settings' );
			$provider_options = '';

			foreach ( $app_settings['providers'] as $provider )
				$provider_options .= "<span class='janrain-provider-icon-32 janrain-provider-icon-{$provider}'></span> ";

			$content = "<div class='gfield_admin_icons'><div class='gfield_admin_header_title'>Janrain Engage Sign-In : Field ID {$field['id']}</div><a id='gfield_delete_{$field['id']}' href='#' title='click to delete this field' onclick='StartDeleteField(this); return false;' class='field_delete_icon'> Delete</a><a href='javascript:void(0)' title='click to edit this field' class='field_edit_icon edit_icon_collapsed'> Edit</a></div><label class='gfield_label' for='input_{$field['id']}'>{$field['label']}</label><div class='ginput_container'><div name='input_{$field['id']}' id='input_{$field['id']}' >$provider_options</div></div><div class='gfield_description'></div>";

		} else {

			$content .= '<div id="janrainEngageEmbed"></div>';
			janrain_engage_widget_script( $form_id );

		}

	}

	return $content;
}

add_filter( 'gform_field_content', 'janrain_social_signin_gfield', 10, 5 );


/**
 * Outputs the widget JS in the site footer
 *
 */
function janrain_engage_widget_script( $form_id ) {

	$settings = get_option( 'janrain_settings' );

	// Config values for client app
	$app_settings = array(
		'appid' => $settings['appid'],
		'appurl' => untrailingslashit( $settings['appurl'] ),
		'providers' => '[ "' . implode( '", "', $settings['providers'] ) . '" ]',
		'token_url' => admin_url( 'admin-ajax.php?action=return-token&form='.$form_id ),
		'count' => count( $settings['providers'] ),
	);

	echo <<<SCRIPT
<script type="text/javascript" id="janrain-gform-connector">
(function() {
    if (typeof window.janrain !== 'object') window.janrain = {};
    if (typeof window.janrain.settings !== 'object') window.janrain.settings = {};

    /* _______________ can edit below this line _______________ */

	janrain.settings.appId = '{$app_settings['appid']}';
	janrain.settings.appUrl = '{$app_settings['appurl']}';
	janrain.settings.providers = {$app_settings['providers']};
	janrain.settings.tokenUrl = '{$app_settings['token_url']}';
	janrain.settings.tokenAction = 'event';
	janrain.settings.type = 'embed';
	janrain.settings.providersPerPage = '{$app_settings['count']}';
	janrain.settings.format = 'one row';
	janrain.settings.showAttribution = false;
	janrain.settings.fontColor = '#666666';
	janrain.settings.fontFamily = 'inherit';
	janrain.settings.backgroundColor = 'transparent';
	janrain.settings.noReturnExperience = true;
	janrain.settings.width = '100%';
	janrain.settings.borderColor = 'transparent';
	janrain.settings.borderRadius = '10';
	janrain.settings.buttonBorderColor = '#CCCCCC';
	janrain.settings.buttonBorderRadius = '5';
	janrain.settings.buttonBackgroundStyle = 'gradient';
	janrain.settings.language = 'en';
	janrain.settings.linkClass = 'janrainEngage';

    /* _______________ can edit above this line _______________ */

	function isReady() { janrain.ready = true; };

	if (document.addEventListener) {
		document.addEventListener("DOMContentLoaded", isReady, false);
	} else {
		window.attachEvent('onload', isReady);
	}

    var e = document.createElement('script');
    e.type = 'text/javascript';
    e.id = 'janrainAuthWidget';

    if (document.location.protocol === 'https:') {
      e.src = 'https://rpxnow.com/js/lib/janrain/engage.js';
    } else {
      e.src = 'http://widget-cdn.rpxnow.com/js/lib/janrain/engage.js';
    }

    var s = document.getElementsByTagName('script')[0];
    s.parentNode.insertBefore(e, s);
})();

function janrainWidgetOnload() {
    janrain.events.onProviderLoginToken.addHandler(function(tokenResponse) {
		jQuery.post( '{$app_settings['token_url']}',
			{ 'token': tokenResponse.token },
			function( response ) {
				for ( key in response ) {
					jQuery('#'+key).val(response[key]);
				}
			}
		);
		jQuery('#janrainEngageEmbed').closest('.gfield').fadeOut();
		return true;
    })
}

</script>
SCRIPT;

}

require_once( plugin_dir_path( __FILE__ ) . 'field-list.php' );
require_once( plugin_dir_path( __FILE__ ) . 'admin-settings.php' );
require_once( plugin_dir_path( __FILE__ ) . 'ajax-response.php' );


// vi:sw=5 sts=5 noexpandtab
