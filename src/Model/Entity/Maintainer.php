<?php
namespace App\Model\Entity;

use Cake\ORM\Entity;

/**
 * Maintainer Entity
 *
 * @property int $id
 * @property int $user_id
 * @property string $group
 * @property string $username
 * @property string $email
 * @property string $name
 * @property string $alias
 * @property string $url
 * @property string $twitter_username
 * @property string $company
 * @property string $location
 * @property \Cake\I18n\Time $created
 * @property \Cake\I18n\Time $modified
 * @property string $gravatar_id
 * @property string $password
 * @property string $activation_key
 * @property int $github_id
 * @property string $avatar_url
 *
 * @property \App\Model\Entity\User $user
 * @property \App\Model\Entity\Gravatar $gravatar
 * @property \App\Model\Entity\Github $github
 * @property \App\Model\Entity\Package[] $packages
 */
class Maintainer extends Entity
{

    /**
     * Fields that can be mass assigned using newEntity() or patchEntity().
     *
     * Note that when '*' is set to true, this allows all unspecified fields to
     * be mass assigned. For security purposes, it is advised to set '*' to false
     * (or remove it), and explicitly make individual fields accessible as needed.
     *
     * @var array
     */
    protected $_accessible = [
        '*' => true,
        'id' => false,
    ];

    /**
     * Fields that are excluded from JSON versions of the entity.
     *
     * @var array
     */
    protected $_hidden = [
        'password',
    ];

    public function route()
    {
        return [
            'plugin' => null,
            'controller' => 'Maintainers',
            'action' => 'view',
            'id' => $this->id,
            'slug' => $this->username,
        ];
    }
}
