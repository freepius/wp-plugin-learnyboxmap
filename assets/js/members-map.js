/* global LearnyboxMapPlugin */

import LearnyboxMap from './class/Map';
import '../styles/members-map.scss';

document.addEventListener( 'DOMContentLoaded', () => {
	const memberAddress = document.getElementById( 'address' );
	const memberCoords = document.getElementById( 'geo_coordinates' );

	const searchAddressButton = document.getElementById( 'search-address' );
	const memberMarkerButton = document.getElementById( 'member-marker' );

	const unknownAddressError = document.getElementById( 'error-address' );

	const lbmap = new LearnyboxMap( LearnyboxMapPlugin.members, LearnyboxMapPlugin.categories, memberCoords.value );

	/**
	 * On *search-address* button click:
	 * - get address from <input> field
	 * - get coords from this address
	 * - set current member marker with these coords
	 * - go on map and center on these coords
	 * - if no coord found: delete current member marker
	 */
	searchAddressButton.addEventListener( 'click', async ( e ) => {
		e.preventDefault();

		if ( '' === memberAddress.value ) {
			return;
		}

		const coords = await lbmap.latlngFromAddress( memberAddress.value );

		if ( coords ) {
			lbmap.currentMember.set( coords ).focusOn();
			unknownAddressError.style.display = 'none';
		} else {
			lbmap.currentMember.delete();
			unknownAddressError.style.display = 'block';
		}
	} );

	memberMarkerButton.addEventListener( 'click', ( e ) => {
		e.preventDefault();
		lbmap.currentMember.focusOn();
	} );

	document.addEventListener( 'LearnyboxMap.CurrentMemberMarker.change', () => memberCoords.value = lbmap.currentMember.get() );
} );
