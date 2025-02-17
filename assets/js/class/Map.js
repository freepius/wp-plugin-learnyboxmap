/* global LearnyboxMapPlugin */

import Members from './Members';
import CurrentMember from './CurrentMember';

// Import Leaflet library, plugins and css.
import L from 'leaflet';
import 'leaflet-providers';
import 'leaflet/dist/leaflet.css';

/**
 * Fix Leaflet images URLs for marker.
 */

// Import marker images via Webpack (these imports will point to the URLs generated by Webpack)
import markerIcon from 'leaflet/dist/images/marker-icon.png';
import markerIcon2x from 'leaflet/dist/images/marker-icon-2x.png';
import markerShadow from 'leaflet/dist/images/marker-shadow.png';

// Remove the existing _getIconUrl method (to force the URLs update)
delete L.Icon.Default.prototype._getIconUrl;

// Merge default options with the new URLs
L.Icon.Default.mergeOptions({
  iconRetinaUrl: markerIcon2x,
  iconUrl: markerIcon,
  shadowUrl: markerShadow,
});

/**
 * Manage the LearnyBox members map.
 */
export default class {
	/**
	 * Some base layers provide by the 'leaflet-providers' plugin.
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
		medZoom: 8,
	}

	/**
	 * Object managing member markers and layers.
	 *
	 * @member {Members}
	 */
	members

	/**
	 * Marker of the current member, ie the member who is creating/editing his LearnyboxMap profile.
	 *
	 * @member {CurrentMember}
	 */
	currentMember

	/**
	 * @param {Array<Array<string>>} members An array of registered members.
	 * @param {Object} categories Object where keys are category IDs and value are category names.
	 * @param {(string|L.LatLngExpression|null)} [latlng] Initial current member marker coordinates, if any.
	 */
	constructor( members, categories, latlng ) {
		this.#map = L.map( 'map', this.#mapOptions );
		this.currentMember = new CurrentMember( this.#map, latlng );
		this.members = new Members( this.#map, members, categories, this.currentMember.marker );

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

		L.control.layers( baseLayers, {}, { position: 'topleft' } ).addTo( this.#map );
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
