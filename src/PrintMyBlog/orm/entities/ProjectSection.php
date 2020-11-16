<?php

namespace PrintMyBlog\orm\entities;

use PrintMyBlog\system\Context;
use stdClass;

class ProjectSection
{
    protected $ID;
    protected $post_id;
    protected $post_title;
    protected $parent_id;
    protected $section_order;
    protected $template;
    protected $placement;
    protected $height;
    protected $depth;

    /**
     * @var ProjectSection[]
     */
    protected $subsections;

    /**
     * ProjectSection constructor.
     *
     * @param stdClass $db_row
     */
    public function __construct(stdClass $db_row)
    {
        $this->ID = $db_row->ID;
        $this->post_id = $db_row->post_id;
        if (isset($db_row->post_title)) {
            $this->post_title = $db_row->post_title;
        }
        $this->parent_id = $db_row->parent_id;
        if (isset($db_row->section_order)) {
            $this->section_order = $db_row->section_order;
        }
        $this->template  = $db_row->template;
        $this->placement = $db_row->placement;
        $this->height    = $db_row->height;
        $this->depth     = $db_row->depth;
    }

    /**
     * @return int
     */
    public function getId()
    {
        return (int)$this->ID;
    }

    /**
     * @return int
     */
    public function getPostId()
    {
        return (int)$this->post_id;
    }

    /**
     * @return string
     */
    public function getPostTitle()
    {
        return $this->post_title;
    }

    /**
     * @return int
     */
    public function getParentId()
    {
        return (int)$this->parent_id;
    }

    /**
     * @return int
     */
    public function getSectionOrder()
    {
        return (int)$this->section_order;
    }

    /**
     * @return string
     */
    public function getTemplate()
    {
        return $this->template;
    }

    /**
     * @return string
     */
    public function getPlacement()
    {
        return $this->placement;
    }

    /**
     * Populates the child sections for convenience in retrieval, but does not do anything that will affect the
     * database.
     *
     * @param ProjectSection[] $sections
     */
    public function cacheSubSections($sections)
    {
        $this->subsections = $sections;
    }

    /**
     * @return ProjectSection[]
     */
    public function getCachedSubsections()
    {
        return (array)$this->subsections;
    }

    /**
     * Gets how many layers DEEP this section is. Eg, how many layers there are ABOVE it.
     * @return int
     */
    public function getDepth()
    {
        return $this->depth;
    }

    /**
     * Gets how many layers HIGH this section is. Eg, how many layers there are BELOW it.
     * @return int
     */
    public function getHeight()
    {
        return $this->height;
    }
}
