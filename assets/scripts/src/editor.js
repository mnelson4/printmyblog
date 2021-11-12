/* global wp */

/**
 * WordPress dependencies.
 */
const { Button, Dropdown, PanelRow } = wp.components;
const { PluginPostStatusInfo } = wp.editPost;
const { Component } = wp.element;
const { sprintf, __ } = wp.i18n;

const PostTypeSwitcher = ( { children, className } ) => {
    return(
        <PluginPostStatusInfo>
        <div dangerouslySetInnerHTML={{ __html: window.pmbBlockEditor.html }} />
        </PluginPostStatusInfo>
);
};

export default PostTypeSwitcher;