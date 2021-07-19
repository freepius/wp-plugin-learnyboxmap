/* global LearnyboxMapPlugin */

import L from 'leaflet';

/**
 * Represent and manage the current member marker.
 */
export default class {
	/**
	 * @member {L.Map}
	 */
	#map

	/**
	 * @member {L.Marker}
	 */
	marker

	/**
	 * @member {Event}
	 */
	#changeEvent = new Event( 'LearnyboxMap.CurrentMemberMarker.change' )

	/**
	 * @param {L.Map} map
	 * @param {(string|L.LatLngExpression)} [latlng] Initial coordinates, if any.
	 */
	constructor( map, latlng ) {
		this.#map = map;

		this.marker = L.marker( [ 0, 0 ], {
			draggable: true,
			autoPan: true,
			title: LearnyboxMapPlugin.t.CurrentMemberMarkerTitle,
		} );

		if ( latlng ) {
			if ( 'string' === typeof latlng ) {
				latlng = latlng.split( ',' ).map( Number.parseFloat );
			}
			this.set( latlng );
		}

		this.marker
			.on( 'move', () => document.dispatchEvent( this.#changeEvent ) )
			.on( 'click', () => this.focusOn() );
	}

	/**
	 * @return {string} Return "latitude, longitude" of the current member marker.
	 */
	get() {
		const coords = this.marker.getLatLng();
		return this.#map.hasLayer( this.marker ) ? coords.lat + ', ' + coords.lng : '';
	}

	/**
	 * @param {L.LatLngExpression} latlng
	 */
	set( latlng ) {
		this.marker.setLatLng( latlng ).addTo( this.#map );
		document.dispatchEvent( this.#changeEvent );
		return this;
	}

	delete() {
		this.#map.removeLayer( this.marker );
		document.dispatchEvent( this.#changeEvent );
	}

	/**
	 * Go on map and center on current member marker.
	 * If this one is not on the map yet, add it to center
	 */
	focusOn() {
		if ( ! this.#map.hasLayer( this.marker ) ) {
			this.set( this.#map.options.center );
		}

		this.#map.setView( this.marker.getLatLng(), this.#map.options.medZoom );

		location.href = '#map';

		// <iframe> case: indicate to the parent window to go on map (ie, on top of the map <iframe>).
		window.parent.postMessage( 'goOnTop', '*' );
	}
}
