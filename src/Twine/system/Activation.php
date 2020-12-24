<?php

namespace Twine\system;

/**
 * Class Activation
 *
 * Description
 *
 * @package        Print My Blog
 * @author         Mike Nelson
 * @since          $VID:$
 *
 */
abstract class Activation
{

    /**
     * @var RequestType
     */
    protected $request_type;

    public function inject(
        RequestType $request_type
    ) {
        $this->request_type = $request_type;
    }
    /**
     * Redirects the user to the blog printing page if the user just activated the plugin and
     * they have the necessary capability.
     * @since 1.0.0
     */
    public function detectActivation()
    {
        if ($this->request_type->shouldCheckDb()) {
            $this->install();
        }
    }


    /**
     * Checks the DB and other options are present
     */
    abstract public function install();
}
