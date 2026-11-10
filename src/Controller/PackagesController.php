<?php
declare(strict_types=1);

namespace App\Controller;

use App\Model\Table\PackagesTable;

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
        $this->Authentication->allowUnauthenticated(['index', 'view']);
    }

    /**
     * Render the catalogue island in the normal application layout.
     */
    public function index(): void
    {
    }

    /**
     * Provide a normal detail URL for direct links and browsers without JavaScript.
     */
    public function view(string $id): void
    {
        $package = $this->fetchTable(PackagesTable::class)->get($id, contain: ['Tags']);
        $this->set(compact('package'));
    }
}
