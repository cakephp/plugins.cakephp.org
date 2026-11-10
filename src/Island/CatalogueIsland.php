<?php
declare(strict_types=1);

namespace App\Island;

use App\Model\Table\PackagesTable;
use Cake\Http\Exception\BadRequestException;
use Interact\Attribute\Action;
use Interact\Attribute\Pagination;
use Interact\Attribute\State;
use Interact\Island;
use Interact\Pagination\PaginationState;
use Interact\UpdatedPaths;

/**
 * Owns the public catalogue's accepted filters and paging.
 * Queries and entities remain request-local; only selections enter snapshots.
 */
class CatalogueIsland extends Island
{
    #[State(writable: true, url: true)]
    public string $search = '';

    /**
     * @var list<string>
     */
    #[State(writable: true, url: 'cakephp_slugs', history: true)]
    public array $cakephpSlugs = [];

    /**
     * @var list<string>
     */
    #[State(writable: true, url: 'php_slugs', history: true)]
    public array $phpSlugs = [];

    #[Pagination(
        settings: [
            'limit' => 21,
            'sortableFields' => ['downloads', 'stars', 'latest_stable_release_date'],
            'order' => ['Packages.latest_stable_release_date' => 'desc', 'Packages.id' => 'desc'],
        ],
        url: ['page' => 'page', 'sort' => 'sort', 'direction' => 'direction'],
        history: true,
        defaults: ['sort' => 'latest_stable_release_date', 'direction' => 'desc'],
    )]
    public PaginationState $paging;

    protected PackagesTable $packages;

    /**
     * Resolve request-local collaborators without running queries.
     */
    public function initialize(): void
    {
        $this->packages = $this->fetchTable(PackagesTable::class);
    }

    /**
     * Reset paging for filter edits while respecting history's explicit page.
     */
    public function updated(UpdatedPaths $paths): void
    {
        if (($paths->has('search') || $paths->has('cakephpSlugs') || $paths->has('phpSlugs')) && !$paths->has('paging.page')) {
            $this->paging->page = 1;
        }
    }

    /**
     * Toggle a version selected from the directory filters.
     */
    #[Action]
    public function toggleVersion(string $group, string $slug): void
    {
        if (!in_array($group, ['cakephp_slugs', 'php_slugs'], true)) {
            throw new BadRequestException('Unsupported version group.');
        }
        $property = $group === 'cakephp_slugs' ? 'cakephpSlugs' : 'phpSlugs';
        $values = $this->{$property};
        $values = in_array($slug, $values, true)
            ? array_values(array_diff($values, [$slug]))
            : [...$values, $slug];
        $this->{$property} = $values;
        $this->paging->page = 1;
    }

    /**
     * Select a compatibility version from plugin details without losing other filters.
     */
    #[Action]
    public function selectVersion(string $group, string $slug): void
    {
        if (!in_array($group, ['cakephp_slugs', 'php_slugs'], true)) {
            throw new BadRequestException('Unsupported version group.');
        }
        $property = $group === 'cakephp_slugs' ? 'cakephpSlugs' : 'phpSlugs';
        $this->{$property} = [$slug];
        $this->paging->page = 1;
    }

    /**
     * Clear the search and version selections while retaining the current sort.
     */
    #[Action]
    public function clearFilters(): void
    {
        $this->search = '';
        $this->cakephpSlugs = [];
        $this->phpSlugs = [];
        $this->paging->page = 1;
    }

    /**
     * Query the catalogue from validated state on initial and update requests.
     *
     * @return array<string, mixed>
     */
    public function viewData(): array
    {
        if (mb_strlen($this->search) > 100) {
            throw new BadRequestException('Search query is too long.');
        }
        $options = $this->packages->filterOptions();
        $this->cakephpSlugs = array_values(array_intersect(array_filter($this->cakephpSlugs, is_string(...)), array_keys($options['cakephp'])));
        $this->phpSlugs = array_values(array_intersect(array_filter($this->phpSlugs, is_string(...)), array_keys($options['php'])));
        $query = [
            'search' => $this->search,
            'cakephp_slugs' => $this->cakephpSlugs,
            'php_slugs' => $this->phpSlugs,
            'sort' => $this->paging->sort,
            'direction' => $this->paging->direction,
        ];
        $hasFilters = trim($this->search) !== '' || $this->cakephpSlugs !== [] || $this->phpSlugs !== [];
        $showFeatured = !$hasFilters && $this->paging->page === 1;
        $packages = $this->paginate($this->packages->find('catalogue', filters: $query));

        return [
            'packages' => $packages,
            'showFeatured' => $showFeatured,
            'hasFilters' => $hasFilters,
            'cakephpTags' => $options['cakephp'],
            'phpTags' => $options['php'],
            'catalogueQuery' => $query,
        ];
    }
}
