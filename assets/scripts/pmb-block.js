var el = wp.element.createElement,
    registerBlockType = wp.blocks.registerBlockType,
    blockStyle = { backgroundColor: '#900', color: '#fff', padding: '20px' };

registerBlockType( 'printmyblog/setupform', {
    title: 'Print My Blog',

    icon: 'universal-access-alt',

    category: 'layout',

    edit: function() {
        return 'Print My Blog Form Here';
    },

    save: function() {
        return null;
    },
} );

