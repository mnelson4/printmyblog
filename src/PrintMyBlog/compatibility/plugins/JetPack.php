<?php

namespace PrintMyBlog\compatibility\plugins;

use Twine\compatibility\CompatibilityBase;
use Twine\forms\base\FormSection;
use Twine\forms\inputs\SelectInput;

class JetPack extends CompatibilityBase
{
    public function setHooks()
    {
        add_filter(
            'PrintMyBlog\domain\DefaultDesignTemplates->getGenericDesignForm',
            [$this,'removeScaledResizeOption']
        );
    }

    /**
     * Removes the option to choose "scaled" image size as JetPack doesn't have it
     * when using their CDN.
     * @param FormSection $form
     */
    public function removeScaledResizeOption(FormSection $form)
    {
        $image_quality_input = $form->findSection('image_quality');
        if ($image_quality_input instanceof SelectInput) {
            $image_quality_input->removeOption('scaled');
        }
        return $form;
    }
}
