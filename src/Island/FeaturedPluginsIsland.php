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
        $logos = $this->featured();
        $names = array_values(array_intersect($this->order, array_keys($logos)));
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
            'featuredLogos' => $logos,
        ];
    }

    /**
     * Package names map to optional logo URLs; plain list entries use an emblem.
     *
     * @return array<string, string|null>
     */
    protected function featured(): array
    {
        $featured = [];
        foreach ((array)Configure::read('Packages.featured', []) as $name => $logo) {
            if (is_int($name)) {
                $name = $logo;
                $logo = null;
            }
            if (is_string($name)) {
                $featured[$name] = is_string($logo) && $logo !== '' ? $logo : null;
            }
        }

        return $featured;
    }
}
