<?php

use PrintMyBlog\orm\entities\Project;

/**
 * @var $project \PrintMyBlog\orm\entities\Project
 * @var $form_url string
 * @var $project_support_front_matter bool
 * @var $project_support_back_matter bool
 * @var $front_matter_sections \PrintMyBlog\orm\entities\ProjectSection[]
 * @var $main_matter_sections \PrintMyBlog\orm\entities\ProjectSection[]
 * @var $back_matter_sections \PrintMyBlog\orm\entities\ProjectSection[]
 * @var $post_types WP_Post_Type[]
 * @var $authors WP_User[]
 * @var $steps_to_urls array
 * @var $current_step string
 */
pmb_render_template(
	'partials/project_header.php',
	[
		'project' => $project,
		'page_title' => __('Edit Project Content', 'print-my-blog'),
		'current_step' => $current_step,
		'steps_to_urls' => $steps_to_urls
	]
);
?>
    <form id="pmb-filter-form" method="get" action="<?php echo esc_attr( admin_url( 'admin-ajax.php' ));?>">
        <input type="hidden" name="action" value="pmb_post_search">
        <input type="hidden" name="project" value="<?php echo $project->getWpPost()->ID;?>">
    </form>
    <form id="pmb-project-form" method="POST" action="<?php echo $form_url;?>">
        <div id="pmb-project-layout" class="pmb-project-layout">
            <div class="pmb-project-layout-inner">
                <div class="pmb-project-column pmb-project-choices-column">
                    <div class="pmb-project-choices-text"><h2 class="pmb-inline-header pmb-snug-header"><span class="dashicons dashicons-search"></span><?php _e('Select Content', 'print-my-blog');?></h2>
                            <span class="pmb-comment"><?php _e('Use <em>CTRL</em> or <em>SHIFT</em> to select and drag multiple items', 'print-my-blog');?></span></div>
                    <div class="pmb-project-choices-filters pmb-scrollable-window">
                        <div class="pmb-project-choices-filters-table-wrap">
                            <div class="pmb-button-spacer">
                                <input id="pmb-filter-form-submit" type="submit" for="pmb-filter-form" class="button button-secondary" value="<?php echo esc_attr(_e('Search & Apply Filters', 'print-my-blog'));?>">
                            </div>
                            <table class="form-table">
                                <tr>
                                    <th><label for="pmb-project-choices-search"><?php esc_html_e('Search', 'print-my-blog');?></th>
                                    <td><input id="pmb-project-choices-search" type="text" name="pmb-search" form="pmb-filter-form"></td>
                                </tr>
                                <tr>
                                    <th><label for="pmb-project-choices-post-type"><?php esc_html_e('Post Type', 'print-my-blog');?></label></th>
                                    <td>
                                        <select id="pmb-project-choices-post-type" name="pmb-post-type" form="pmb-filter-form">
                                            <option value=""><?php esc_html_e('Any', 'print-my-blog');?></option>
                                            <?php
                                            foreach($post_types as $post_type_obj){
                                                ?><option value="<?php echo esc_attr($post_type_obj->name);?>"><?php echo $post_type_obj->label;?></option><?php
                                            }
                                            ?>
                                        </select>
                                    </td>
                                </tr>
                                <tr>
                                    <th><?php esc_html_e('Status','print-my-blog' );?></th>
                                    <td> <?php
                                        // status
                                        $statuses = [
                                            'draft'    => esc_html__( 'Draft' ),
                                            'pending'  => esc_html__( 'Pending Review' ),
                                            'private'  => esc_html__( 'Private' ),
                                            'publish'  => esc_html__( 'Published' ),
                                            'future'   => esc_html__( 'Scheduled' ),
                                            'trash'    => esc_html__( 'Trash' )
                                        ];
                                        foreach($statuses as $value => $label ){
                                            ?>
                                            <input type="checkbox" name="pmb-status[]" value="<?php echo esc_attr($value);?>" id="<?php echo esc_attr($value);?>-id" <?php echo in_array($value, ['publish', 'private']) ? 'checked="checked"' : '';?> form="pmb-filter-form">
                                            <label for="<?php echo esc_attr($value);?>-id"><?php echo $label;?></label><br>
                                            <?php
                                        }
                                        ?></td>
                                </tr>
                                <?php foreach(get_taxonomies(array('show_in_rest' => true, 'show_ui' => true), 'objects') as $taxonomy){
                                    $rest_base = $taxonomy->rest_base ? $taxonomy->rest_base : $taxonomy->name;
                                    /**
                                     * @var $taxonomy WP_Taxonomy
                                     */
                                    ?>
                                    <tr>
                                        <th><label for="pmb-project-choices-by"><?php echo $taxonomy->label; ?></label></th>
                                        <td>
                                            <select id="pmb-taxonomy-<?php echo esc_attr($taxonomy->name);?>" class="pmb-taxonomies-select" name="taxonomies[<?php echo esc_attr($taxonomy->name);?>][]" multiple="multiple"data-rest-base="<?php echo esc_attr($rest_base);?>" form="pmb-filter-form"></select>
                                        </td>
                                    </tr>
                                <?php
                                }
                                ?>
                                <tr>
                                    <th><label for="pmb-project-choices-by"><?php esc_html_e('By', 'print-my-blog'); ?></label></th>
                                    <td>
                                        <select id="pmb-project-choices-by" class="pmb-author-select" name="pmb-author" form="pmb-filter-form">
                                            <option value=""></option>
                                            <?php
                                            foreach($authors as $author){
                                                ?><option value="<?php echo esc_attr($author->ID);?>"><?php echo $author->display_name;?></option><?php
                                            }
                                            ?>
                                        </select>
                                    </td>
                                </tr>
                                <tr>
                                    <th><?php esc_html_e('Written Between', 'print-my-blog');?></th>
                                    <td><?php printf(
                                            // translators: 1: "from" date input, 2: "to" date input
                                            __('%s and %s', 'print-my-blog'),
                                        '<input id="pmb-from-date" type="text" class="pmb-date" name="pmb-date[from]" form="pmb-filter-form">',
                                        '<input id="pmb-to-date" type="text" class="pmb-date" name="pmb-date[to]" form="pmb-filter-form">'
                                    ); ?>
                                    </td>
                                </tr>
                                <tr>
                                    <th><label for="pmb-project-choices-order-by"><?php esc_html_e('Order By', 'print-my-blog');?></label></th>
                                    <td><select id="pmb-project-choices-order-by" name="pmb-order-by" form="pmb-filter-form">
                                            <?php
                                            $orderby = [
                                                'title' => __('Title', 'print-my-blog'),
                                                'date' => __('Date', 'print-my-blog'),
                                                'menu_order' => __('Menu Order', 'print-my-blog'),
                                                'relevance' => __('Search Relevance', 'print-my-blog'),
                                                'ID' => __('Post ID', 'print-my-blog'),
                                            ];
                                            $default = 'ID';
                                            foreach($orderby as $value => $display){
                                                ?><option value="<?php echo esc_attr($value);?>" <?php echo $value === $default ? 'selected' : '';?> ><?php echo $display;?></option><?php
                                            }
                                            ?>
                                        </select>
                                        <br>
                                        <select name="pmb-order" form="pmb-filter-form">
                                            <option value="ASC"><?php esc_html_e('Ascending', 'print-my-blog');?></option>
                                            <option value="DESC" selected><?php esc_html_e('Descending', 'print-my-blog');?></option>
                                        </select>
                                    </td>
                                </tr>
                            </table>
                        </div>
                    </div>
                    <div class="pmb-project-content-available list-group pmb-scrollable-window">
                    <div id="pmb-project-choices" class="pmb-draggable-area pmb-project-choices " style="height:1000px">
                        <div class="no-drag"><div class="pmb-spinner"></div></div>
                    </div>

                    </div>
                </div>
                <div class="pmb-actions-column"><button id="pmb-add-item" title="<?php
                    echo esc_attr(
                        sprintf(
                            __('Add selected items to project', 'print-my-blog'),
                            $title
                        )
                    );
                    ?>">&gt;</button>
                    <button id="pmb-remove-item" title="<?php
                    echo esc_attr(
                        sprintf(
                            __('Remove selected items', 'print-my-blog'),
                            $title
                        )
                    );
                    ?>">x</button><button>^</button><button>v</button></div>
                <div class="pmb-project-column pmb-project-matters-wrapper">
                    <h2 class="pmb-snug-header"><span class="dashicons dashicons-portfolio"></span> <?php esc_html_e('Project Content', 'print-my-blog');?></h2>
                    <div class="pmb-project-matters pmb-scrollable-window">
                        <?php if($project_support_front_matter){
                           ?>
                            <h2><?php esc_html_e('Front Matter', 'print-my-blog');?></h2>
                            <div id="pmb-project-front-matter" class="pmb-draggable-area pmb-project-content-chosen list-group pmb-sortable pmb-sortable-base pmb-sortable-root" data-max-nesting="0">
	                            <?php
	                            foreach($front_matter_sections as $post) {
		                            pmb_content_item( $post, $project, 0 );
	                            }
	                            pmb_drag_here();
	                            ?>
                            </div>

                        <?php
                        }?>
                        <h2>
                            <?php _e('Main Matter', 'print-my-blog');?>
                            <span class="pmb-help">
                            <?php
                            $all_designs = $project->getDesignsSelected();
                            $a_design = reset($all_designs);
                            if($project->getLevelsAllowed() > 0 ){
                                $division_descriptions = [];
			                        for($i=0; $i < $project->getLevelsAllowed(); $i++){
                                        $division_descriptions[]=  sprintf(
						                        __('each %1$s can be put in a %2$s', 'print-my-blog'),
                                                $a_design->getDesignTemplate()->divisionLabelSingular($i),
                                                $a_design->getDesignTemplate()->divisionLabelSingular($i+1)
					                        );
			                        }
			                        echo implode(', ', $division_descriptions);
		                        }
                            ?></span>
                        </h2>
                        <div id="pmb-project-main-matter" class="pmb-draggable-area pmb-project-content-chosen list-group pmb-sortable pmb-sortable-base pmb-sortable-root" data-max-nesting="<?php echo esc_attr($project->getLevelsAllowed());?>">
                            <?php
                            foreach($sections as $post) {
                                pmb_content_item( $post, $project );
                            }
                            pmb_drag_here();
                            ?>
                        </div>
	                    <?php if($project_support_back_matter){
		                    ?>
                            <h2><?php esc_html_e('Back Matter', 'print-my-blog');?></h2>
                            <div id="pmb-project-back-matter" class="pmb-draggable-area pmb-project-content-chosen list-group pmb-sortable pmb-sortable-base pmb-sortable-root" data-max-nesting="0">
			                    <?php
			                    foreach($back_matter_sections as $post) {
				                    pmb_content_item( $post, $project, 0 );
			                    }
			                    pmb_drag_here();
			                    ?>
                            </div>
		                    <?php
	                    }?>
                    </div>
                    <input type="hidden" name="pmb-project-front-matter-data" id="pmb-project-front-matter-data">
                    <input type="hidden" name="pmb-project-main-matter-data" id="pmb-project-main-matter-data">
                    <input type="hidden" name="pmb-project-back-matter-data" id="pmb-project-back-matter-data">
                    <input type="hidden" name="pmb-project-depth" id="pmb-project-depth">
                </div>
            </div>
        </div>
        <?php wp_nonce_field( 'pmb-project-edit' );?>
        <div class="pmb-button-spacer">
            <button class="button-primary button" id="pmb-save" name="pmb-save" value="save"><?php esc_html_e('Save', 'print-my-blog'); ?></button>
        </div>
    </form>

    <div
            style="display:none" id="pmb-add-print-materials-dialogue"
            title="<?php esc_html_e('Quick Add Print Material',
        'print-my-blog');?>"
    >
        <div class="pmb-add-print-materials-dialogue-content">
            <label><?php esc_html_e('Title', 'print-my-blog');?> <input type="text" name="title"
                                                                        id="pmb-print-material-title" ></label>
            <input type="hidden" name="project" value="<?php echo esc_attr($project->getWpPost()->ID);?>"
                   id="pmb-print-material-project">

        </div>
    </div>
    <form id="pmb-add-rint-materials-dialogue"></form>

<?php pmb_render_template('partials/project_footer.php');