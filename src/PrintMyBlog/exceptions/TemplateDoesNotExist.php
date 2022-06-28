<?php

namespace PrintMyBlog\exceptions;

use Exception;

/**
 * Class TemplateDoesNotExist
 * @package PrintMyBlog\exceptions
 */
class TemplateDoesNotExist extends Exception
{
    /**
     * TemplateDoesNotExist constructor.
     * @param string $template_file
     */
    public function __construct($template_file)
    {
        parent::__construct(
            sprintf(
                // translators: %s: file path
                __('Template file "%s" should exist but doesn\'t.', 'print-my-blog'),
                $template_file
            )
        );
    }
}
