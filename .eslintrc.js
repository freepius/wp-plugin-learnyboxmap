module.exports = {
	parser: 'babel-eslint',
	extends: [
		'plugin:@wordpress/eslint-plugin/esnext',
		'plugin:@wordpress/eslint-plugin/i18n',
	],
	plugins: [ 'import' ],
	env: {
		node: true,
	},
	globals: {
		window: true,
		document: true,
		fetch: true,
		location: true,
		Event: true,
	},
	rules: {
		'import/no-extraneous-dependencies': [
			'error',
			{
				peerDependencies: true,
			},
		],
		'import/no-unresolved': 'error',
		'import/default': 'warn',
		'import/named': 'warn',
	},
};
