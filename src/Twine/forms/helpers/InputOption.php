<?php

namespace Twine\forms\helpers;

class InputOption
{
    protected $display_text;
    protected $help_text;
    protected $enabled;

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
    public function enabled(){
        return $this->enabled;
    }
}
