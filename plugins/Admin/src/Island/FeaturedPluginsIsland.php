<?php
declare(strict_types=1);

namespace Admin\Island;

use App\Model\Enum\FeaturedPluginStatus;
use App\Model\Table\FeaturedPluginsTable;
use Cake\Http\Exception\BadRequestException;
use Interact\Attribute\Action;
use Interact\Attribute\Pagination;
use Interact\Attribute\State;
use Interact\Pagination\PaginationState;
use Interact\UpdatedPaths;

class FeaturedPluginsIsland extends AdminIsland
{
    #[State(writable: true, url: true)]
    public string $search = '';

    #[State(writable: true, url: true)]
    public string $status = '';

    #[Pagination(
        settings: [
            'limit' => 20,
            'order' => [
                'FeaturedPlugins.package' => 'asc',
                'FeaturedPlugins.id' => 'asc',
            ],
            'sortableFields' => [
                'package' => 'FeaturedPlugins.package',
                'title' => 'FeaturedPlugins.title',
                'stars' => 'CataloguePackages.stars',
                'downloads' => 'CataloguePackages.downloads',
                'enabled' => 'FeaturedPlugins.enabled',
            ],
        ],
        url: [
            'page' => 'page',
            'sort' => 'sort',
            'direction' => 'direction',
        ],
        defaults: [
            'sort' => 'package',
            'direction' => 'asc',
        ],
    )]
    public PaginationState $paging;

    #[State]
    public bool $editing = false;

    #[State]
    public ?int $editingId = null;

    #[State]
    public ?int $removingId = null;

    #[State]
    public string $message = '';

    /**
     * Restart paging when editing list filters.
     */
    public function updated(UpdatedPaths $paths): void
    {
        if ($paths->has('search') || $paths->has('status')) {
            $this->paging->page = 1;
        }
    }

    /**
     * Mount a fresh child editor for a new feature.
     */
    #[Action]
    public function create(): void
    {
        $this->editing = true;
        $this->editingId = $this->removingId = null;
        $this->message = '';
    }

    /**
     * Key the editor by record so each entry receives a fresh draft.
     */
    #[Action]
    public function edit(int $id): void
    {
        $this->editing = true;
        $this->editingId = $id;
        $this->removingId = null;
        $this->message = '';
    }

    /**
     * Refresh the list only after a successful editor save.
     */
    #[Action(on: 'featured-plugin-saved')]
    public function saved(): void
    {
        $this->cancel();
        $this->message = 'Featured plugin saved.';
    }

    /**
     * Discard the draft and any removal confirmation.
     */
    #[Action(on: 'featured-plugin-editor-closed')]
    public function cancel(): void
    {
        $this->editing = false;
        $this->editingId = $this->removingId = null;
    }

    /**
     * Load the entry before offering a removal confirmation.
     */
    #[Action]
    public function confirmRemoval(int $id): void
    {
        $this->fetchTable(FeaturedPluginsTable::class)->get($id);
        $this->removingId = $id;
        $this->editing = false;
        $this->message = '';
    }

    /**
     * Remove only the confirmed editorial entry, never its package.
     */
    #[Action]
    public function remove(): void
    {
        if ($this->removingId === null) {
            throw new BadRequestException('Confirm removal first.');
        }
        $table = $this->fetchTable(FeaturedPluginsTable::class);
        $table->deleteOrFail($table->get($this->removingId));
        $this->cancel();
        $this->message = 'Featured plugin removed.';
    }

    /**
     * @return array<string, mixed>
     */
    public function viewData(): array
    {
        $status = FeaturedPluginStatus::tryFrom($this->status);
        if (mb_strlen($this->search) > 100 || ($this->status !== '' && $status === null)) {
            throw new BadRequestException('Invalid featured plugin filters.');
        }
        $table = $this->fetchTable(FeaturedPluginsTable::class);
        $query = $table->find('adminListing', search: $this->search, status: $status);

        return [
            'entries' => $this->paginate($query),
            'removingEntry' => $this->removingId !== null ? $table->get($this->removingId) : null,
        ];
    }
}
