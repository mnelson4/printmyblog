<?php // phpcs:ignore WordPress.Files.FileName
/**
 * Admin-Notices class.
 *
 * Handles creating Notices and printing them.
 *
 * @package   mnelson4/admin-notices
 * @author    mnelson4 <themes@wordpress.org>
 * @copyright 2019 mnelson4
 * @license   https://www.gnu.org/licenses/gpl-2.0.html GPL-2.0-or-later
 * @link      https://github.com/mnelson4/admin-notices
 */

namespace mnelson4\AdminNotices;

/**
 * The Admin_Notice class, responsible for creating admin notices.
 *
 * Each notice is a new instance of the object.
 *
 * @since 1.0.0
 */
class Notices
{

    /**
     * An array of notices.
     *
     * @access private
     * @since 1.0
     * @var array
     */
    private $notices = [];

    /**
     * Adds actions for the notices.
     *
     * @access public
     * @since 1.0
     * @return void
     */
    public function boot()
    {

        // Add the notice.
        add_action('admin_notices', [ $this, 'the_notices' ]);

        // Print the script to the footer.
        add_action('admin_enqueue_scripts', [ $this, 'enqueue_scripts' ]);

    }

    public function enqueue_scripts(){
        // Only enqueue the script if there's anything notices to show.
        $show = false;
        foreach($this->get_all() as $notice){
            if($notice->show()){
                $show = true;
            }
        }
        if($show){
            wp_enqueue_script(
                'wptrt-dismiss',
                MNELSON4_JS_URL . 'dismiss-notice.js',
                ['jquery','common'],
                filemtime(MNELSON4_JS_DIR . 'dismiss-notice.js'),
                true
            );
        }
    }

    /**
     * Add a notice.
     *
     * @access public
     * @since 1.0
     * @param string $id      A unique ID for this notice. Can contain lowercase characters and underscores.
     * @param string $title   The title for our notice.
     * @param string $message The message for our notice.
     * @param array  $options An array of additional options to change the defaults for this notice.
     *                        See Notice::__constructor() for details.
     * @return void
     */
    public function add($id, $title, $message, $options = [])
    {
        $this->notices[ $id ] = new Notice($id, $title, $message, $options);
    }

    /**
     * @param $id
     * @param Notice  $notice_obj
     */
    public function add_notice($notice_obj)
    {
        $this->notices[ $notice_obj->id() ] = $notice_obj;
    }

    /**
     * Remove a notice.
     *
     * @access public
     * @since 1.0
     * @param string $id The unique ID of the notice we want to remove.
     * @return void
     */
    public function remove($id)
    {
        unset($this->notices[ $id ]);
    }

    /**
     * Get a single notice.
     *
     * @access public
     * @since 1.0
     * @param string $id The unique ID of the notice we want to retrieve.
     * @return Notice|null
     */
    public function get($id)
    {
        if (isset($this->notices[ $id ])) {
            return $this->notices[ $id ];
        }
        return null;
    }

    /**
     * Get all notices.
     *
     * @access public
     * @since 1.0
     * @return Notice[]
     */
    public function get_all()
    {
        return $this->notices;
    }

    /**
     * Prints the notice.
     *
     * @access public
     * @since 1.0
     * @return void
     */
    public function the_notices()
    {
        $notices = $this->get_all();

        foreach ($notices as $notice) {
            $notice->the_notice();
        }
    }

    /**
     * Prints scripts for the notices.
     *
     * @access public
     * @since 1.0
     * @return void
     */
    public function print_scripts()
    {
        $notices = $this->get_all();

        foreach ($notices as $notice) {
            if ($notice->show()) {
                $notice->dismiss->print_script();
            }
        }
    }
}
