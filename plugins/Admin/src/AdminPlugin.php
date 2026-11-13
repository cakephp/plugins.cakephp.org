<?php
declare(strict_types=1);

namespace Admin;

use Cake\Core\BasePlugin;
use Cake\Routing\RouteBuilder;

/**
 * App-specific administration, using the host's identity and domain services.
 */
class AdminPlugin extends BasePlugin
{
    /**
     * Only declared endpoints are reachable; there are no plugin fallbacks.
     */
    public function routes(RouteBuilder $routes): void
    {
        $routes->plugin('Admin', ['path' => '/admin'], function (RouteBuilder $builder): void {
            $builder->get('/', [
                'controller' => 'Users',
                'action' => 'home',
            ], 'admin:home');
            $builder->get('/users', [
                'controller' => 'Users',
                'action' => 'index',
            ], 'admin:users');
            $builder->get('/featured-plugins', [
                'controller' => 'FeaturedPlugins',
                'action' => 'index',
            ], 'admin:featured');
        });
    }
}
