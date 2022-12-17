<?php


namespace PrintMyBlog\compatibility\plugins;


use Twine\compatibility\CompatibilityBase;

class PaidMembershipsPro extends CompatibilityBase
{
    /**
     * Sets hooks to modify a PMB request
     */
    public function setRenderingHooks()
    {
        // If they added the post to a project, show it in the project plz.
        add_filter(
            'pmpro_has_membership_access_filter',
        '__return_true'
        );
    }
}