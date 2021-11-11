# Print My Blog
WordPress Plugin that simplifies printing your entire blog. Please see https://wordpress.org/plugins/print-my-blog/

## Note to Developers on Backward Compatibility

This plugin uses [Semantic Versioning](https://semver.org/); meaning that whenever the major version changes (eg from 1.0.0 to 2.0.0) you should expect backward-incompatible changes.
If you make customizations, please contact us to inform us of them so we can avoid breaking them (eg 
created an issue in GitHub discussing what your integration/customization does and what it depends on.)

## PHP Code Sniffing
This project uses Composer for PHP code sniffing. Go to the root directory and run `composer phpcbf` to do code sniffing and automatically fix PHP code style.

## Building Javascript
This project uses NPM and webpack for building some Javascript. Go to the root directory and run `npm run build` to re-build the JS files in `assets/scripts/src`.