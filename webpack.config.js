/**
 * External Dependencies
 */
const path = require( 'path' );

/**
 * WordPress Dependencies
 */
const defaultConfig = require( '@wordpress/scripts/config/webpack.config.js' );

module.exports = {
    ...defaultConfig,
    ...{
        // Add any overrides to the default here.
        entry:{
            editor: path.resolve( process.cwd(), 'assets/scripts/src/editor', 'index.js')
        },
        output:{
            path: path.resolve(__dirname, 'assets/scripts/build'),
        }
    }
}
