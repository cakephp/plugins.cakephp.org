<?php
declare(strict_types=1);

namespace App\Island;

use App\Model\Table\PackagesTable;
use Interact\Island;

/**
 * Global ecosystem figures remain independent of catalogue filters and paging.
 */
class EcosystemIsland extends Island
{
    /**
     * @return array<string, mixed>
     */
    public function viewData(): array
    {
        return [
            'stats' => $this->fetchTable(PackagesTable::class)->ecosystemStats(),
        ];
    }
}
