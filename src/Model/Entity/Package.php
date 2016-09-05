<?php
namespace App\Model\Entity;

use App\Traits\GithubRssTrait;
use Cake\Cache\Cache;
use Cake\ORM\Entity;
use Cake\Routing\Router;

/**
 * Package Entity
 *
 * @property int $id
 * @property int $maintainer_id
 * @property string $name
 * @property string $repository_url
 * @property string $bakery_article
 * @property string $homepage
 * @property string $description
 * @property string $tags
 * @property string $category_id
 * @property int $open_issues
 * @property int $forks
 * @property int $watchers
 * @property int $collaborators
 * @property int $contributors
 * @property \Cake\I18n\Time $created_at
 * @property \Cake\I18n\Time $last_pushed_at
 * @property \Cake\I18n\Time $created
 * @property \Cake\I18n\Time $modified
 * @property bool $contains_model
 * @property bool $contains_view
 * @property bool $contains_controller
 * @property bool $contains_behavior
 * @property bool $contains_helper
 * @property bool $contains_component
 * @property bool $contains_shell
 * @property bool $contains_theme
 * @property bool $contains_datasource
 * @property bool $contains_vendor
 * @property bool $contains_test
 * @property bool $contains_lib
 * @property bool $contains_resource
 * @property bool $contains_config
 * @property bool $contains_app
 * @property bool $deleted
 * @property int $forks_count
 * @property int $network_count
 * @property int $open_issues_count
 * @property int $stargazers_count
 * @property int $subscribers_count
 * @property int $watchers_count
 *
 * @property \App\Model\Entity\Maintainer $maintainer
 * @property \App\Model\Entity\Category $category
 */
class Package extends Entity
{
    use GithubRssTrait {
        rss as _rss;
    }

    protected static $_categoryColors = [];

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
        'id' => false
    ];

    public function route()
    {
        return [
            'plugin' => null,
            'controller' => 'Packages',
            'action' => 'view',
            'id' => $this->id,
            'slug' => $this->name,
        ];
    }

    public function disableRoute()
    {
        return [
            'admin' => true,
            'plugin' => null,
            'controller' => 'Packages',
            'action' => 'disable',
            $this->id
        ];
    }

    public function cloneUrl()
    {
        return "git://github.com:{$this->maintainer->username}/{$this->name}.git";
    }

    public function githubUrl()
    {
        return "https://github.com/{$this->maintainer->username}/{$this->name}";
    }

    public function rss()
    {
        return Cache::remember(sprintf('rss.%s', $this->id), [$this, '_rss']);
    }

    public function disqus()
    {
        return [
            'disqus_shortname' => 'cakepackages',
            'disqus_identifier' => $this->id,
            'disqus_title' => implode(' ', [
                $this->name,
                'by',
                $this->maintainer->username,
            ]),
            'disqus_url' => Router::url($this->route(), true),
        ];
    }
}
