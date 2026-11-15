<?php
declare(strict_types=1);

namespace App\View\Helper;

use Cake\Routing\Router;
use Cake\Utility\Text;
use Cake\View\Helper;

/**
 * Shared metadata is built from view blocks without waiting for lazy islands.
 */
class SeoHelper extends Helper
{
    public const array FILTER_QUERY_KEYS = [
        'search', 'cakephp_slugs', 'php_slugs', 'category', 'awesome',
        'sort', 'direction', 'page', 'keyword', 'query', 'cakephp',
    ];

    /**
     * Canonicals omit query variants; filtered catalogues remain followable.
     *
     * @return array{title: string, description: string, url: string, image: string, imageAlt: string, robots: string|null}
     */
    public function metadata(): array
    {
        $view = $this->getView();
        $request = $view->getRequest();
        $title = $this->plainText($view->fetch('title'));
        $description = $this->plainText($view->fetch('description'));
        $robots = null;
        if ($request->getParam('plugin') === 'Admin' || preg_match('#^/(admin|auth)(/|$)#', $request->getPath())) {
            $robots = 'noindex,nofollow';
        } elseif (array_intersect(array_keys($request->getQueryParams()), self::FILTER_QUERY_KEYS)) {
            $robots = 'noindex,follow';
        }

        return [
            'title' => $title !== '' ? $title . ' | CakePHP Plugins' : 'CakePHP Plugins',
            'description' => $description ?: 'Discover CakePHP plugins for your next project. Browse by CakePHP and PHP version, explore publishers, and find installation and compatibility details.',
            'url' => $view->fetch('canonicalUrl') ?: Router::url($request->getPath() ?: '/', true),
            'image' => Router::url('/img/social-card.png', true),
            'imageAlt' => 'CakePHP Plugins — Discover plugins for your projects',
            'robots' => $robots,
        ];
    }

    /**
     * Package descriptions may contain HTML, entities or extra whitespace.
     */
    public function plainText(string $value): string
    {
        $value = strip_tags(html_entity_decode($value, ENT_QUOTES | ENT_HTML5, 'UTF-8'));
        $value = trim(preg_replace('/\s+/u', ' ', $value) ?? $value);

        return Text::truncate($value, 160, ['ellipsis' => '…', 'exact' => false, 'html' => false]);
    }
}
