<?php
declare(strict_types=1);

namespace App\Controller;

/**
 * Hosts the public catalogue; islands own subsequent interactions.
 */
class PackagesController extends AppController
{
    /**
     * Allow anonymous catalogue visits.
     */
    public function initialize(): void
    {
        parent::initialize();
        $this->Authentication->allowUnauthenticated(['index']);
    }

    /**
     * Render the catalogue island in the normal application layout.
     */
    public function index(): void
    {
    }
}
