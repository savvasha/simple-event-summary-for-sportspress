/**
 * Stylelint config - WordPress coding standards for CSS.
 *
 * @package simple-event-summary-for-sportspress
 */

module.exports = {
	extends: '@wordpress/stylelint-config/stylistic',
	ignoreFiles: [ 'assets/vendor/**', 'node_modules/**', '**/*.min.css' ],
	rules: {
		// Advisory rule disabled: reordering selectors to satisfy it risks
		// changing the cascade/intent without changing the rendered result.
		'no-descending-specificity': null,
	},
};
