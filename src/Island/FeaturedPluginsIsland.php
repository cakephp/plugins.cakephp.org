<?php
declare(strict_types=1);

namespace App\Island;

use App\Model\Table\PackagesTable;
use Cake\Core\Configure;
use Interact\Attribute\State;
use Interact\Island;

/**
 * Owns the featured selection independently of catalogue sorting and filtering.
 */
class FeaturedPluginsIsland extends Island
{
    /**
     * @var list<string>
     */
    #[State]
    public array $order = [];

    /**
     * Shuffle once per mount; subsequent updates retain the selection's order.
     */
    public function mount(): void
    {
        $this->order = array_keys($this->featured());
        shuffle($this->order);
    }

    /**
     * Load the configured packages in their saved order without serializing entities.
     *
     * @return array<string, mixed>
     */
    public function viewData(): array
    {
        $metadata = $this->featured();
        $names = array_values(array_intersect($this->order, array_keys($metadata)));
        $packages = [];
        if ($names !== []) {
            $featured = $this->fetchTable(PackagesTable::class)->find('featured', names: $names)->all()->indexBy('package')->toArray();
            foreach ($names as $name) {
                if (isset($featured[$name])) {
                    $packages[] = $featured[$name];
                    if (count($packages) === 3) {
                        break;
                    }
                }
            }
        }

        return [
            'featuredPackages' => $packages,
            'featuredMetadata' => $metadata,
        ];
    }

    /**
     * Read featured metadata keyed by package name.
     *
     * @return array<string, array{logo: string|null}>
     */
    protected function featured(): array
    {
        return (array)Configure::read('Packages.featured', []);
    }
}
