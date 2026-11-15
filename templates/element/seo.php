<?php
/** @var \App\View\AppView $this */
use App\View\Helper\SeoHelper;

$seo = $this->Seo->metadata();
$siteUrl = $this->Url->build(['_name' => 'plugin:index'], ['fullBase' => true]);
$schema = [
    '@context' => 'https://schema.org',
    '@graph' => [
        [
            '@type' => 'WebSite',
            '@id' => $siteUrl . '#website',
            'url' => $siteUrl,
            'name' => 'CakePHP Plugins',
            'inLanguage' => 'en',
        ],
        [
            '@type' => 'WebPage',
            '@id' => $seo['url'],
            'url' => $seo['url'],
            'name' => $seo['title'],
            'description' => $seo['description'],
            'isPartOf' => ['@id' => $siteUrl . '#website'],
            'inLanguage' => 'en',
        ],
    ],
];
?>
<title><?= h($seo['title']) ?></title>
<meta name="description" content="<?= h($seo['description']) ?>">
<link rel="canonical" href="<?= h($seo['url']) ?>" data-filter-query-keys="<?= h(json_encode(SeoHelper::FILTER_QUERY_KEYS, JSON_THROW_ON_ERROR)) ?>">
<?php if ($seo['robots'] !== null) : ?>
    <meta name="robots" content="<?= h($seo['robots']) ?>">
<?php endif; ?>
<meta property="og:type" content="website">
<meta property="og:site_name" content="CakePHP Plugins">
<meta property="og:title" content="<?= h($seo['title']) ?>">
<meta property="og:description" content="<?= h($seo['description']) ?>">
<meta property="og:url" content="<?= h($seo['url']) ?>">
<meta property="og:image" content="<?= h($seo['image']) ?>">
<meta property="og:image:width" content="1200">
<meta property="og:image:height" content="630">
<meta property="og:image:alt" content="<?= h($seo['imageAlt']) ?>">
<meta name="twitter:card" content="summary_large_image">
<meta name="twitter:title" content="<?= h($seo['title']) ?>">
<meta name="twitter:description" content="<?= h($seo['description']) ?>">
<meta name="twitter:image" content="<?= h($seo['image']) ?>">
<meta name="twitter:image:alt" content="<?= h($seo['imageAlt']) ?>">
<script type="application/ld+json"><?= json_encode($schema, JSON_THROW_ON_ERROR | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?></script>
