<?php
/**
 * All functions that should only be loaded in admin section.
 *
 * @package	janrain-gforms
 * @since	2012-12-14
 * @author	janrain
 */


/**
 * Enqueue Quilt providers script on admin pages
 *
 * Its being loaded through javascript in the form itself; this is just to
 * make the admin pages look a bit prettier.
 *
 */
function quilt_providers_stylesheet() {

	if ( !isset( $_GET['page'] ) || !in_array( $_GET['page'], array( 'gf_edit_forms', 'janrain_settings' ) ) )
		return;

	wp_enqueue_style( 'quilt-providers', 'http://cdn.quilt.janrain.com/2.2.5/providers.css' );

	// Providers css file for older version of IE
	wp_register_style( 'quilt-providers-ie', 'http://cdn.quilt.janrain.com/2.2.5/providers-ie.css' );
	$GLOBALS['wp_styles']->add_data( 'quilt-providers-ie', 'conditional', 'lte IE 8' );
	wp_enqueue_style( 'quilt-providers-ie' );

	if ( $_GET['page'] === 'janrain_settings' )
		wp_enqueue_script( 'engage-integration-settings', JANRAIN_GFORMS_DIRECTORY . 'settings.js' );
}

add_action( 'admin_enqueue_scripts', 'quilt_providers_stylesheet' );


/**
 * Display the option to "prefill this field/ from".
 *
 * Shown in every field, just above the "Rules" section on the standard "Properties" tab
 *
 * TODO: pay attention to the $form_id variable and only show on forms that have a social signin field.
 */
function janrain_engage_field_prepop_settings( $position, $form_id ) {
	if ( $position == 1600 ) {
?>
	<li class="janrain_engage_prefill field_setting">
		<input type="checkbox" class="social_prefill" />
		<label class="inline" for="field_social_prefill">
			<?php _e( 'Prefill this field from social login profile:', 'gforms_janrain' ); ?>
		</label>
		<?php gform_tooltip( 'field_social_prefill' ); ?>
		<select disabled="disabled" class="field_social_prefill_with" >
	<?php
		require_once( 'field-list.php' );
		foreach ( Prefill::get_fields() as $name => $field )	{ ?>
			<option value="<?php echo esc_attr( $name ); ?>"><?php echo $field['name']; ?></option>
	<?php } ?>
		</select>
	</li>
<?php

	}

}

add_action( 'gform_field_standard_settings', 'janrain_engage_field_prepop_settings', 10, 2 );


/**
 * Tooltips for the relevant fields added by this plugin
 *
 * (needs i18n)
 */
function janrain_engage_gforms_tooltips( $tooltips ) {
	$tooltips['field_social_prefill'] = '<h6>' .  __( 'Prefill from Social Profile', 'gforms_janrain' ) . '</h6>' .
        __( 'If a social login field is defined, you can select a field from the user\'s ' .
			 'social profile to prefill this field with, once they authenticate.', 'gforms_janrain' );
	return $tooltips;
}

add_filter( 'gform_tooltips', 'janrain_engage_gforms_tooltips' );


/**
 * Common Javascript run on the gf_edit_forms page, to initialize the new fields
 * we've added and handle updating their values when user changes them. Since
 * the admin form is Ajax-only, we have to manually update the javascript form
 * object whenever a field changes.
 *
 */
