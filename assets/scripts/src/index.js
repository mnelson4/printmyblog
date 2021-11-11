import { registerPlugin } from '@wordpress/plugins';
import { default as PostTypeSwitcher } from './scripts/post-duplicate-as-print-material';

registerPlugin( 'post-type-switcher', {
    render: PostTypeSwitcher,
} );