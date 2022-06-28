<?php

namespace Twine\forms\helpers;

/**
 * Class InputOption
 * @package Twine\forms\helpers
 */
class InputOption
{
    /**
     * @var string
     */
    protected $display_text;

    /**
     * @var string
     */
    protected $help_text;

    /**
     * @var bool
     */
    protected $enabled;

    /**
     * InputOption constructor.
     * @param string $display_text
     * @param null $help_text
     * @param bool $enabled
     */
    public function __construct($display_text, $help_text = null, $enabled = true)
    {
        $this->display_text = (string)$display_text;
        $this->help_text = (string)$help_text;
        $this->enabled = (bool)$enabled;
    }

    /**
     * @return string
     */
    public function getDisplayText()
    {
        return $this->display_text;
    }

    /**
     * @return string
     */
    public function getHelpText()
    {
        return $this->help_text;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return $this->getDisplayText();
    }

    /**
     * @return boolean
     */
    public function enabled()
    {
        return $this->enabled;
    }
}