function janrain_gforms_editor_script(){

    ?>
<script type='text/javascript' id='janrain-gforms-editor-js'>

	// Show "prefill" field on all field types...
	for ( type in fieldSettings ) {
		fieldSettings[ type ] += ", .janrain_engage_prefill";
	}

	// ... except the signin widget itself
	fieldSettings["janrain_engage"] = ".conditional_logic_field_setting, .error_message_setting, .label_setting, .admin_label_setting, .rules_setting, .description_setting, .css_class_setting";

	// Update the form meta json object when these fields are updated
	jQuery('.social_prefill').on( 'click', function() {
		SetFieldProperty( 'socialPrefill', jQuery(this).is(':checked') );
		$jSelect = jQuery(this).closest('li').find('select.field_social_prefill_with');
		if ( jQuery(this).is(':checked') ) {
			$jSelect.prop( 'disabled', false );
			SetFieldProperty( 'socialPrefillWith', $jSelect.val() );
		} else {
			$jSelect.prop( 'disabled', true );
			SetFieldProperty( 'socialPrefillWith', '' );
		}
	});
	jQuery('.field_social_prefill_with').on( 'change', function() {
		SetFieldProperty( 'socialPrefillWith', jQuery(this).val() );
	});

	// display proper values on init
	jQuery(document).bind( 'gform_load_field_settings',
		function( event, field, form ) {
			if ( field.socialPrefill ) {
				jQuery('input.social_prefill').prop( 'checked', 'checked' );
				jQuery('select.field_social_prefill_with').val( field.socialPrefillWith ).removeAttr('disabled');
			} else {
				jQuery('input.social_prefill').removeProp( 'checked' );
				jQuery('select.field_social_prefill_with').val('').attr( 'disabled', 'disabled' );
			}
		}
	);
</script>
    <?php
}

add_action( 'gform_editor_js', 'janrain_gforms_editor_script' );


/**
 * Add settings page to menu under "Forms"
 *
 */
function janrain_jump_setting_page_register( $menu_items ) {

	$menu_items[] = array(
		'name' => 'janrain_settings',
		'label' => 'Janrain Settings',
		'callback' => 'janrain_jump_settings_page',
		'permission' => 'manage_options'
	);

	return $menu_items;

}

add_filter( 'gform_addon_navigation', 'janrain_jump_setting_page_register' );


/**
 * Display the settings page
 *
 */
function janrain_jump_settings_page() {

	// Handle submitted values
	// TODO: Use Settings API
	if ( isset( $_POST['janrain_settings'] ) ) {

		check_admin_referer( 'janrain_settings' );

		update_option( 'janrain_settings',
			array(
				'appid' => sanitize_text_field( $_POST['janrain_settings']['appid'] ),
				'appurl' => sanitize_text_field( $_POST['janrain_settings']['appurl'] ),
				'secret' => sanitize_text_field( $_POST['janrain_settings']['secret'] ),
				'providers' => array_keys( $_POST['janrain_settings']['providers'] ),
			)
		);

		echo '<div id="message" class="message updated"><p>'
			. __( 'Janrain Engage app settings updated.', 'gforms_janrain' ) . '</p></div>';

	}

	$defaults = array(
		'appid' => '',
		'appurl' => '',
		'secret' => '',
		'providers' => array(),
	);

	$settings = wp_parse_args( get_option( 'janrain_settings' ), $defaults );

	$providers_text = '<p class="description">'
		. __( 'You will have to enter your app info before choosing available providers.', 'gforms_janrain' )
		. '</p>';

	// Get list of available providers for app, if app info has been entered correctly
	if ( !empty( $settings['appurl'] ) ) {

		$providers = wp_remote_post( trailingslashit( $settings['appurl'] ) . 'api/v2/get_available_providers' );
		// In case of HTTP error, display information about the issue
		if ( is_wp_error( $providers ) )
			$providers_text = '<p>' . __( 'An error occurred while checking your app settings:', 'gforms_janrain' )
			. '<br>' . print_r( $providers, true ) . '</p>';

		// If provider info was successfully retrieved, build checklist here
		else {

			$signin_providers = json_decode( $providers['body'] );

			$app_url_base = str_replace( array( 'https://', '.rpxnow.com', '/' ), '', $settings['appurl'] );

			if ( isset( $signin_providers->signin ) && is_array( $signin_providers->signin ) ) {

				$providers_text = '<div style="-moz-column-width:16em;-moz-column-gap:1em;-webkit-column-width:16em;-webkit-column-gap:1em">';
				foreach ( $signin_providers->signin as $signin_provider ) {
					$providers_text .= '
						<p class="' . ( in_array( $signin_provider, $settings['providers'] ) ? "janrain-provider-text-color-$signin_provider" : 'description' ) . '">
							<input id="janrain_settings_provider_'.$signin_provider.'" type="checkbox" name="janrain_settings[providers]['.$signin_provider.']" ' . checked( in_array( $signin_provider, $settings['providers'] ), true, false ) . '/>
							<label for="janrain_settings_provider_'.$signin_provider.'" >
								<span class="janrain-provider-icon-32 ' . (
									( in_array( $signin_provider, $settings['providers'] ) )?
										  'janrain-provider-icon-' . $signin_provider
										: 'janrain-provider-icon-grayscale-' . $signin_provider
									  ) . '" ></span> &nbsp;'. ucwords( $signin_provider ) . '
							</label>
						</p>';
				}
				$providers_text .= '</div>';
				$providers_text .= '<p>' . sprintf(
					__( 'Provider you wish to use not shown here? Check your <a href="%s">Janrain dashboard</a> to make sure its enabled there.', 'gforms_janrain' ),
					"https://rpxnow.com/relying_parties/$app_url_base/setup_widget"
					) . '</p>';
			}
		}
	}

	echo '
