<?php
namespace Users\Model\Table\Traits;

use Cake\Core\Configure;
use Cake\Event\Event;

trait TokenTrait
{
    /**
     * Callbacks definition
     *
     * @return array
     */
    public function implementedEvents()
    {
        return [
            'Model.initialize' => 'modelInitialize',
        ];
    }

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

    /**
     * Attaches the tokenize behavior to the table
     *
     * @return void
     */
    public function modelInitialize(Event $event)
    {
        if (Configure::read('Users.enablePasswordReset') !== true) {
            return;
        }

        $Table = $event->getSubject();
        if (!$Table->behaviors()->has('Muffin/Tokenize.Tokenize')) {
            $this->addBehavior('Muffin/Tokenize.Tokenize');
        }
    }
}
