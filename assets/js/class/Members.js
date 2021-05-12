import L from 'leaflet';

/**
 * Represent and manage the member markers and groups.
 * Currently, members are grouped by category.
 */
export default class {
	/**
	 * @member {L.Map}
	 */
	#map

	/**
	 * @param {L.Map} map
	 * @param {Array<Array<string>>} members  Array of registered members.
	 * @param {Object} categories Object where keys are category IDs and value are category names.
	 */
	constructor( map, members, categories ) {
		this.#map = map;
		const layersById = {};
		const layersByName = {};

		// Create one layer by category.
		for ( const [ id, name ] of Object.entries( categories ) ) {
			layersById[ id ] = L.layerGroup().addTo( this.#map );
			layersByName[ name ] = layersById[ id ];
		}

		L.control.layers( {}, layersByName, { collapsed: false } ).addTo( this.#map );

		// For each member, create its marker and add it to the proper category layer.
		for ( const [ name, categoryId, latitude, longitude, description ] of members ) {
			L.marker( [ latitude, longitude ] )
				.addTo( layersById[ categoryId ] )
				.bindTooltip(
					`<strong>${ name }</strong>
					${ description ? `<hr>${ description }` : '' }`
				);
		}
	}
}
