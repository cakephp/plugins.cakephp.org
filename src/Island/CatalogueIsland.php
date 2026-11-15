<?php
declare(strict_types=1);

namespace App\Island;

use App\Model\Table\CategoriesTable;
use App\Model\Table\PackagesTable;
use App\Utility\CakephpVersion;
use App\Utility\FilterSelection;
use Cake\Http\Exception\BadRequestException;
use Interact\Attribute\Action;
use Interact\Attribute\Pagination;
use Interact\Attribute\Prop;
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
    #[Prop]
    public ?string $namespace = null;

    #[State(writable: true, url: true)]
    public string $search = '';

    #[State(writable: true, url: true, history: true)]
    public string $category = '';

    #[State(writable: true, url: 'awesome', history: true)]
    public bool $awesomeOnly = false;

    #[State(writable: true, url: 'featured', history: true)]
    public bool $featuredOnly = false;

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

    /**
     * @var list<string>
     */
    #[State(writable: true, url: 'exclude_cakephp_slugs', history: true)]
    public array $excludedCakephpSlugs = [];

    /**
     * @var list<string>
     */
    #[State(writable: true, url: 'exclude_php_slugs', history: true)]
    public array $excludedPhpSlugs = [];

    /**
     * @var list<string>
     */
    #[State(writable: true, url: 'exclude_categories', history: true)]
    public array $excludedCategories = [];

    #[Pagination(
        settings: [
            'limit' => 21,
            'sortableFields' => ['downloads', 'monthly_downloads', 'stars', 'latest_stable_release_date'],
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
        $filtersChanged = $paths->hasAny(
            'search',
            'cakephpSlugs',
            'phpSlugs',
            'excludedCakephpSlugs',
            'excludedPhpSlugs',
            'excludedCategories',
            'category',
            'awesomeOnly',
            'featuredOnly',
        );
        if ($filtersChanged && !$paths->has('paging.page')) {
            $this->paging->page = 1;
        }
    }

    /**
     * Cycle a version through neutral, included and excluded.
     */
    #[Action]
    public function toggleVersion(string $group, string $slug): void
    {
        [$property, $excludedProperty] = $this->versionProperties($group);
        $selection = FilterSelection::cycle($this->{$property}, $this->{$excludedProperty}, $slug);
        $this->{$property} = $selection['included'];
        $this->{$excludedProperty} = $selection['excluded'];
        $this->paging->page = 1;
    }

    /**
     * Clear both inclusion and exclusion for one version family.
     */
    #[Action]
    public function clearVersions(string $group): void
    {
        [$property, $excludedProperty] = $this->versionProperties($group);
        $this->{$property} = [];
        $this->{$excludedProperty} = [];
        $this->paging->page = 1;
    }

    /**
     * @return array{string, string}
     */
    private function versionProperties(string $group): array
    {
        return match ($group) {
            'cakephp_slugs' => ['cakephpSlugs', 'excludedCakephpSlugs'],
            'php_slugs' => ['phpSlugs', 'excludedPhpSlugs'],
            default => throw new BadRequestException('Unsupported version group.'),
        };
    }

    /**
     * Select a compatibility version from plugin details without losing other filters.
     */
    #[Action]
    public function selectVersion(string $group, string $slug): void
    {
        [$property, $excludedProperty] = $this->versionProperties($group);
        $this->{$property} = [$slug];
        $this->{$excludedProperty} = array_values(array_diff($this->{$excludedProperty}, [$slug]));
        $this->paging->page = 1;
    }

    /**
     * Choose one category from the directory or plugin details.
     */
    #[Action]
    public function selectCategory(string $slug): void
    {
        $this->category = $slug;
        $this->excludedCategories = $slug === '' ? [] : array_values(array_diff($this->excludedCategories, [$slug]));
        $this->paging->page = 1;
    }

    /**
     * Keep one included category while allowing multiple exclusions.
     */
    #[Action]
    public function toggleCategory(string $slug): void
    {
        $selection = FilterSelection::cycle($this->category !== '' ? [$this->category] : [], $this->excludedCategories, $slug, multiple: false);
        $this->category = $selection['included'][0] ?? '';
        $this->excludedCategories = $selection['excluded'];
        $this->paging->page = 1;
    }

    /**
     * Toggle community-curated membership without changing other filters.
     */
    #[Action]
    public function toggleAwesome(): void
    {
        $this->awesomeOnly = !$this->awesomeOnly;
        $this->paging->page = 1;
    }

    /**
     * Keep community picks within the current search, compatibility and sort.
     */
    #[Action]
    public function toggleFeatured(): void
    {
        $this->featuredOnly = !$this->featuredOnly;
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
        $this->excludedCakephpSlugs = [];
        $this->excludedPhpSlugs = [];
        $this->excludedCategories = [];
        $this->category = '';
        $this->awesomeOnly = false;
        $this->featuredOnly = false;
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
        $this->excludedCakephpSlugs = $this->acceptedSlugs($this->excludedCakephpSlugs, $options['cakephp']);
        $this->excludedPhpSlugs = $this->acceptedSlugs($this->excludedPhpSlugs, $options['php']);
        $this->cakephpSlugs = array_values(array_diff($this->acceptedSlugs($this->cakephpSlugs, $options['cakephp']), $this->excludedCakephpSlugs));
        $this->phpSlugs = array_values(array_diff($this->acceptedSlugs($this->phpSlugs, $options['php']), $this->excludedPhpSlugs));
        $includeDevelopment = array_any($this->cakephpSlugs, static fn(mixed $slug): bool => is_string($slug) && CakephpVersion::isDevelopmentSlug($slug));
        $categoryOptions = $this->fetchTable(CategoriesTable::class)->unhydratedFind('catalogueOptions', namespace: $this->namespace, includeDevelopment: $includeDevelopment)->all()->toArray();
        $this->excludedCategories = $this->acceptedSlugs($this->excludedCategories, $categoryOptions);
        if (!array_key_exists($this->category, $categoryOptions) || in_array($this->category, $this->excludedCategories, true)) {
            $this->category = '';
        }
        $query = [
            'search' => $this->search,
            'cakephp_slugs' => $this->cakephpSlugs,
            'php_slugs' => $this->phpSlugs,
            'exclude_cakephp_slugs' => $this->excludedCakephpSlugs,
            'exclude_php_slugs' => $this->excludedPhpSlugs,
            'exclude_categories' => $this->excludedCategories,
            'category' => $this->category,
            'awesome' => $this->awesomeOnly,
            'featured' => $this->featuredOnly,
            'sort' => $this->paging->sort,
            'direction' => $this->paging->direction,
        ];
        $hasFilters = trim($this->search) !== '' || $this->cakephpSlugs !== [] || $this->phpSlugs !== []
            || $this->excludedCakephpSlugs !== [] || $this->excludedPhpSlugs !== [] || $this->excludedCategories !== []
            || $this->category !== '' || $this->awesomeOnly || $this->featuredOnly;
        $packages = $this->paginate($this->packages->find('catalogue', filters: $query, namespace: $this->namespace));

        return [
            'packages' => $packages,
            'hasFilters' => $hasFilters,
            'cakephpTags' => $options['cakephp'],
            'phpTags' => $options['php'],
            'catalogueQuery' => $query,
            'categoryOptions' => $categoryOptions,
            'catalogueRoute' => $this->namespace !== null ? [
                '_name' => 'plugin:namespace',
                'namespace' => $this->namespace,
            ] : [
                '_name' => 'plugin:index',
            ],
        ];
    }

    /**
     * @param array<mixed> $values Untrusted URL or client selections.
     * @param array<array-key, mixed> $options Available choices.
     * @return list<string>
     */
    private function acceptedSlugs(array $values, array $options): array
    {
        return array_values(array_unique(array_intersect(array_filter($values, is_string(...)), array_keys($options))));
    }
}
