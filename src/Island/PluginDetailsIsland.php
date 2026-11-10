<?php
declare(strict_types=1);

namespace App\Island;

use App\Model\Table\PackagesTable;
use Interact\Attribute\Action;
use Interact\Attribute\State;
use Interact\Island;

/**
 * Loads one public plugin on demand without refreshing the catalogue.
 */
class PluginDetailsIsland extends Island
{
    #[State]
    public ?int $packageId = null;

    /**
     * Choose the plugin to display through the signed island action transport.
     */
    #[Action]
    public function show(int $id): void
    {
        $this->packageId = $id;
    }

    /**
     * Keep entities request-local; the initial, closed modal performs no package query.
     *
     * @return array<string, mixed>
     */
    public function viewData(): array
    {
        $package = null;
        if ($this->packageId !== null) {
            $package = $this->fetchTable(PackagesTable::class)->get($this->packageId, contain: ['Tags', 'Categories']);
        }

        return compact('package');
    }
}
