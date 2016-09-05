<?php
namespace App\Model\Table\Finder;

use App\Exception\RedirectException;
use Cake\Log\Log;
use Cake\Network\Exception\NotFoundException;
use Cake\ORM\Query;
use InvalidArgumentException;

trait PackageViewFinderTrait
{
    /**
     * Returns a query scoped by Packages.id
     *
     * @param \Cake\ORM\Query $query The query to find with
     * @param array $options The options to use for the find
     * @return \Cake\ORM\Query The query builder
     */
    public function findView(Query $query, array $options)
    {
        $slug = $options['slug'];
        if (empty($options['package_id'])) {
            throw new InvalidArgumentException(__('Missing package_id argument'));
        }
        if (empty($options['slug'])) {
            throw new InvalidArgumentException(__('Missing slug argument'));
        }
        $query->find('package');
        $query->where([
            "{$this->alias()}.{$this->primaryKey()}" => $options['package_id'],
        ]);
        $query->limit(1);

        $query->formatResults(function ($results) use ($slug) {
            return $this->viewFinderRedirect($results, $slug);
        });

        return $query;
    }

    /**
     * Returns a query scoped by Maintainer.id
     *
     * @param \Cake\ORM\ResultSet $results The query to find with
     * @param string $slug Slug being checked against of the user
     * @return \Cake\ORM\Query The query builder
     * @throws \App\Exception\RedirectException
     */
    protected function viewFinderRedirect($results, $slug)
    {
        return $results->map(function ($row) use ($slug) {
            if ($slug != $row['name']) {
                $redirect = new RedirectException('Invalid package slug');
                $redirect->setRoute([
                    'plugin' => null,
                    'controller' => 'Packages',
                    'action' => 'view',
                    'id' => $row['id'],
                    'slug' => $row['name'],
                ]);
                throw $redirect;
            }

            return $row;
        });
    }
}
