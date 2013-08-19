<?php
/**
 * Functions to retrieve user data through auth_info call.
 *
 * Called through Ajax from form
 *
 * @package	janrain-gforms
 * @since	2012-12-14
 * @author	janrain
 */


/**
 * On receiving login token, retrieve all social data available
 *
 * Called from auth widget through Ajax handler. Echos a JSON object in the
 * format `'field_id': 'prefill_value'` and exits.
 *
 * @param	$_REQUEST['token']	Token recieved from social login provider
 * @param	$_REQUEST['form_id']	Form to gather prefill data for.
 * @return	void
 */
function janrain_gforms_retrieve_userdata() {

	$token = $_REQUEST['token'];
	$form_id = intval( $_REQUEST['form'] );

	$app_settings = get_option( 'janrain_settings' );

	$request =  array(
		'apiKey' => $app_settings['secret'],
		'token' => $_REQUEST['token'],
		'extended' => 'true',
		'tokenURL' => admin_url( 'admin-ajax.php?action=return-token' ),
	);

	// Retrieve auth info. TODO: error handling
	$auth_info = wp_remote_post(
		add_query_arg(
			$request,
			trailingslashit( $app_settings['appurl'] ) . 'api/v2/auth_info'
		)
	);

	$user_data = json_decode( $auth_info['body'] );

	$form_data = map_profile_to_form_fields( $user_data, $form_id );

	header( 'Content-type: application/json; charset=utf-8' );
	header( 'Access-Control-Allow-Origin: *' );
	echo json_encode( $form_data );
	exit;
}

add_action( 'wp_ajax_return-token', 'janrain_gforms_retrieve_userdata' );
add_action( 'wp_ajax_nopriv_return-token', 'janrain_gforms_retrieve_userdata' );


/**
 * Format profile data returned into format required for pre-fill
 *
 * Hard-coded values for now, needs to be set up in form options later
 *
 */
function map_profile_to_form_fields( $profile, $form_id ) {

	$form = RGFormsModel::get_form_meta( $form_id );
	$prefill_functions = Prefill::get_fields();

	$return_array = array();

	foreach ( $form['fields'] as $field ) {
		$field = (object)$field;
		if ( isset( $field->socialPrefill ) && $field->socialPrefill ) {

			$data_function = $prefill_functions[ $field->socialPrefillWith ]['function'];

			$value = call_user_func( $data_function, $profile );

			$return_array[ 'input_'.$form_id.'_'.$field->id ] = $value;
		}
	}

	return $return_array;

}


// vi:sw=5 sts=5 noexpandtab
