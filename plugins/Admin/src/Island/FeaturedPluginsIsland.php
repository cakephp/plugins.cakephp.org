<?php
declare(strict_types=1);

namespace Admin\Island;

use App\Model\Enum\FeaturedPluginStatus;
use App\Model\Table\FeaturedPluginsTable;
use App\Model\Table\PackagesTable;
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
            'order' => ['FeaturedPlugins.package' => 'asc', 'FeaturedPlugins.id' => 'asc'],
            'sortableFields' => [
                'package' => 'FeaturedPlugins.package',
                'title' => 'FeaturedPlugins.title',
                'stars' => 'CataloguePackages.stars',
                'downloads' => 'CataloguePackages.downloads',
                'enabled' => 'FeaturedPlugins.enabled',
            ],
        ],
        url: ['page' => 'page', 'sort' => 'sort', 'direction' => 'direction'],
        defaults: ['sort' => 'package', 'direction' => 'asc'],
    )]
    public PaginationState $paging;

    #[State]
    public bool $editing = false;

    #[State]
    public ?int $editingId = null;

    #[State(writable: true)]
    public string $packageSearch = '';

    #[State]
    public string $package = '';

    #[State(writable: true)]
    public bool $enabled = true;

    #[State(writable: true)]
    public string $logo = '';

    #[State(writable: true)]
    public string $title = '';

    #[State(writable: true)]
    public string $description = '';

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
     * Start an empty editorial draft.
     */
    #[Action]
    public function create(): void
    {
        $this->editing = true;
        $this->editingId = null;
        $this->packageSearch = $this->package = $this->logo = $this->title = $this->description = $this->message = '';
        $this->enabled = true;
    }

    /**
     * Only existing catalogue entries may be selected for a new feature.
     */
    #[Action]
    public function selectPackage(int $id): void
    {
        if ($this->editingId !== null || !$this->editing) {
            throw new BadRequestException('Start a new featured entry first.');
        }
        $this->package = $this->fetchTable(PackagesTable::class)->get($id)->package;
        $this->packageSearch = '';
    }

    /**
     * Load an existing entry, including entries missing from the catalogue.
     */
    #[Action]
    public function edit(int $id): void
    {
        $entry = $this->fetchTable(FeaturedPluginsTable::class)->get($id);
        $this->editing = true;
        $this->editingId = $id;
        $this->package = $entry->package;
        $this->enabled = $entry->enabled;
        $this->logo = $entry->logo ?? '';
        $this->title = $entry->title ?? '';
        $this->description = $entry->description ?? '';
        $this->packageSearch = $this->message = '';
    }

    /**
     * Validate and save plain-text overrides without changing imported metadata.
     */
    #[Action]
    public function save(): void
    {
        if (!$this->editing) {
            throw new BadRequestException('Open the featured editor first.');
        }
        $table = $this->fetchTable(FeaturedPluginsTable::class);
        if ($this->editingId === null && !$this->fetchTable(PackagesTable::class)->exists(['package' => $this->package])) {
            $this->setErrors(['package' => ['Choose an existing plugin.']]);

            return;
        }
        $entry = $this->editingId !== null ? $table->get($this->editingId) : $table->newEmptyEntity();
        $entry = $table->patchEntity($entry, [
            'package' => $this->package,
            'enabled' => $this->enabled,
            'logo' => trim($this->logo) === '' ? null : trim($this->logo),
            'title' => trim($this->title) === '' ? null : trim($this->title),
            'description' => trim($this->description) === '' ? null : trim($this->description),
        ]);
        if (!$table->save($entry)) {
            $this->setErrors($entry->getErrors() ?: ['_form' => ['Unable to save this featured plugin.']]);

            return;
        }
        $this->cancel();
        $this->message = 'Featured plugin saved.';
    }

    /**
     * Discard the draft and any removal confirmation.
     */
    #[Action]
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
        if (mb_strlen($this->search) > 100 || mb_strlen($this->packageSearch) > 100 || ($this->status !== '' && $status === null)) {
            throw new BadRequestException('Invalid featured plugin filters.');
        }
        $table = $this->fetchTable(FeaturedPluginsTable::class);
        $query = $table->find()->contain(['CataloguePackages']);
        if (trim($this->search) !== '') {
            $query->where(['FeaturedPlugins.package LIKE' => '%' . trim($this->search) . '%']);
        }
        if ($status === FeaturedPluginStatus::Unavailable) {
            $query->where(['CataloguePackages.id IS' => null]);
        } elseif ($status !== null) {
            $query->where(['FeaturedPlugins.enabled' => $status === FeaturedPluginStatus::Enabled]);
        }
        $packages = $this->fetchTable(PackagesTable::class);
        $matches = [];
        if ($this->editing && $this->editingId === null && $this->package === '') {
            $matches = $packages->find()->select(['id', 'package'])->where(['package LIKE' => '%' . trim($this->packageSearch) . '%'])
                ->orderByAsc('package')->limit(10)->all();
        }

        return [
            'entries' => $this->paginate($query),
            'matches' => $matches,
            'previewLogo' => $this->logo !== '' && $table->getValidator()->validate(['logo' => $this->logo], false) === [] ? $this->logo : null,
            'previewPackage' => $this->editing && $this->package !== '' ? $packages->find()->where(['package' => $this->package])->first() : null,
            'removingEntry' => $this->removingId !== null ? $table->get($this->removingId) : null,
        ];
    }
}
