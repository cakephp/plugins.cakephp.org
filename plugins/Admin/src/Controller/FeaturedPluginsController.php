<?php
declare(strict_types=1);

namespace Admin\Controller;

use Cake\Routing\Attribute\Route;
use Cake\Routing\Attribute\Scope;

#[Scope('/admin', namePrefix: 'admin:')]
class FeaturedPluginsController extends AppController
{
    /**
     * The island owns editorial interactions.
     */
    #[Route('/featured-plugins', name: 'featured', methods: ['GET'])]
    public function index(): void
    {
    }
}
