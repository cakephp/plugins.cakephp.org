<?php
declare(strict_types=1);

namespace App\Controller;

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
    public function index(): void
    {
    }
}
