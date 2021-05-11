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
	#marker

	/**
	 * @member {Event}
	 */
	#changeEvent = new Event( 'LearnyboxMap.CurrentMemberMarker.change' )

	/**
	 * @param {L.Map} map
	 */
	constructor( map ) {
		this.#map = map;

		// Init a marker, but do not add it to the map yet.
		this.#marker = L.marker( [ 0, 0 ], {
			draggable: true,
			autoPan: true,
			title: LearnyboxMapPlugin.t.CurrentMemberMarkerTitle,
		} );

		this.#marker.on( 'move', () => document.dispatchEvent( this.#changeEvent ) );
	}

	/**
	 * @return {string} Return "latitude, longitude" of the current member marker.
	 */
	get() {
		const coords = this.#marker.getLatLng();
		return this.#map.hasLayer( this.#marker ) ? coords.lat + ', ' + coords.lng : '';
	}

	/**
	 * @param {L.LatLngExpression} latlng
	 */
	set( latlng ) {
		this.#marker.setLatLng( latlng ).addTo( this.#map );
		document.dispatchEvent( this.#changeEvent );
	}

	delete() {
		this.#map.removeLayer( this.#marker );
		document.dispatchEvent( this.#changeEvent );
	}

	/**
	 * Go on map and center on current member marker.
	 * If this one is not on the map yet, add it to center
	 */
	focusOn() {
		if ( ! this.#map.hasLayer( this.#marker ) ) {
			this.set( this.#map.options.center );
		}

		const [ min, max ] = [ this.#map.options.maxZoom, this.#map.options.minZoom ];

		this.#map.setView(
			this.#marker.getLatLng(),
			min + Math.floor( ( max - min ) / 2 )
		);

		location.href = '#map';
	}
}
