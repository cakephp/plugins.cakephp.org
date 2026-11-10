<?php
declare(strict_types=1);

namespace App\Island;

use App\Model\Table\PackagesTable;
use Cake\Http\Exception\BadRequestException;
use Interact\Attribute\Action;
use Interact\Attribute\State;
use Interact\Island;

/**
 * Renders server-owned suggestions independently of catalogue state.
 * Submitting visits the catalogue through Interact's normal page navigation.
 */
class PackageSearchIsland extends Island
{
    #[State(writable: true)]
    public string $query = '';

    /**
     * Initialize the draft from the page's applied search.
     */
    public function mount(): void
    {
        $this->query = (string)$this->getRequest()->getQuery('search', '');
    }

    /**
     * Submit a search while preserving the browser's currently applied filters.
     *
     * @param array<string, mixed> $filters Current URL version filters.
     */
    #[Action]
    public function submit(array $filters = []): void
    {
        $options = $this->fetchTable(PackagesTable::class)->filterOptions();
        $query = array_filter([
            'search' => trim($this->query),
            'cakephp_slugs' => array_values(array_intersect(array_filter((array)($filters['cakephp_slugs'] ?? []), is_string(...)), array_keys($options['cakephp']))),
            'php_slugs' => array_values(array_intersect(array_filter((array)($filters['php_slugs'] ?? []), is_string(...)), array_keys($options['php']))),
        ], static fn($value): bool => $value !== '' && $value !== []);
        $this->redirect(['controller' => 'Packages', 'action' => 'index', 'plugin' => null, '?' => $query], navigate: true);
    }

    /**
     * Render a bounded suggestion list without serializing entities as state.
     *
     * @return array<string, mixed>
     */
    public function viewData(): array
    {
        if (mb_strlen($this->query) > 100) {
            throw new BadRequestException('Search query is too long.');
        }
        $search = trim($this->query);

        return [
            'suggestions' => mb_strlen($search) < 2 ? [] : $this->fetchTable(PackagesTable::class)->find('autocomplete', search: $search)->all(),
        ];
    }
}
