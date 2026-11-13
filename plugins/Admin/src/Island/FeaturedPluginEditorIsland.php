<?php
declare(strict_types=1);

namespace Admin\Island;

use App\Model\Entity\FeaturedPlugin;
use App\Model\Table\FeaturedPluginsTable;
use App\Model\Table\PackagesTable;
use Cake\Http\Exception\BadRequestException;
use Interact\Attribute\Action;
use Interact\Attribute\Prop;
use Interact\Attribute\State;

/**
 * Owns the editorial draft and preview independently of the paginated listing.
 */
class FeaturedPluginEditorIsland extends AdminIsland
{
    #[Prop]
    public ?int $entryId = null;

    #[State(writable: true)]
    public string $packageSearch = '';

    #[State]
    public string $package = '';

    /**
     * @var array<string, mixed>
     */
    #[State(writable: true)]
    public array $draft = [
        'enabled' => true,
        'logo' => '',
        'title' => '',
        'description' => '',
    ];

    protected ?FeaturedPlugin $entry = null;

    /**
     * Initialize the draft after authorization; later requests preserve edits.
     */
    public function mount(): void
    {
        if ($this->entryId === null) {
            return;
        }
        $entry = $this->fetchTable(FeaturedPluginsTable::class)->get($this->entryId);
        $this->package = $entry->package;
        $this->draft = [
            'enabled' => $entry->enabled,
            'logo' => $entry->logo ?? '',
            'title' => $entry->title ?? '',
            'description' => $entry->description ?? '',
        ];
    }

    /**
     * Only new entries can select a different canonical catalogue package.
     */
    #[Action]
    public function selectPackage(int $id): void
    {
        if ($this->entryId !== null) {
            throw new BadRequestException('Existing featured entries cannot change package.');
        }
        $this->package = $this->fetchTable(PackagesTable::class)->get($id)->package;
        $this->packageSearch = '';
    }

    /**
     * Delegate normalization, validation and creation rules to the model.
     */
    #[Action]
    public function save(): void
    {
        $table = $this->fetchTable(FeaturedPluginsTable::class);
        $entry = $this->entryId !== null ? $table->get($this->entryId) : $table->newEmptyEntity();
        $this->entry = $table->patchEntity($entry, [
            ...$this->draft,
            'package' => $this->package,
        ], [
            'fields' => ['package', 'enabled', 'logo', 'title', 'description'],
        ]);
        if (!$table->save($this->entry)) {
            $this->setErrors($this->entry->getErrors() ?: ['_form' => ['Unable to save this featured plugin.']]);

            return;
        }
        $this->dispatch('featured-plugin-saved');
    }

    /**
     * Close the parent-owned modal without persisting the draft.
     */
    #[Action(render: false)]
    public function cancel(): void
    {
        $this->dispatch('featured-plugin-editor-closed');
    }

    /**
     * Preview updates never execute the featured listing query.
     *
     * @return array<string, mixed>
     */
    public function viewData(): array
    {
        if (mb_strlen($this->packageSearch) > 100) {
            throw new BadRequestException('Package search is too long.');
        }
        $packages = $this->fetchTable(PackagesTable::class);
        $table = $this->fetchTable(FeaturedPluginsTable::class);

        return [
            'entry' => $this->entry ?? $table->newEntity([
                ...$this->draft,
                'package' => $this->package,
            ], [
                'fields' => ['package', 'enabled', 'logo', 'title', 'description'],
            ]),
            'matches' => $this->entryId === null && $this->package === ''
                ? $packages->find('featuredCandidates', search: $this->packageSearch)->limit(10)->all()
                : [],
            'previewPackage' => $this->package !== '' ? $packages->find()->where(['package' => $this->package])->first() : null,
        ];
    }
}
