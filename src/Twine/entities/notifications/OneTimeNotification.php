<?php

namespace Twine\entities\notifications;

use Twine\helpers\Array2;

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

    public function __construct($options)
    {
        $this->setType(Array2::setOr($options, 'type', self::TYPE_WARNING));
        $this->html = Array2::setOr($options, 'html', '');
    }

    protected function setType($type)
    {
        if (
            in_array(
                $type,
                [
                self::TYPE_WARNING,
                self::TYPE_ERROR,
                self::TYPE_INFO,
                self::TYPE_SUCCESS
                ]
            )
        ) {
            $this->type = $type;
        } else {
            $this->type = self::TYPE_WARNING;
        }
    }

    /**
     * @return string the notice
     */
    public function display()
    {
        return '<div class="notice notice-' . $this->type . '">' . $this->html . '</div>';
    }
}
