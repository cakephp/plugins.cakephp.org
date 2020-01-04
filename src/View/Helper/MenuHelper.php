<?php
namespace App\View\Helper;

use Cake\Core\Configure;
use Cake\View\Helper;

/**
 * Menu Helper
 */
class MenuHelper extends Helper
{
    public $helpers = ['Html'];

    /**
     * List of options related to community menu option
     *
     * @return array
     */
    public function communityItems()
    {
        $items = Configure::read('Site.menu.items.community');
        $items = $items ?: [];

        return $items;
    }

    /**
     * List of options related to help and support menu option
     *
     * @return array
     */
    public function helpAndSupportItems()
    {
        $items = Configure::read('Site.menu.items.help');
        $items = $items ?: [];

        return $items;
    }

    /**
     * List of options related to jobs menu option
     *
     * @return array
     */
    public function jobsItems()
    {
        $items = Configure::read('Site.menu.items.jobs');
        $items = $items ?: [];

        return $items;
    }

    /**
     * List of options related to documentation menu option
     *
     * @return array
     */
    public function documentationItems()
    {
        $items = Configure::read('Site.menu.items.documentation');
        $items = $items ?: [];

        return $items;
    }

    /**
     * List of options related to service providers menu option
     *
     * @return array
     */
    public function serviceProvidersItems()
    {
        $items = Configure::read('Site.menu.items.serviceProvider');
        $items = $items ?: [];

        return $items;
    }

    /**
     * List of options related to calendar menu option
     *
     * @return array
     */
    public function calendarItems()
    {
        $items = Configure::read('Site.menu.items.calendar');
        $items = $items ?: [];

        return $items;
    }
}
