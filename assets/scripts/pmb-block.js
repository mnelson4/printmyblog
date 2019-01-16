var el = wp.element.createElement,
    registerBlockType = wp.blocks.registerBlockType,
    ServerSideRender = wp.components.ServerSideRender;

registerBlockType( 'printmyblog/setupform', {
    title: 'Print My Blog',

    icon: 'universal-access-alt',

    category: 'layout',

    edit: function(props) {
        return (
            el(ServerSideRender, {
                block: "printmyblog/setupform",
                attributes: props.attributes
            })
        );
    },

    save: function() {
        return null;
    },
} );

