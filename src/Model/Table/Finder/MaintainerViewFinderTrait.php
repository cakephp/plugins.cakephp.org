<?php
namespace App\Model\Table\Finder;

use App\Exception\RedirectException;
use Cake\Log\Log;
use Cake\Network\Exception\NotFoundException;
use Cake\ORM\Query;
use InvalidArgumentException;

trait MaintainerViewFinderTrait
{
    /**
     * Returns a query scoped by Maintainers.id
     *
     * @param \Cake\ORM\Query $query The query to find with
     * @param array $options The options to use for the find
     * @return \Cake\ORM\Query The query builder
     */
    public function findView(Query $query, array $options)
    {
        $slug = $options['slug'];
        if (empty($options['maintainer_id'])) {
            throw new InvalidArgumentException(__('Missing maintainer_id argument'));
        }
        if (empty($options['slug'])) {
            throw new InvalidArgumentException(__('Missing slug argument'));
        }

        $query->limit(1);
        $query->where([
            "{$this->alias()}.{$this->primaryKey()}" => $options['maintainer_id']
        ]);

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
            if ($slug != $row['username']) {
                $redirect = new RedirectException('Invalid package slug');
                $redirect->setRoute([
                    'plugin' => null,
                    'controller' => 'Maintainers',
                    'action' => 'view',
                    'id' => $row['id'],
                    'slug' => $row['username'],
                ]);
                throw $redirect;
            }

            return $row;
        });
    }
}
