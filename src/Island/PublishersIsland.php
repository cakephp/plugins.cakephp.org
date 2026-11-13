<?php
declare(strict_types=1);

namespace App\Island;

use App\Model\Table\PackagesTable;
use App\Service\GithubProfileService;
use Cake\Http\Exception\BadRequestException;
use Interact\Attribute\Action;
use Interact\Attribute\Pagination;
use Interact\Attribute\State;
use Interact\Island;
use Interact\Pagination\PaginationState;
use Interact\UpdatedPaths;

/**
 * Searches and ranks publishers without fetching external profile metadata.
 */
class PublishersIsland extends Island
{
    #[State(writable: true, url: true)]
    public string $search = '';

    #[State(writable: true, url: true, history: true)]
    public string $sort = 'monthly_downloads';

    #[State(writable: true, url: true, history: true)]
    public string $cakephp = 'current';

    #[Pagination(
        settings: [
            'limit' => 20,
            'maxLimit' => 20,
            'sortableFields' => [],
        ],
        url: ['page' => 'page'],
        history: true,
    )]
    public PaginationState $paging;

    /**
     * Only the pure avatar URL builder is needed; profile lookups remain on profile pages.
     */
    public function __construct(private readonly GithubProfileService $profiles)
    {
    }

    /**
     * Preserve explicit history paging while resetting pages after filter changes.
     */
    public function updated(UpdatedPaths $paths): void
    {
        if (($paths->has('search') || $paths->has('sort') || $paths->has('cakephp')) && !$paths->has('paging.page')) {
            $this->paging->page = 1;
        }
    }

    /**
     * Retain the selected popularity metric when clearing a search.
     */
    #[Action]
    public function clearSearch(): void
    {
        $this->search = '';
        $this->paging->page = 1;
    }

    /**
     * Aggregate once, then batch-load details for the visible page.
     *
     * @return array<string, mixed>
     */
    public function viewData(): array
    {
        if (mb_strlen($this->search) > 100) {
            throw new BadRequestException('Search query is too long.');
        }
        $sortOptions = [
            'monthly_downloads' => __('Monthly downloads'),
            'downloads' => __('Lifetime downloads'),
            'stars' => __('Stars'),
            'plugins' => __('Plugin count'),
        ];
        if (!isset($sortOptions[$this->sort])) {
            $this->sort = 'monthly_downloads';
        }
        $packages = $this->fetchTable(PackagesTable::class);
        $majors = $packages->publisherMajors();
        $current = $majors[0] ?? null;
        $versionOptions = [];
        if ($current !== null) {
            $versionOptions['current'] = __('CakePHP {0} (current)', $current);
            foreach ($majors as $major) {
                if ($major !== $current) {
                    $versionOptions[$major] = __('CakePHP {0}', $major);
                }
            }
        }
        $versionOptions['all'] = __('All versions');
        if (!isset($versionOptions[$this->cakephp])) {
            $this->cakephp = $current !== null ? 'current' : 'all';
        }
        $major = match ($this->cakephp) {
            'all' => null,
            'current' => $current,
            default => $this->cakephp,
        };
        $publishers = $this->paginate($packages->unhydratedFind('publishers', search: $this->search, sort: $this->sort, major: $major));
        $namespaces = [];
        foreach ($publishers as $publisher) {
            $namespaces[] = $publisher['namespace'];
        }
        $details = $packages->publisherDetails($namespaces, $this->sort, $major);
        foreach ($details as &$detail) {
            $detail['avatarUrl'] = $detail['githubUrl'] !== null
                ? $this->profiles->avatarUrl(basename($detail['githubUrl']), 96)
                : null;
        }
        unset($detail);

        return [
            'publishers' => $publishers,
            'details' => $details,
            'sortOptions' => $sortOptions,
            'versionOptions' => $versionOptions,
            'selectedMajor' => $major,
            'showRanks' => trim($this->search) === '',
            'rankStart' => ($this->paging->page - 1) * $this->paging->limit + 1,
        ];
    }
}
