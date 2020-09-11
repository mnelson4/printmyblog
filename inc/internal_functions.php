<?php
/**
 * Functions used internally by Print My Blog that other devs probably won't need.
 */

/**
 * Returns a string that says this feature only works with Print My Blog Pro.
 * @return string
 */
function pmb_pro_only(){
	return ' ' . __('*Pro Only*', 'print-my-blog');
}

/**
 * Returns a string that says this feature works best with Print My Blog Pro.
 * @return string
 */
function pmb_pro_best(){
	return ' ' . __('*Best with Pro*', 'print-my-blog');
}

/**
 * Whether or not this is the pro version.
 * @todo BETA replace with Freemius magic
 * @return bool
 */
function pmb_pro(){
	return defined('PMB_PRO');
}