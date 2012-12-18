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

	/*
	function address( $profile ) {
		return $profile->profile->address;
	}
	function birthday( $profile ) {
		return $profile->profile->birthday;
	}
	function gender( $profile ) {
		return $profile->profile->gender;
	}
	function homepage( $profile ) {
		return $profile->profile->url;
	}
	function time( $profile ) {
		return ;
	}
	function verified( $profile ) {
		return ;
	}
	function friends( $profile ) {
		return ;
	}
	function about( $profile ) {
		return ;
	}
	function accounts( $profile ) {
		return ;
	}
	function username( $profile ) {
		return ;
	}
	function addresses( $profile ) {
		return ;
	}
	function anniversary( $profile ) {
		return ;
	}
	function body( $profile ) {
		return ;
	}
	function books( $profile ) {
		return ;
	}
	function cars( $profile ) {
		return ;
	}
	function children( $profile ) {
		return ;
	}
	function connected( $profile ) {
		return ;
	}
	function current( $profile ) {
		return ;
	}
	function drinker( $profile ) {
		return ;
	}
	function emails( $profile ) {
		return ;
	}
	function ethnicity( $profile ) {
		return ;
	}
	function fashion( $profile ) {
		return ;
	}
	function food( $profile ) {
		return ;
	}
	function happiest( $profile ) {
		return ;
	}
	function heroes( $profile ) {
		return ;
	}
	function humor( $profile ) {
		return ;
	}
	function ims( $profile ) {
		return ;
	}
	function interestedinmeeting( $profile ) {
		return ;
	}
	function interests( $profile ) {
		return ;
	}
	function job( $profile ) {
		return ;
	}
	function languages( $profile ) {
		return ;
	}
	function living( $profile ) {
		return ;
	}
	function looking( $profile ) {
		return ;
	}
	function movies( $profile ) {
		return ;
	}
	function music( $profile ) {
		return ;
	}
	function nickname( $profile ) {
		return ;
	}
	function note( $profile ) {
		return ;
	}
	function organizations( $profile ) {
		return ;
	}
	function pets( $profile ) {
		return ;
	}
	function photos( $profile ) {
		return ;
	}
	function political( $profile ) {
		return ;
	}
	function profile( $profile ) {
		return ;
	}
	function profile( $profile ) {
		return ;
	}
	function profile( $profile ) {
		return ;
	}
	function published( $profile ) {
		return ;
	}
	function quotes( $profile ) {
		return ;
	}
	function relationship( $profile ) {
		return ;
	}
	function relationships( $profile ) {
		return ;
	}
	function religion( $profile ) {
		return ;
	}
	function romance( $profile ) {
		return ;
	}
	function scared( $profile ) {
		return ;
	}
	function sexual( $profile ) {
		return ;
	}
	function smoker( $profile ) {
		return ;
	}
	function sports( $profile ) {
		return ;
	}
	function status( $profile ) {
		return ;
	}
	function tags( $profile ) {
		return ;
	}
	function turn( $profile ) {
		return ;
	}
	function turn( $profile ) {
		return ;
	}
	function tv( $profile ) {
		return ;
	}
	function updated( $profile ) {
		return ;
	}
	function urls( $profile ) {
		return ;
	}
	function active( $profile ) {
		return ;
	}
	function user( $profile ) {
		return ;
	}
	function locale( $profile ) {
		return ;
	}
	function positions( $profile ) {
		return ;
	}
	function verified( $profile ) {
		return ;
	}
	function access( $profile ) {
		return ;
	}
	function type( $profile ) {
		return ;
	}
	function pings( $profile ) {
		return ;
	}
	function relationship( $profile ) {
		return ;
	}
	function google( $profile ) {
		return ;
	}
	function birthday( $profile ) {
		return ;
	}
	function time( $profile ) {
		return ;
	}
	function access( $profile ) {
		return ;
	}
	function verified( $profile ) {
		return ;
	}
	function blood( $profile ) {
		return ;
	}
	function favorite( $profile ) {
		return ;
	}
	function occupation( $profile ) {
		return ;
	}
	function following( $profile ) {
		return ;
	}
	function followers( $profile ) {
		return ;
	}
	function friendships( $profile ) {
		return ;
	}
	function counters( $profile ) {
		return ;
	}
	function name( $profile ) {
		return ;
	}
	function activities( $profile ) {
		return ;
	}
	function offline( $profile ) {
		return ;
	}
	function hometown( $profile ) {
		return ;
	}
	function location( $profile ) {
		return ;
	}
	 */
}


