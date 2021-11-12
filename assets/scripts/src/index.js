import { registerPlugin } from '@wordpress/plugins';
import { default as PostTypeSwitcher } from './editor';

registerPlugin( 'post-type-switcher', {
    render: PostTypeSwitcher,
} );