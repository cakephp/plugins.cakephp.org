<?php
namespace App\Controller\Admin;

use App\Controller\AppController as BaseAppController;

class AppController extends BaseAppController
{
    /**
     * Whether or not to treat a controller as
     * if it were an admin controller or not.
     *
     * Used to turn CrudView on and off at a class-level
     *
     * @var bool
     */
    protected $isAdmin = true;

    /**
     * A list of actions that should be allowed for
     * authenticated users
     *
     * @var array
     */
    protected $allowedActions = [
        'index',
    ];

    /**
     * Retrieves the navigation elements for the page
     *
     * @return array
     */
    protected function getUtilityNavigation()
    {
        if ($this->Auth->user('id') === null) {
            return [
                new \CrudView\Menu\MenuItem(
                    'Forgot Password?',
                    ['prefix' => false, 'plugin' => 'Users', 'controller' => 'Users', 'action' => 'forgotPassword']
                ),
                new \CrudView\Menu\MenuItem(
                    'Login',
                    ['prefix' => false, 'plugin' => 'Users', 'controller' => 'Users', 'action' => 'login']
                ),
            ];
        }
        return [
            new \CrudView\Menu\MenuItem(
                'Categories',
                ['prefix' => 'admin', 'plugin' => null, 'controller' => 'Categories', 'action' => 'index']
            ),
            new \CrudView\Menu\MenuItem(
                'Maintainers',
                ['prefix' => 'admin', 'plugin' => null, 'controller' => 'Maintainers', 'action' => 'index']
            ),
            new \CrudView\Menu\MenuItem(
                'Packages',
                ['prefix' => 'admin', 'plugin' => null, 'controller' => 'Packages', 'action' => 'index']
            ),
            new \CrudView\Menu\MenuItem(
                'Users',
                ['prefix' => false, 'plugin' => 'Users', 'controller' => 'Users', 'action' => 'index']
            ),
            new \CrudView\Menu\MenuItem(
                'Profile',
                ['prefix' => false, 'plugin' => 'Users', 'controller' => 'Users', 'action' => 'edit']
            ),
            new \CrudView\Menu\MenuItem(
                'Log Out',
                ['prefix' => false, 'plugin' => 'Users', 'controller' => 'Users', 'action' => 'logout']
            )
        ];
    }
}
