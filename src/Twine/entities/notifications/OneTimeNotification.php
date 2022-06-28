<?php

namespace Twine\entities\notifications;

use Twine\helpers\Array2;

/**
 * Class OneTimeNotification
 * @package Twine\entities\notifications
 */
class OneTimeNotification
{
    const TYPE_SUCCESS = 'success';
    const TYPE_WARNING = 'warning';
    const TYPE_ERROR = 'error';
    const TYPE_INFO = 'info';
    /**
     * @var string
     */
    protected $type;

    /**
     * @var string
     */
    protected $html;

    /**
     * OneTimeNotification constructor.
     * @param array $options with keys 'type' (which is one of the constants on \Twine\entities\notifications\OneTimeNotification) and 'html' (sanitized HTML to dispplay to the user)
     */
    public function __construct($options)
    {
        $this->setType(Array2::setOr($options, 'type', self::TYPE_WARNING));
        $this->html = Array2::setOr($options, 'html', '');
    }

    /**
     * @param string $type matching one of the constants on \Twine\entities\notifications\OneTimeNotification
     */
    protected function setType($type)
    {
        if (
            in_array(
                $type,
                [
                    self::TYPE_WARNING,
                    self::TYPE_ERROR,
                    self::TYPE_INFO,
                    self::TYPE_SUCCESS,
                ],
                true
            )
        ) {
            $this->type = $type;
        } else {
            $this->type = self::TYPE_WARNING;
        }
    }

    /**
     * @return string the notice in HTML
     */
    public function display()
    {
        return '<div class="notice notice-' . $this->type . '">' . $this->html . '</div>';
    }
}
