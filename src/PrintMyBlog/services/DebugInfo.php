<?php

namespace PrintMyBlog\services;

use PrintMyBlog\exceptions\DesignTemplateDoesNotExist;
use PrintMyBlog\orm\entities\Design;
use PrintMyBlog\orm\entities\Project;
use PrintMyBlog\orm\managers\DesignManager;
use PrintMyBlog\orm\managers\ProjectManager;
use WP_Debug_Data;

/**
 * Class DebugInfo
 * @package PrintMyBlog\services
 */
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

    /**
     * @param ProjectManager $project_manager
     * @param DesignManager $design_manager
     */
    public function inject(ProjectManager $project_manager, DesignManager $design_manager)
    {
        $this->project_manager = $project_manager;
        $this->design_manager = $design_manager;
    }

    /**
     * @param bool $pretty
     * @return string
     */
    public function getDebugInfoString($pretty = true)
    {
        if (! defined('JSON_PRETTY_PRINT')) {
            define('JSON_PRETTY_PRINT', 128);
        }
        return wp_unslash(wp_json_encode($this->getDebugInfo(), $pretty ? JSON_PRETTY_PRINT : null));
    }

    /**
     * @return array
     * @throws \ImagickException
     */
    public function getDebugInfo()
    {
        require_once ABSPATH . 'wp-admin/includes/class-wp-debug-data.php';
        $all_debug_core_info = WP_Debug_Data::debug_data();

        $plugins_active = $all_debug_core_info['wp-plugins-active']['fields'];
        $simplified_plugin_data = [];
        foreach ($plugins_active as $plugin_slug => $plugin_info) {
            $version = str_replace('Version ', '', $plugin_info['value']);
            $unnecessary_auto_updates_string_location = strpos($version, '| Auto-updates');
            if ($unnecessary_auto_updates_string_location !== false) {
                $version = substr($version, 0, $unnecessary_auto_updates_string_location);
            }
            $simplified_plugin_data[$plugin_slug] = $version;
        }
        $active_theme = $all_debug_core_info['wp-active-theme']['fields'];
        $simplified_theme_data = [];
        $simplified_theme_data = [
            'name' => $active_theme['name']['value'],
            'version' => $active_theme['version']['value'],
            'author' => $active_theme['author']['value'],
            'author_website' => $active_theme['author_website']['value'],
            'parent_theme' => $active_theme['parent_theme']['value'],
            'theme_features' => $active_theme['theme_features']['value'],
        ];
        $is_ssl                 = is_ssl();
        $is_multisite           = is_multisite();
        $blog_public            = get_option('blog_public');
        if (function_exists('wp_get_environment_type')) {
            $environment_type = wp_get_environment_type();
        } else {
            $environment_type = 'unknown';
        }
        $core_version           = get_bloginfo('version');
        $language = get_locale();
        $home_url = get_bloginfo('url');
        $site_url = get_bloginfo('wpurl');
        $debug = defined(WP_DEBUG) ? WP_DEBUG : false;
        $post_max_size       = ini_get('post_max_size');
        $upload_max_filesize = ini_get('upload_max_filesize');
        $effective           = min(
            wp_convert_hr_to_bytes($post_max_size),
            wp_convert_hr_to_bytes($upload_max_filesize)
        );
        $php_version = phpversion();


        return [
            'pmb' => PMB_VERSION,
            'pmb_pro' => pmb_fs()->is_premium(),
            'php' => $php_version,
            'wp' => $core_version,
            'site_url' => $site_url,
            'home_url' => $home_url,
            'language' => $language,
            'public' => (bool)$blog_public,
            'environment_type' => $environment_type,
            'plugins_active' => $simplified_plugin_data,
            'active_theme' => $simplified_theme_data,
            'debug' => $debug,
            'post_max_size' => $post_max_size,
            'upload_max_size' => $upload_max_filesize,
            'effective_max_size' => $effective,
            'ssl' => $is_ssl,
            'multisite' => $is_multisite,
            'projects' => $this->getProjectData(),
            'designs' => $this->getDesignData(),
        ];
    }

    /**
     * @return array
     */
    protected function getDesignData()
    {
        $design_datas = [];
        $designs = $this->design_manager->getAll(
            new \WP_Query()
        );
        foreach ($designs as $design) {
            $design_datas[] = $this->simplifyDesignData($design);
        }
        return $design_datas;
    }

    /**
     * @return array
     */
    protected function getProjectData()
    {
        /**
         * @var Project[] $projects
         */
        $projects = $this->project_manager->getAll(
            new \WP_Query(
                [
                    'order' => 'DESC',
                    'orderby' => 'modified',
                    'posts_per_page' => 5,
                ]
            )
        );
        $project_datas = [];
        foreach ($projects as $project) {
            $project_data = [
                'title' => $project->getWpPost()->post_title,
                'generations' => [],
                'meta' => [],
                'designs' => [],
            ];
            foreach ($project->getDesigns() as $format => $design) {
                $project_data['designs'][$format] = $design->getWpPost()->post_title . ' (ID:' . $design->getWpPost()->ID . ')';
            }
            foreach ($project->getAllGenerations() as $generation) {
                $project_data['generations'][$generation->getFormat()->slug()] = $generation->getGeneratedIntermediaryFileUrl();
            }
            $project_data['meta'] = $this->simplifyProjectMeta($this->simpifyMetadata(get_post_meta($project->getWpPost()->ID)));
            $project_datas[] = $project_data;
        }
        return $project_datas;
    }

    /**
     * @param array $project_meta
     * @return array
     */
    protected function simplifyProjectMeta($project_meta)
    {
        $metas = array_diff_key(
            $project_meta,
            array_flip(
                [
                    '_pmb_pmb_code',
                    '_wp_old_slug',
                    '_pmb_format',
                    '_pmb_progress_setup',
                    '_pmb_levels_used',
                ]
            )
        );
        $starters_to_ignore = [
            '_pmb_progress_',
            '_pmb_design_for',
            '_pmb_dirty_',
            '_pmb_last_section_',
            '_pmb_generated_',
        ];
        $final_metas = [];
        foreach ($metas as $key => $value) {
            $ok = true;
            foreach ($starters_to_ignore as $starter_to_ignore) {
                if (
                    strpos(
                        $key,
                        $starter_to_ignore
                    ) === 0
                ) {
                    $ok = false;
                    break;
                }
            }
            if ($ok) {
                $final_metas[$key] = $value;
            }
        }
        return $final_metas;
    }

    /**
     * @param Design $design
     * @return array
     */
    protected function simplifyDesignData(Design $design)
    {
        try {
            $template = $design->getDesignTemplate();
            $title = $template->getTitle();
        } catch (DesignTemplateDoesNotExist $error) {
            $title = 'Template no longer active';
        }

        return [
            'title' => $design->getWpPost()->post_title,
            'ID' => $design->getWpPost()->ID,
            'template' => $title,
            'meta' => array_diff_key(
                $this->simpifyMetadata(get_post_meta($design->getWpPost()->ID)),
                array_flip(
                    [
                        '_pmb_format',
                        '_pmb_design_template',
                        '_pmb_preview_1_url',
                        '_pmb_preview_1_desc',
                        '_pmb_preview_2_url',
                        '_pmb_preview_2_desc',
                        '_pmb_author_name',
                        '_pmb_author_url',
                    ]
                )
            ),
        ];
    }

    /**
     * Make it look pretty so it's easy to find info in it.
     * @param array $metadata
     * @return array
     */
    protected function simpifyMetadata($metadata)
    {
        $simplified_metas = [];
        foreach ($metadata as $meta_key => $meta_values) {
            $value = reset($meta_values);
            // if it's serialized data, it'd be nice to show it as JSON instead
            $simplified_metas[$meta_key] = maybe_unserialize($value);
        }
        return $simplified_metas;
    }
}
