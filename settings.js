/*
 * Janrain Engage settings page javascript
 *
 * @package gravityforms_janrain
 *
 */

jQuery(document).ready( function() {
  jQuery('form').on( 'change click blur', 'input[type="checkbox"]',
    function(evt) {
      console.log( evt, this );
      var label= jQuery(this).next('label').find('span.janrain-provider-icon-32');
      if ( label.length ) {
        if ( this.checked ) {
          label[0].className = label[0].className.replace( '-grayscale', '' );
        } else {
          if ( !label[0].className.match( 'grayscale' ) ) {
            label[0].className = label[0].className.replace( /janrain-provider-icon-([a-z]+)/, 'janrain-provider-icon-grayscale-$1' );
          }
        }
      }
    }
  );
})

