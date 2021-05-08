import '../styles/members-map.scss';

// Import Leaflet library, plugins and css.
import L from 'leaflet';
import 'leaflet-providers';
import 'leaflet/dist/leaflet.css';

// Help Leaflet to find its images through Webpack
L.Icon.Default.prototype._getIconUrl = function( name ) {
	return require( 'leaflet/dist/images/' + L.Icon.prototype._getIconUrl.call( this, name ) ).default;
};
