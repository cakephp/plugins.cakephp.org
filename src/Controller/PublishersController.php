<?php
declare(strict_types=1);

namespace App\Controller;

use Cake\Routing\Attribute\Route;

/**
 * Hosts the public publisher directory.
 */
class PublishersController extends AppController
{
    /**
     * Publisher statistics are public catalogue information.
     */
    public function initialize(): void
    {
        parent::initialize();
        $this->Search->setConfig('actions', [], false);
        $this->Authentication->allowUnauthenticated(['index']);
    }

    /**
     * Render the directory island in the shared application layout.
     */
    #[Route('/publishers', name: 'publisher:index', methods: ['GET'])]
    public function index(): void
    {
    }
}
