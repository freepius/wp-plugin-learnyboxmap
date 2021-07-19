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
	 * @param {?L.Marker} currentMemberMarker Marker of current member (if any).
	 */
	constructor( map, members, categories, currentMemberMarker ) {
		this.#map = map;

		let i = 0;
		const layersForControl = {};

		// For each category: create one layer and one icon type.
		for ( const [ id, name ] of Object.entries( categories ) ) {
			const layer = L.layerGroup().addTo( this.#map );

			categories[ id ] = {
				name,
				icon: L.divIcon( { className: `member-marker cat-${ i }`, iconSize: 15 } ),
				layer,
			};

			layersForControl[ `<span class="cat-${ i++ }">${ name }</span>` ] = categories[ id ].layer;
		}

		// Add control for category layers.
		if ( 0 !== Object.entries( layersForControl ).length ) {
			L.control.layers( {}, layersForControl, { collapsed: false } ).addTo( this.#map );
		}

		// For each member, create its marker and add it to the proper category layer.
		for ( const [ name, categoryId, latitude, longitude, description, isCurrentMember ] of members ) {
			const category = categories[ categoryId ] ?? {
				icon: L.divIcon( { className: 'member-marker cat-0', iconSize: 15 } ),
				layer: this.#map,
			};

			const marker = isCurrentMember
				? currentMemberMarker
				: L.marker( [ latitude, longitude ], { icon: category.icon } ).addTo( category.layer );

			marker
				.bindPopup(
					( category.name ? `<em>${ category.name }</em><br>` : '' ) +
					`<strong>${ name }</strong>
					${ description ? `<hr>${ description }` : '' }`
				)
				.on( 'mouseover', ( e ) => e.target.openPopup() )
				.on( 'click', ( e ) => {
					this.#map.setView( e.latlng, this.#map.options.medZoom );
				} );
		}
	}
}
