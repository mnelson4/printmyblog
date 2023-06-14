<?php

namespace PrintMyBlog\compatibility;

use PrintMyBlog\compatibility\plugins\AdvancedCustomFields;
use PrintMyBlog\compatibility\plugins\AdvancedExcerpt;
use PrintMyBlog\compatibility\plugins\CoBlocks;
use PrintMyBlog\compatibility\plugins\ContactForm7;
use PrintMyBlog\compatibility\plugins\EasyFootnotes;
use PrintMyBlog\compatibility\plugins\GoogleLanguageTranslator;
use PrintMyBlog\compatibility\plugins\JetPack;
use PrintMyBlog\compatibility\plugins\LazyLoadingFeaturePlugin;
use PrintMyBlog\compatibility\plugins\PaidMembershipsPro;
use PrintMyBlog\compatibility\plugins\TablePress;
use PrintMyBlog\compatibility\plugins\Wpml;
use PrintMyBlog\compatibility\plugins\WpVrView;
use PrintMyBlog\compatibility\plugins\YoastSeo;
use PrintMyBlog\system\Context;
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
    /**
     * @var array|null
     */
    protected $compatibility_mods = null;

    /**
     * @return CompatibilityBase[]
     */
    protected function getCompatibilityMods()
    {
        if ($this->compatibility_mods === null) {
            /**
             * @var $compatiblity_mods_to_activate CompatibilityBase[]
             */
            $compatiblity_mods_to_activate = [
                new LazyLoadingFeaturePlugin(),
            ];
            if (class_exists('easyFootnotes')) {
                $compatiblity_mods_to_activate[] = new EasyFootnotes();
            }
            if (function_exists('vr_creation')) {
                $compatiblity_mods_to_activate[] = new WpVrView();
            }
            if (class_exists('TablePress')) {
                $compatiblity_mods_to_activate[] = new TablePress();
            }
            if (class_exists('CoBlocks')) {
                $compatiblity_mods_to_activate[] = new CoBlocks();
            }
            if (class_exists('WPSEO_Sitemaps')) {
                $compatiblity_mods_to_activate[] = new YoastSeo();
            }
            if (class_exists('google_language_translator')) {
                $compatiblity_mods_to_activate[] = new GoogleLanguageTranslator();
            }
            if (defined('WPCF7_VERSION')) {
                $compatiblity_mods_to_activate[] = new ContactForm7();
            }
            if (defined('ICL_SITEPRESS_VERSION')) {
                $compatiblity_mods_to_activate[] = Context::instance()->reuse('PrintMyBlog\compatibility\plugins\Wpml');
            }
            if (defined('JETPACK__VERSION')) {
                $compatiblity_mods_to_activate[] = new JetPack();
            }
            if (defined('PMPRO_VERSION')) {
                $compatiblity_mods_to_activate[] = new PaidMembershipsPro();
            }
            if( class_exists('ACF')){
                $compatiblity_mods_to_activate[] = new AdvancedCustomFields();
            }
            if(class_exists('Advanced_Excerpt')){
                $compatiblity_mods_to_activate[] = new AdvancedExcerpt();
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

    /**
     * Using a filter as an action to initiate our callbacks
     * @param string $pre_dispatch_result
     * @return string
     */
    public function activateRenderingCompatibilityModes($pre_dispatch_result = '')
    {
        foreach ($this->getCompatibilityMods() as $compatibility_mod) {
            $compatibility_mod->setRenderingHooks();
        }
        return $pre_dispatch_result;
    }
}
// End of file DetectAndActivate.php
// Location: PrintMyBlog\compatibility/DetectAndActivate.php
