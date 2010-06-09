jQuery(document).ready( function($) {
	$('#tml-options-user-links tbody').wpList( {
		addBefore: function( s ) {
			var parts = s.element.split('-');
			var role = parts[0];
			s.what = role + '-link';
			return s;
		},
		addAfter: function( xml, s ) {
			var parts = s.element.split('-');
			var role = parts[1];
			$('table#' + role + '-link-table').show();
		},
		delBefore: function( s ) {
			var role = s.element.split('-', 1);
			s.data.user_role = role;
			return s;
		},
		delAfter: function( r, s ) {
			$('#' + s.element).remove();
		}
	} );
} );