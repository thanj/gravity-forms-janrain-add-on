/*
 * Janrain Engage settings page javascript
 *
 * @package gravityforms_janrain
 *
 */

jQuery(document).ready( function() {
  jQuery('form').on( 'change click blur', 'input[type="checkbox"]',
    function(evt) {
      var label = jQuery(this).next('label').find('span.janrain-provider-icon-32');
	  var p = jQuery(this).closest('p');
      if ( label.length ) {
        if ( this.checked ) {
          label[0].className = label[0].className.replace( '-grayscale', '' );
		  var provider = jQuery(this).next('label').attr('for').replace( 'janrain_settings_provider_', '' );
		  p.removeClass( 'description' ).addClass( 'janrain-provider-text-color-'+provider );
        } else {
          if ( !label[0].className.match( 'grayscale' ) ) {
            label[0].className = label[0].className.replace( /janrain-provider-icon-([a-z]+)/, 'janrain-provider-icon-grayscale-$1' );
			p[0].className = 'description';
          }
        }
      }
    }
  );
})

