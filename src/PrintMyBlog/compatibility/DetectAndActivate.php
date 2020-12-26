<?php

namespace PrintMyBlog\compatibility;

use PrintMyBlog\compatibility\plugins\EasyFootnotes;
use PrintMyBlog\compatibility\plugins\LazyLoadingFeaturePlugin;
use PrintMyBlog\compatibility\plugins\WpVrView;
use Twine\compatibility\CompatibilityBase;

/**
 * Class DetectAndActivate
 *
 * Description
 *
 * @package     Print My Blog
 * @author         Mike Nelson
 * @since         2.1.4
 *
 */
class DetectAndActivate
{
    protected $compatibility_mods = null;

    /**
     * @return CompatibilityBase[]
     */
    protected function getCompatibilityMods(){
        if($this->compatibility_mods === null){
            /**
             * @var $compatiblity_mods_to_activate CompatibilityBase[]
             */
            $compatiblity_mods_to_activate = [
                new LazyLoadingFeaturePlugin(),
            ];
            if (class_exists('easyFootnotes')) {
                $compatiblity_mods_to_activate[] = new EasyFootnotes();
            }
            if(function_exists('vr_creation')){
                $compatiblity_mods_to_activate[] = new WpVrView();
            }
            $this->compatibility_mods = $compatiblity_mods_to_activate;
        }
        return $this->compatibility_mods;
    }
    /**
     * @since 2.1.4
     */
    public function detectAndActivateGlobalCompatibilityMods()
    {
        $compatiblity_mods_to_activate = $this->getCompatibilityMods();
        foreach ($compatiblity_mods_to_activate as $compatibility_mod) {
            $compatibility_mod->setHooks();
        }
    }

    public function activateRenderingCompatibilityModes(){
        foreach($this->getCompatibilityMods() as $compatibilityMod){
            $compatibilityMod->setRenderingHooks();
        }
    }
}
// End of file DetectAndActivate.php
// Location: PrintMyBlog\compatibility/DetectAndActivate.php
