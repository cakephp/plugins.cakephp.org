<?php
namespace Users\Model\Table\Traits;

use Cake\Core\Configure;
use Cake\Event\Event;

trait TokenTrait
{
    /**
     * Find user based on token
     *
     * @param \Cake\ORM\Query $query The query to find with
     * @param array $options The options to find with
     * @return \Cake\ORM\Query The query builder
     */
    public function findToken($query, $options)
    {
        return $this->find()->matching('Tokens', function ($q) use ($options) {
            return $q->where(['Tokens.token' => $options['token']]);
        });
    }
}
