<?php


namespace PrintMyBlog\services;


use PrintMyBlog\orm\entities\Design;
use PrintMyBlog\orm\entities\Project;
use PrintMyBlog\orm\managers\DesignManager;
use PrintMyBlog\orm\managers\ProjectManager;
use WP_Debug_Data;

class DebugInfo
{

    /**
     * @var ProjectManager
     */
    protected $project_manager;

    /**
     * @var DesignManager
     */
    protected $design_manager;

    public function inject(ProjectManager $project_manager, DesignManager $design_manager){
        $this->project_manager = $project_manager;
        $this->design_manager = $design_manager;
    }

    /**
     * @return string
     */
    public function getDebugInfoString(){
        if(! defined('JSON_PRETTY_PRINT')){
            define('JSON_PRETTY_PRINT', 128);
        }
        return wp_json_encode($this->getDebugInfo(), JSON_PRETTY_PRINT);
    }

    /**
     * @return array
     * @throws \ImagickException
     */
    public function getDebugInfo(){
        require_once ABSPATH . 'wp-admin/includes/class-wp-debug-data.php';
        $all_debug_core_info = WP_Debug_Data::debug_data();

        $plugins_active = $all_debug_core_info['wp-plugins-active']['fields'];
        $active_theme = $all_debug_core_info['wp-active-theme']['fields'];
        $is_ssl                 = is_ssl();
        $is_multisite           = is_multisite();
        $blog_public            = get_option( 'blog_public' );
        if(function_exists('wp_get_environment_type')){
            $environment_type       = wp_get_environment_type();
        } else {
            $environment_type = 'unknown';
        }
        $core_version           = get_bloginfo( 'version' );
        $language = get_locale();
        $home_url = get_bloginfo( 'url' );
        $site_url = get_bloginfo( 'wpurl' );
        $debug = defined(WP_DEBUG) ? WP_DEBUG: false;
        $post_max_size       = ini_get( 'post_max_size' );
        $upload_max_filesize = ini_get( 'upload_max_filesize' );
        $effective           = min( wp_convert_hr_to_bytes( $post_max_size ), wp_convert_hr_to_bytes( $upload_max_filesize ) );
        $php_version = phpversion();


        return [
            'php' => $php_version,
            'wp' => $core_version,
            'site_url' => $site_url,
            'home_url' => $home_url,
            'language' => $language,
            'public' => $blog_public,
            'environment_type' => $environment_type,
            'plugins_active' => $plugins_active,
            'active_theme' => $active_theme,
            'debug' => $debug,
            'post_max_size' => $post_max_size,
            'upload_max_size' => $upload_max_filesize,
            'effective_max_size' => $effective,
            'ssl' => $is_ssl,
            'multisite' => $is_multisite,
            'projects' => $this->getProjectData(),
            'designs' => $this->getDesignData()
        ];

    }

    protected function getProjectData()
    {
        /**
         * @var Project[] $projects
         */
        $projects = $this->project_manager->getAll(
            new \WP_Query([
                'order' => 'DESC',
                'posts_per_page' => 10
            ])
        );
        $project_datas = [];
        foreach ($projects as $project) {
            $project_data = [
                'title' => $project->getWpPost()->post_title,
            ];

            foreach($project->getAllGenerations() as $generation){
                $project_data['generations'][$generation->getFormat()->slug()] = $generation->getGeneratedIntermediaryFileUrl();
            }
            $project_data['meta'] = get_post_meta($project->getWpPost()->ID);
            $project_datas[] = $project_data;
        }
        return $project_datas;
    }
    protected function getDesignData(){

        /**
         * @var $designs Design[]
         */
        $designs = $this->design_manager->getAll(
            new \WP_Query([
                'posts_per_page' => 10
            ])
        );
        $design_datas = [];
        foreach($designs as $design){

            $design_datas[] = [
                'title' => $design->getWpPost()->ID,
                'template' => $design->getDesignTemplate()->getTitle(),
                'meta' => get_post_meta($design->getWpPost()->ID)
            ];
        }
        return $design_datas;
    }
}