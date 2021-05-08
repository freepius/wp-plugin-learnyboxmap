import '../styles/members-map.scss';

// Import Leaflet library, plugins and css.
import L from 'leaflet';
import 'leaflet-providers';
import 'leaflet/dist/leaflet.css';

// Help Leaflet to find its images through Webpack
L.Icon.Default.prototype._getIconUrl = function( name ) {
	return require( 'leaflet/dist/images/' + L.Icon.prototype._getIconUrl.call( this, name ) ).default;
};

document.addEventListener( 'DOMContentLoaded', () => {
	const map = new LearnyboxMap();
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
	map
	mapOptions = {
		center: [ 46.227638, 2.213749 ], // centered on France center
		zoom: 5, // start on a very wide view
		minZoom: 5,
		maxZoom: 10,
	}

	/**
	 *
	 */
	constructor() {
		this.map = L.map( 'map', this.mapOptions );

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

		baseLayers[ 'TerrainBackground (Stamen)' ].addTo( this.map );

		L.control.layers( baseLayers ).addTo( this.map );
	}
}
