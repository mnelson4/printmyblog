<?php

namespace PrintMyBlog\domain;

/**
 * Class TempProNotification
 *
 * Temporary notification about Print My Blog Pro. This class will likely to be removed soon.
 *
 * @package     Print My Blog
 * @author         Mike Nelson
 * @since         $VID:$
 *
 */
class ProNotification
{
    const DISMISSED_OPTION_NAME = 'pmb_pro_notification_dismissed';

    /**
     * Sets up hooks related to the displaying and handling the pro invitation
     */
    public function setHooks()
    {
        $this->checkForSubmission();
        add_action('admin_notices',[$this,'maybeAddNotice']);
    }

    /**
     * Checks if the user submitted the pro signup invitation and taking action if they did.
     */
    public function checkForSubmission()
    {
        if(isset(
            $_POST['pmb_pro_notice_signup'],
            $_POST['name'],
            $_POST['email']
        )){
            $this->rememberDismissed('accepted');
            wp_remote_post(
                'https://blog.us19.list-manage.com/subscribe/post?u=5881790528ea076edfc10d859&id=32ccd044c3',
                [
                    'body' => [
                      'FNAME' => $_POST['name'],
                      'EMAIL' => $_POST['email']
                    ],
                    'blocking' => false,
                ]
            );
            wp_redirect(
                'https://printmy.blog/thanks/thanks-for-signing-up/'
            );
        }
        if(isset($_GET['pmb_pro_notice_dismiss'])){
            $this->rememberDismissed('dismiss');
        }
    }

    /**
     * @since $VID:$
     */
    public function maybeAddNotice()
    {
        global $pagenow;
        if($pagenow === 'index.php' && current_user_can(PMB_ADMIN_CAP) && ! $this->wasDismissed()){
            $this->addNotice();
        }

    }

    public function addNotice()
    {
        global $current_user;
        include(PMB_TEMPLATES_DIR . 'pro_notice.template.php');
    }

    public function wasDismissed()
    {
        return get_option(self::DISMISSED_OPTION_NAME,false);
    }

    public function rememberDismissed($choice){
        if(!$this->wasDismissed()){
            add_option(self::DISMISSED_OPTION_NAME, $choice,null,'no');
        }
    }
}
// End of file TempProNotification.php
// Location: PrintMyBlog\domain/TempProNotification.php
