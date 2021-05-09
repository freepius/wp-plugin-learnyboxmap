/* global LearnyboxMapPlugin */

import '../styles/members-map.scss';

// Import Leaflet library, plugins and css.
import L from 'leaflet';
import 'leaflet-providers';
import 'leaflet/dist/leaflet.css';

// Help Leaflet to find its images through Webpack
L.Icon.Default.prototype._getIconUrl = function( name ) {
	return LearnyboxMapPlugin.buildUrl + require( 'leaflet/dist/images/' + L.Icon.prototype._getIconUrl.call( this, name ) ).default;
};

document.addEventListener( 'DOMContentLoaded', () => {
	const memberMarkerButton = document.getElementById( 'member-marker' );
	const searchAddressButton = document.getElementById( 'search-address' );
	const lbmap = new LearnyboxMap();

	/**
	 * On *search-address* button click:
	 * - get address from <input> field
	 * - get coords from this address
	 * - go on map and center on these coords
	 */
	searchAddressButton.addEventListener( 'click', async ( e ) => {
		e.preventDefault();
		const coords = await lbmap.latlngFromAddress( document.getElementById( 'address' )?.value );

		if ( coords ) {
			document.getElementById( 'geo_coordinates' ).value = coords[ 0 ] + ', ' + coords[ 1 ];
			lbmap.setCurrentMemberMarker( coords );
			lbmap.centerOnCurrentMemberMarker();
		} else {
			document.getElementById( 'geo_coordinates' ).value = '';
			lbmap.removeCurrentMemberMarker();
			// @todo Display an error message
		}
	} );

	/**
	 * On *member-marker* button click:
	 * - go on map and center on current member marker
	 * - if this one does not exist yet, create one on map center
	 */
	memberMarkerButton.addEventListener( 'click', ( e ) => {
		e.preventDefault();
		lbmap.centerOnCurrentMemberMarker();
		document.getElementById( 'geo_coordinates' ).value = lbmap.getCurrentMemberLatLng();
	} );
} );

/**
 * This class manage the LearnyBox members map.
 */
class LearnyboxMap {
	/**
	 * Some base layers provide by the 'leaflet-providers' plugin
	 *
	 * @see {@link https://leaflet-extras.github.io/leaflet-providers/preview/}
	 */
	#providers = [
		'Stamen.TerrainBackground',
		'Stamen.Watercolor',
		'Esri.WorldImagery',
	]

	/**
	 * @member {L.Map}
	 */
	#map
	#mapOptions = {
		center: [ 46.227638, 2.213749 ], // centered on France center
		zoom: 5, // start on a very wide view
		minZoom: 5,
		maxZoom: 16,
	}

	/**
	 * Marker of the current member, ie the member who is creating/editing his LearnyboxMap profile.
	 *
	 * @member {L.Marker}
	 */
	#currentMemberMarker

	/**
	 *
	 */
	constructor() {
		this.#map = L.map( 'map', this.#mapOptions );

		this.#addBaseLayers();

		return this;
	}

	/**
	 * Add base layers to the Leaflet map:
	 *   -> the 'TerrainBackground' layer is selected by default
	 *   -> the others are added in a control button
	 */
	#addBaseLayers() {
		const baseLayers = {};

		this.#providers.forEach( ( provider ) =>
			baseLayers[ provider.replace( /(\w+)\.(\w+)/, '$2 ($1)' ) ] = L.tileLayer.provider( provider )
		);

		baseLayers[ 'TerrainBackground (Stamen)' ].addTo( this.#map );

		L.control.layers( baseLayers ).addTo( this.#map );
	}

	/**
	 * Get geo coordinates from an address, using the Nominatim service of openstreetmap.org.
	 *
	 * @param {string} address The address for which get the geo coordinates.
	 * @return {(Array<number,number>|null)}  Return [latitude,longitude] as array, or null if no coordinate is found.
	 */
	async latlngFromAddress( address ) {
		if ( ! address ) {
			return null;
		}

		const response = await fetch(
			`https://nominatim.openstreetmap.org/?format=json&limit=1&q=${ encodeURI( address ) }`,
			{ cache: 'force-cache' }
		);

		if ( 200 !== response.status ) {
			return null;
		}

		const data = ( await response.json() )[ 0 ] ?? {};

		if ( ! data.lat || ! data.lon ) {
			return null;
		}

		return [ parseFloat( data.lat ), parseFloat( data.lon ) ];
	}

	/**************************************************
	 * Methods to handle the marker of current member.
	 *************************************************/

	/**
	 * @return {string} Return "latitude, longitude" of the current member marker.
	 */
	getCurrentMemberLatLng() {
		const coords = this.#currentMemberMarker?.getLatLng();
		return coords ? coords.lat + ', ' + coords.lng : '';
	}

	/**
	 * @param {L.LatLngExpression} latlng
	 */
	setCurrentMemberMarker( latlng ) {
		if ( ! this.#currentMemberMarker ) {
			this.#currentMemberMarker = L.marker( latlng ).addTo( this.#map );
		}

		this.#currentMemberMarker.setLatLng( latlng );
	}

	removeCurrentMemberMarker() {
		if ( this.#currentMemberMarker ) {
			this.#map.removeLayer( this.#currentMemberMarker );
			this.#currentMemberMarker = undefined;
		}
	}

	/**
	 * Go on map and center on current member marker.
	 * If this one does not exist yet, create one on map center
	 */
	centerOnCurrentMemberMarker() {
		if ( ! this.#currentMemberMarker ) {
			this.setCurrentMemberMarker( this.#mapOptions.center );
		}

		this.#map.setView(
			this.#currentMemberMarker.getLatLng(),
			this.#mapOptions.maxZoom
		);

		location.href = '#map';
	}
}
