/* global LearnyboxMapPlugin */

import CurrentMemberMarker from './CurrentMemberMarker';

// Import Leaflet library, plugins and css.
import L from 'leaflet';
import 'leaflet-providers';
import 'leaflet/dist/leaflet.css';

// Help Leaflet to find its images through Webpack
L.Icon.Default.prototype._getIconUrl = function( name ) {
	return LearnyboxMapPlugin.buildUrl + require( 'leaflet/dist/images/' + L.Icon.prototype._getIconUrl.call( this, name ) ).default;
};

/**
 * Manage the LearnyBox members map.
 */
export default class {
	/**
	 * Some base layers provide by the 'leaflet-providers' plugin
	 *
	 * @see {@link https://leaflet-extras.github.io/leaflet-providers/preview/}
	 */
	#providers = [
		'OpenStreetMap.Mapnik',
		'Esri.WorldImagery',
		'Stamen.Watercolor',
	]

	/**
	 * @member {L.Map}
	 */
	#map
	#mapOptions = {
		center: [ 46.227638, 2.213749 ], // centered on France center
		zoom: 5,
		minZoom: 2,
		maxZoom: 14,
	}

	/**
	 * Marker of the current member, ie the member who is creating/editing his LearnyboxMap profile.
	 *
	 * @member {CurrentMemberMarker}
	 */
	currentMember

	/**
	 *
	 */
	constructor() {
		this.#map = L.map( 'map', this.#mapOptions );
		this.currentMember = new CurrentMemberMarker( this.#map );

		this.#addBaseLayers();

		return this;
	}

	/**
	 * Add base layers to the Leaflet map:
	 *   -> the 'OpenStreetMap.Mapnik' layer is selected by default
	 *   -> the others are added in a control button
	 */
	#addBaseLayers() {
		const baseLayers = {};

		this.#providers.forEach( ( provider ) =>
			baseLayers[ provider.replace( /(\w+)\.(\w+)/, '$2 ($1)' ) ] = L.tileLayer.provider( provider )
		);

		baseLayers[ 'Mapnik (OpenStreetMap)' ].addTo( this.#map );

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
}
