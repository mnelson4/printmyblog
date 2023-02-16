<?php

namespace Twine\forms\inputs;

use Google\Site_Kit\Core\Util\Input;
use Twine\forms\helpers\InputOption;

/**
 * Class FontInput
 * @package Twine\forms\inputs
 */
class FontInput extends SelectInput
{
    /**
     * FontInput constructor.
     * @param array $args
     */
    public function __construct($args)
    {
        if(isset($args['format']) && strtolower($args['format']) === 'epub'){
            $options = [
                '' => new InputOption(__('Default', 'print-my-blog')),
                'roboto' => new InputOption(__('Roboto', 'print-my-blog')),
                'Arial' => new InputOption(__('Arial', 'print-my-blog')),
                'Baskerville' => new InputOption(__('Baskerville', 'print-my-blog')),
                'bookerly' => new InputOption(__('Bookerly', 'print-my-blog')),
                'georgia' => new InputOption(__('Georgia', 'print-my-blog')),
                'helvetica' => new InputOption(__('Helvetica', 'print-my-blog')),
                'lucida console' => new InputOption(__('Lucida Console', 'print-my-blog')),
                'palatino linotype' => new InputOption(__('Palatino Linotype', 'print-my-blog')),
                'verdana' => new InputOption(__('Verdana', 'print-my-blog')),
            ];
        } else {
            $options = [
                'arial' => new InputOption(__('Arial', 'print-my-blog')),
                'courier new' => new InputOption(__('Courier New', 'print-my-blog')),
                'georgia' => new InputOption(__('Georgia', 'print-my-blog')),
                'impact' => new InputOption(__('Impact', 'print-my-blog')),
                'lucida console' => new InputOption(__('Lucida Console', 'print-my-blog')),
                'palatino linotype' => new InputOption(__('Palatino Linotype', 'print-my-blog')),
                'tahoma' => new InputOption(__('Tahoma', 'print-my-blog')),
                'times new roman' => new InputOption(__('Times New Roman', 'print-my-blog')),
                'verdana' => new InputOption(__('Verdana', 'print-my-blog')),
            ];
        }

        parent::__construct($options, $args);
    }
}
