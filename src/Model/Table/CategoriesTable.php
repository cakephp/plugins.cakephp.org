<?php
declare(strict_types=1);

namespace App\Model\Table;

use Cake\ORM\Table;

/**
 * Categories imported from Awesome CakePHP.
 *
 * @extends \Cake\ORM\Table<array{}, \App\Model\Entity\Category>
 * @property \Cake\ORM\Association\BelongsToMany<\App\Model\Table\PackagesTable> $Packages
 */
class CategoriesTable extends Table
{
    /**
     * @param array<string, mixed> $config Table configuration.
     */
    public function initialize(array $config): void
    {
        parent::initialize($config);
        $this->setDisplayField('name');
        $this->belongsToMany('Packages');
    }
}