<div class="wrap">
	<h2>' . __( 'Janrain Engage Integration', 'gforms_janrain' ) .'</h2>
	<h3>' . __( 'Janrain App Configuration', 'gforms_janrain' ) . '</h3>
	<p class="description">' . __( 'You will need to enter the Application ID and URL for your Engage application. This information can be obtained from your dashboard at <a href="http://dashboard.janrain.com">dashboard.janrain.com</a>.', 'gforms_janrain' ) . '</p>
	<form action="" method="POST">
	' . wp_nonce_field( 'janrain_settings', '_wpnonce', true, false ) . '
	<table class="form-table">
		<tr valign="top">
			<th scope="row">
				<label for="appurl">' . __( 'Application Domain', 'gforms_janrain' ) . '</label>
			</th>
			<td>
				<input type="text" class="regular-text" name="janrain_settings[appurl]" value="' . esc_attr( $settings['appurl'] ) . '" />
			</td>
		</tr>
		<tr valign="top">
			<th scope="row">
				<label for="appid">' . __( 'App ID', 'gforms_janrain' ) . '</label>
			</th>
			<td>
				<input type="text" class="regular-text" name="janrain_settings[appid]" value="' . esc_attr( $settings['appid'] ) . '" />
			</td>
		</tr>
		<tr valign="top">
			<th scope="row">
				<label for="secret">' . __( 'API Key (Secret)', 'gforms_janrain' ) . '</label>
			</th>
			<td>
				<input type="text" class="regular-text" name="janrain_settings[secret]" value="' . esc_attr( $settings['secret'] ) . '" />
			</td>
		</tr>
		<tr valign="top">
			<th scope="row">
				<label for="secret">' . __( 'Sign-In Providers', 'gforms_janrain' ) . '</label>
			</th>
			<td>
				' . $providers_text . '
			</td>
		</tr>
		<tr valign="top">
			<th scope="row">
			</th>
			<td>
				<input type="submit" class="button-primary" value="' . __( 'Save settings', 'gforms_janrain' ) .'" />
			</td>
		</tr>
	</table>
	<p>' . sprintf( __( '<b>Important:</b> You will also have to make sure the domain <b>%s</b> is added to the domain whitelist for your application.', 'gforms_janrain' ), parse_url( admin_url(), PHP_URL_HOST ) ) . '</p>
	</form>
</div>
';

}

/**
 * Adds a button for "Social Login" form field to the Advanced Fields group
 *
 */
function janrain_engage_widget_field_admin_button( $field_groups ) {
	foreach ( $field_groups as &$group ) {
		if ( $group['name'] == 'advanced_fields' )
			$group['fields'][] = array(
				'class' => 'button',
				'value' => __( 'Social Login', 'gforms_janrain' ),
				'onclick' => "StartAddField('janrain_engage')"
			);

	}
	return $field_groups;
}

add_action( 'gform_add_field_buttons', 'janrain_engage_widget_field_admin_button' );

// vi:sw=5 sts=5 noexpandtab
