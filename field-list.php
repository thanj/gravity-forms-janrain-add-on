<?php
/**
 * The Prefill class defines the fields available to use for pre-fill
 *
 * See http://developers.janrain.com/documentation/engage/reference/merged-poco-guide/
 * for a current list of available fields.
 * Note: most of these are not available from all providers.
 *
 * To add fields, see the function `get_fields()` below.
 *
 * @since	2012-12-14
 *
 */

class Prefill {

	public static $core_fields = array(
		'given' => array(
			'name' => 'First Name',
			'function' => array( 'Prefill', 'given_name' ),
		),
		'family' => array(
			'name' => 'Last Name',
			'function' => array( 'Prefill', 'family_name' ),
		),
		'formatted' => array(
			'name' => 'Formatted Name',
			'function' => array( 'Prefill', 'formatted_name' ),
		),
		'email' => array(
			'name' => 'Email Address',
			'function' => array( 'Prefill', 'email' ),
		),
		'phone' => array(
			'name' => 'Primary Phone',
			'function' => array( 'Prefill', 'phone' ),
		),
		'title' => array(
			'name' => 'Position',
			'function' => array( 'Prefill', 'title' ),
		),
		'company' => array(
			'name' => 'Company',
			'function' => array( 'Prefill', 'company' ),
		),
	);

	/**
	 * Get the available fields, with user-defined fields included.
	 *
	 * To add another field to the available prefill data or override one of the
	 * built-in functions, add a filter to 'janrain_gforms_profile_data' defining
	 * the field in a format like this:
	 *
	 *		add_filter( 'janrain_gforms_profile_data', 'add_gender_field' );
	 *
	 *		function add_gender_field( $fields ) {
	 *			$fields['gender'] = array(
	 *				'name' => 'Gender',
	 *				'function' => create_function( '$profile',
	 *					'return $profile->profile->gender;' )
	 *			);
	 *		}
	 *
	 * See the Merged Poco Guide on developers.janrain.com for available fields.
	 *
	 */
	function get_fields() {
		return apply_filters(
			'janrain_gforms_profile_data',
			self::$core_fields
		);
	}


	/*
	 * Functions to retrieve data from profile
	 *
	 * These are meant to be examples only. The fields that will be useful will
	 * be different depending on the social providers used, and the context of
	 * the application. If you want, for example, a 'heroes' field, or access to
	 * the user's 'photos', you would have to define it yourself, using the
	 * "janrain_gforms_profile_data" filter.
	 *
	 * Note that some of the fields available require requesting extended
	 * permissions, which you will have to enable in your app through the
	 * rpxnow.com dashboard.
	 *
	 */

	/*
	 * Full Name from profile
	 *
	 */
	function formatted_name( $profile ) {
		return $profile->profile->name->formatted;
	}

	/*
	 * Last Name
	 *
	 * (from profile if available; otherwise attempts to split full name)
	 */
	function family_name( $profile ) {
		list( $first_name, $last_name ) = explode( ' ', $profile->profile->name->formatted );
		if ( isset( $profile->profile->name->familyName ) )
			$last_name = $profile->profile->name->familyName;
		return $last_name;
	}

	/*
	 * First Name
	 *
	 * (from profile if available; otherwise attempts to split full name)
	 */
	function given_name( $profile ) {
		list( $first_name, $last_name ) = explode( ' ', $profile->profile->name->formatted );
		if ( isset( $profile->profile->name->givenName ) )
			$first_name = $profile->profile->name->givenName;
		return $first_name;
	}

	/*
	 * Email Address
	 *
	 * (uses "verified email" from providers that support the feature, otherwise
	 * tries to find a user-supplied email address from profile.)
	 */
	function email( $profile ) {
		if ( isset( $profile->profile->verifiedEmail ) )
			return $profile->profile->verifiedEmail;
		if ( isset( $profile->profile->email ) )
		   return $profile->profile->email;
	}

	/*
	 * Phone number
	 *
	 * (Looks in profile first, and in merged poco if no number is found in
	 * profile.)
	 */
	function phone( $profile ) {
		if ( isset( $profile->profile->phone ) )
		  return $profile->profile->phone;
		if ( isset( $profile->merged_poco->phoneNumbers ) &&
				is_array( $profile->merged_poco->phoneNumbers ) ) {
			$primary = $profile->merged_poco->phoneNumbers[0];
			return $primary->value;
		}
	}

	/*
	 * Job Title from most recent organization in profile
	 *
	 * includes fix for Salesforce, which returns "organizations" in a different
	 * format from all other providers.
	 */
	function title( $profile ) {
		if ( $profile->profile->providerName === 'Salesforce' )
			return '';
		if ( isset( $profile->merged_poco->organizations ) &&
				is_array( $profile->merged_poco->organizations ) ) {
			$job = $profile->merged_poco->organizations[0];
			return $job->title;
		}
	}

	/*
	 * Company from most recent organization in profile
	 *
	 * includes fix for Salesforce, which returns "organizations" in a different
	 * format from all other providers.
	 */
	function company( $profile ) {
		if ( $profile->profile->providerName === 'Salesforce' )
			return '';
		if ( isset( $profile->merged_poco->organizations ) &&
				is_array( $profile->merged_poco->organizations ) ) {
			$job = $profile->merged_poco->organizations[0];
			return $job->name;
		}
	}

}


// vi:sw=5 sts=5 noexpandtab