/*
		Fields from Merged Poco format

		'Display' => array(
			'name' => 'Display',
			'function' => array( 'Prefill', 'Display' ),
		),
		'Gender' => array(
			'name' => 'Gender',
			'function' => array( 'Prefill', 'Gender' ),
		),
		'Profile' => array(
			'name' => 'Profile',
			'function' => array( 'Prefill', 'Profile' ),
		),
		'Preferred' => array(
			'name' => 'Preferred',
			'function' => array( 'Prefill', 'Preferred' ),
		),
		'Homepage' => array(
			'name' => 'Homepage',
			'function' => array( 'Prefill', 'Homepage' ),
		),
		'Time' => array(
			'name' => 'Time',
			'function' => array( 'Prefill', 'Time' ),
		),
		'Verified' => array(
			'name' => 'Verified',
			'function' => array( 'Prefill', 'Verified' ),
		),
		'Friends' => array(
			'name' => 'Friends',
			'function' => array( 'Prefill', 'Friends' ),
		),
		'About' => array(
			'name' => 'About',
			'function' => array( 'Prefill', 'About' ),
		),
		'Accounts' => array(
			'name' => 'Accounts',
			'function' => array( 'Prefill', 'Accounts' ),
		),
		'username,' => array(
			'name' => 'username,',
			'function' => array( 'Prefill', 'username,' ),
		),
		'Addresses' => array(
			'name' => 'Addresses',
			'function' => array( 'Prefill', 'Addresses' ),
		),
		'Anniversary' => array(
			'name' => 'Anniversary',
			'function' => array( 'Prefill', 'Anniversary' ),
		),
		'Body' => array(
			'name' => 'Body',
			'function' => array( 'Prefill', 'Body' ),
		),
		'Books' => array(
			'name' => 'Books',
			'function' => array( 'Prefill', 'Books' ),
		),
		'Cars' => array(
			'name' => 'Cars',
			'function' => array( 'Prefill', 'Cars' ),
		),
		'Children' => array(
			'name' => 'Children',
			'function' => array( 'Prefill', 'Children' ),
		),
		'Connected' => array(
			'name' => 'Connected',
			'function' => array( 'Prefill', 'Connected' ),
		),
		'Current' => array(
			'name' => 'Current',
			'function' => array( 'Prefill', 'Current' ),
		),
		'Drinker' => array(
			'name' => 'Drinker',
			'function' => array( 'Prefill', 'Drinker' ),
		),
		'Emails' => array(
			'name' => 'Emails',
			'function' => array( 'Prefill', 'Emails' ),
		),
		'Ethnicity' => array(
			'name' => 'Ethnicity',
			'function' => array( 'Prefill', 'Ethnicity' ),
		),
		'Fashion' => array(
			'name' => 'Fashion',
			'function' => array( 'Prefill', 'Fashion' ),
		),
		'Food' => array(
			'name' => 'Food',
			'function' => array( 'Prefill', 'Food' ),
		),
		'Happiest' => array(
			'name' => 'Happiest',
			'function' => array( 'Prefill', 'Happiest' ),
		),
		'Heroes' => array(
			'name' => 'Heroes',
			'function' => array( 'Prefill', 'Heroes' ),
		),
		'Humor' => array(
			'name' => 'Humor',
			'function' => array( 'Prefill', 'Humor' ),
		),
		'IMS' => array(
			'name' => 'IMS',
			'function' => array( 'Prefill', 'IMS' ),
		),
		'InterestedInMeeting' => array(
			'name' => 'InterestedInMeeting',
			'function' => array( 'Prefill', 'InterestedInMeeting' ),
		),
		'Interests' => array(
			'name' => 'Interests',
			'function' => array( 'Prefill', 'Interests' ),
		),
		'Job' => array(
			'name' => 'Job',
			'function' => array( 'Prefill', 'Job' ),
		),
		'Languages' => array(
			'name' => 'Languages',
			'function' => array( 'Prefill', 'Languages' ),
		),
		'Living' => array(
			'name' => 'Living',
			'function' => array( 'Prefill', 'Living' ),
		),
		'Looking' => array(
			'name' => 'Looking',
			'function' => array( 'Prefill', 'Looking' ),
		),
		'Movies' => array(
			'name' => 'Movies',
			'function' => array( 'Prefill', 'Movies' ),
		),
		'Music' => array(
			'name' => 'Music',
			'function' => array( 'Prefill', 'Music' ),
		),
		'Nickname' => array(
			'name' => 'Nickname',
			'function' => array( 'Prefill', 'Nickname' ),
		),
		'Note' => array(
			'name' => 'Note',
			'function' => array( 'Prefill', 'Note' ),
		),
		'Organizations' => array(
			'name' => 'Organizations',
			'function' => array( 'Prefill', 'Organizations' ),
		),
		'Pets' => array(
			'name' => 'Pets',
			'function' => array( 'Prefill', 'Pets' ),
		),
		'Photos' => array(
			'name' => 'Photos',
			'function' => array( 'Prefill', 'Photos' ),
		),
		'Political' => array(
			'name' => 'Political',
			'function' => array( 'Prefill', 'Political' ),
		),
		'Profile' => array(
			'name' => 'Profile',
			'function' => array( 'Prefill', 'Profile' ),
		),
		'Profile' => array(
			'name' => 'Profile',
			'function' => array( 'Prefill', 'Profile' ),
		),
		'Profile' => array(
			'name' => 'Profile',
			'function' => array( 'Prefill', 'Profile' ),
		),
		'Published' => array(
			'name' => 'Published',
			'function' => array( 'Prefill', 'Published' ),
		),
		'Quotes' => array(
			'name' => 'Quotes',
			'function' => array( 'Prefill', 'Quotes' ),
		),
		'Relationship' => array(
			'name' => 'Relationship',
			'function' => array( 'Prefill', 'Relationship' ),
		),
		'Relationships' => array(
			'name' => 'Relationships',
			'function' => array( 'Prefill', 'Relationships' ),
		),
		'Religion' => array(
			'name' => 'Religion',
			'function' => array( 'Prefill', 'Religion' ),
		),
		'Romance' => array(
			'name' => 'Romance',
			'function' => array( 'Prefill', 'Romance' ),
		),
		'Scared' => array(
			'name' => 'Scared',
			'function' => array( 'Prefill', 'Scared' ),
		),
		'Sexual' => array(
			'name' => 'Sexual',
			'function' => array( 'Prefill', 'Sexual' ),
		),
		'Smoker' => array(
			'name' => 'Smoker',
			'function' => array( 'Prefill', 'Smoker' ),
		),
		'Sports' => array(
			'name' => 'Sports',
			'function' => array( 'Prefill', 'Sports' ),
		),
		'Status' => array(
			'name' => 'Status',
			'function' => array( 'Prefill', 'Status' ),
		),
		'Tags' => array(
			'name' => 'Tags',
			'function' => array( 'Prefill', 'Tags' ),
		),
		'Turn' => array(
			'name' => 'Turn',
			'function' => array( 'Prefill', 'Turn' ),
		),
		'Turn' => array(
			'name' => 'Turn',
			'function' => array( 'Prefill', 'Turn' ),
		),
		'TV' => array(
			'name' => 'TV',
			'function' => array( 'Prefill', 'TV' ),
		),
		'Updated' => array(
			'name' => 'Updated',
			'function' => array( 'Prefill', 'Updated' ),
		),
		'URLs' => array(
			'name' => 'URLs',
			'function' => array( 'Prefill', 'URLs' ),
		),
		'Phone' => array(
			'name' => 'Phone',
			'function' => array( 'Prefill', 'Phone' ),
		),
		'Active' => array(
			'name' => 'Active',
			'function' => array( 'Prefill', 'Active' ),
		),
		'User' => array(
			'name' => 'User',
			'function' => array( 'Prefill', 'User' ),
		),
		'Locale' => array(
			'name' => 'Locale',
			'function' => array( 'Prefill', 'Locale' ),
		),
		'Positions' => array(
			'name' => 'Positions',
			'function' => array( 'Prefill', 'Positions' ),
		),
		'Verified' => array(
			'name' => 'Verified',
			'function' => array( 'Prefill', 'Verified' ),
		),
		'Access' => array(
			'name' => 'Access',
			'function' => array( 'Prefill', 'Access' ),
		),
		'Type' => array(
			'name' => 'Type',
			'function' => array( 'Prefill', 'Type' ),
		),
		'Pings' => array(
			'name' => 'Pings',
			'function' => array( 'Prefill', 'Pings' ),
		),
		'Relationship' => array(
			'name' => 'Relationship',
			'function' => array( 'Prefill', 'Relationship' ),
		),
		'Google' => array(
			'name' => 'Google',
			'function' => array( 'Prefill', 'Google' ),
		),
		'Birthday' => array(
			'name' => 'Birthday',
			'function' => array( 'Prefill', 'Birthday' ),
		),
		'Time' => array(
			'name' => 'Time',
			'function' => array( 'Prefill', 'Time' ),
		),
		'Access' => array(
			'name' => 'Access',
			'function' => array( 'Prefill', 'Access' ),
		),
		'Verified' => array(
			'name' => 'Verified',
			'function' => array( 'Prefill', 'Verified' ),
		),
		'Blood' => array(
			'name' => 'Blood',
			'function' => array( 'Prefill', 'Blood' ),
		),
		'Favorite' => array(
			'name' => 'Favorite',
			'function' => array( 'Prefill', 'Favorite' ),
		),
		'Occupation' => array(
			'name' => 'Occupation',
			'function' => array( 'Prefill', 'Occupation' ),
		),
		'Following' => array(
			'name' => 'Following',
			'function' => array( 'Prefill', 'Following' ),
		),
		'Followers' => array(
			'name' => 'Followers',
			'function' => array( 'Prefill', 'Followers' ),
		),
		'Friendships' => array(
			'name' => 'Friendships',
			'function' => array( 'Prefill', 'Friendships' ),
		),
		'Counters' => array(
			'name' => 'Counters',
			'function' => array( 'Prefill', 'Counters' ),
		),
		'Name' => array(
			'name' => 'Name',
			'function' => array( 'Prefill', 'Name' ),
		),
		'Activities' => array(
			'name' => 'Activities',
			'function' => array( 'Prefill', 'Activities' ),
		),
		'Offline' => array(
			'name' => 'Offline',
			'function' => array( 'Prefill', 'Offline' ),
		),
		'Hometown' => array(
			'name' => 'Hometown',
			'function' => array( 'Prefill', 'Hometown' ),
		),
		'Location' => array(
			'name' => 'Location',
			'function' => array( 'Prefill', 'Location' ),
		),
 */
