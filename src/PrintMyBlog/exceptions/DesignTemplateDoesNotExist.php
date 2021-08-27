<?php
namespace PrintMyBlog\exceptions;

use Exception;

class DesignTemplateDoesNotExist extends Exception
{
    public function __construct($design_slug)
    {
        parent::__construct(
            sprintf(
                __('The design with slug "%s" should exist but doesn\'t. Check the plugin that added that design is still active (and if it required a subscription, that the subscription is still up-to-date). For now, please choose a different design.', 'print-my-blog'),
                $design_slug
            )
        );
    }
}