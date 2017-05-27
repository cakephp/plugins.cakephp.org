<?php
namespace App\View\Helper;

use Cake\Core\Configure;
use Cake\View\Helper;

/**
 * Resource Helper
 */
class ResourceHelper extends AppHelper
{

    public $helpers = ['Form', 'Html', 'Text', 'Time'];

    /**
     * Returns an HTML link for a package
     *
     * @param string $name Package display name
     * @param int $packageId Package id
     * @param string $slug Slug for package
     * @return string
     */
    public function packageLink($name, $packageId, $slug)
    {
        return $this->Html->link($name, [
            'plugin' => null,
            'controller' => 'packages',
            'action' => 'view',
            'id' => $packageId,
            'slug' => $slug
        ], ['title' => $name]);
    }

    /**
     * Returns a link to a package
     *
     * @param string $name Package display name
     * @param int $packageId Package id
     * @return string
     */
    public function packageUrl($name, $packageId)
    {
        return $this->url([
            'plugin' => null,
            'controller' => 'packages',
            'action' => 'view',
            'id' => $package['id'],
            'slug' => $package['name']
        ]);
    }

    public function githubUrl($maintainer, $package, $name = null)
    {
        $link = "https://github.com/{$maintainer}/{$package}";
        if ($name === null) {
            $name = $link;
        }

        return $this->Html->link($name, $link, [
            'target' => '_blank',
            'class' => 'external github-external',
            'package-name' => "{$maintainer}-{$package}",
        ]);
    }

    public function cloneUrl($maintainer, $name)
    {
        return $this->Form->input('clone', [
            'class' => 'form-control clone-url',
            'div' => false,
            'label' => false,
            'value' => "git://github.com/{$maintainer}/{$name}.git"
        ]);
    }

    public function gravatar($username, $avatarUrl, $gravatarId = null)
    {
        if (empty($avatarUrl) && empty($gravatarId)) {
            return '';
        }

        if (empty($avatarUrl)) {
            $avatarUrl = sprintf('https://secure.gravatar.com/avatar/%s', $gravatarId);
        }

        return $this->Html->image($avatarUrl, [
            'alt' => 'Gravatar for ' . $username,
            'class' => 'img-circle'
        ]);
    }

    public function description($text)
    {
        $text = trim($text);
        return $this->Html->tag('p', $this->Text->truncate(
            $this->Text->autoLink($text),
            100,
            ['html' => true]
        ), ['class' => 'lead']);
    }

    public function sort($order)
    {
        list($order, $direction) = explode(' ', $order);
        list(, $sortField) = explode('.', $order);

        if ($direction == 'asc') {
            $direction = 'desc';
        } else {
            $direction = 'asc';
        }

        $order = null;

        $output = [];
        foreach (Package::$validShownOrders as $sort => $name) {
            if ($sort == $sortField) {
                $output[] = $this->Html->link($name, ['?' => array_merge(
                    (array)$this->_View->request->query,
                    compact('sort', 'direction', 'order')
                )], ['class' => 'active ' . $direction]);
            } else {
                $output[] = $this->Html->link($name, ['?' => array_merge(
                    (array)$this->_View->request->query,
                    ['sort' => $sort, 'direction' => 'desc', 'order' => $order]
                )]);
            }
        }

        return implode(' ', $output);
    }

    public function tagCloud($tags)
    {
        $tags = explode(',', $tags);
        sort($tags);

        $links = [];
        foreach ($tags as $tag) {
            $links[] = $this->tagLink($tag);
        }

        return implode("\n", $links);
    }

    public function tagLink($tag)
    {
        $colorMap = [
            'version:3' => '#27a4dd',
            'version:2' => '#9dd5c0',
            'version:1.3' => '#ffaaa5',
            'version:1.2' => '#ffd3b6',
        ];
        list($key, $value) = explode(':', $tag, 2);
        $options = ['class' => 'label category-label'];
        $queryString = [$key => $value];
        $url = ['controller' => 'packages', 'action' => 'index'];

        if (isset($colorMap[$tag])) {
            $queryString = ['version' => $key];
            $options['style'] = sprintf('background-color:%s;', $colorMap[$tag]);
            $version = strpos($key, '.') === false ? $value . '.x' : $value;
            return $this->Html->link('version:' . $version, $url, $options);
        }

        $color = 'black';
        if (in_array($key, ['has', 'keyword'])) {
            $color = $key == 'has' ? 'gray' : 'darkgray';
            $queryString = [
                $key => [$value]
            ];
        }

        $url['?'] = $queryString;
        $options['style'] = sprintf('background-color:%s;color:white;', $color);
        return $this->Html->link($tag, $url, $options);
    }
}
