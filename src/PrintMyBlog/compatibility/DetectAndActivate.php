<?php

namespace PrintMyBlog\compatibility;

use PrintMyBlog\compatibility\plugins\EasyFootnotes;
use Twine\compatibility\CompatibilityBase;

/**
 * Class DetectAndActivate
 *
 * Description
 *
 * @package     Event Espresso
 * @author         Mike Nelson
 * @since         2.1.4
 *
 */
class DetectAndActivate
{
    /**
     * @since 2.1.4
     */
    public function detectAndActivateCompatibilityMods()
    {
        /**
         * @var $compatiblity_mods_to_activate CompatibilityBase[]
         */
        $compatiblity_mods_to_activate = [];
        if(class_exists('easyFootnotes')){
            $compatiblity_mods_to_activate[] = new EasyFootnotes();
        }

        foreach($compatiblity_mods_to_activate as $compatibility_mod){
            $compatibility_mod->setHooks();
        }
    }

}
// End of file DetectAndActivate.php
// Location: PrintMyBlog\compatibility/DetectAndActivate.php
