<?php

namespace PrintMyBlog\system;

/**
 * Class CustomPostTypes
 *
 * Description
 *
 * @package        Print My Blog
 * @author         Mike Nelson
 * @since          $VID:$
 *
 */
class CustomPostTypes
{
    const PROJECTS = 'pmb_projects';

    /**
     * This must not be done before init eh.
     */
    public function register()
    {
        register_post_type(
            self::PROJECTS,
            [
                'label' => esc_html__('Projects', 'print-my-blog'),
                'description' => esc_html__('Projects for printing with Print My Blog', 'print-my-blog'),
                // 'show_in_menu' => true,
                // 'show_ui' => true,
                'capability_type' => 'pmb_projects',
            ]
        );
    }
}