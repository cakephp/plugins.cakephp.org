<?php
declare(strict_types=1);

namespace App\Test\TestCase\Controller;

use App\Model\Table\PackagesTable;
use Cake\Core\Configure;
use Cake\Routing\Router;
use Cake\TestSuite\IntegrationTestTrait;
use Cake\TestSuite\TestCase;
use DOMDocument;
use DOMXPath;
use Override;
use PHPUnit\Framework\Attributes\DataProvider;

/**
 * Public metadata must be useful in the initial HTML, including lazy pages.
 */
class SeoTest extends TestCase
{
    use IntegrationTestTrait;

    #[Override]
    protected array $fixtures = [
        'app.Packages', 'app.FeaturedPlugins', 'app.Categories', 'app.CategoriesPackages',
        'plugin.Tags.Tags', 'plugin.Tags.Tagged',
    ];

    /**
     * Titles, social previews and structured data agree on each canonical URL.
     */
    #[DataProvider('publicPages')]
    public function testPublicMetadata(string $path, string $canonical, string $title, string $description): void
    {
        $this->get($path);
        $this->assertResponseOk();
        $head = $this->document();
        $url = Router::url($canonical, true);
        $this->assertSame($title . ' | CakePHP Plugins', $head->evaluate('string(//head/title)'));
        $this->assertStringContainsString($description, $head->evaluate('string(//meta[@name="description"]/@content)'));
        $this->assertSame($url, $head->evaluate('string(//link[@rel="canonical"]/@href)'));
        $this->assertSame($url, $head->evaluate('string(//meta[@property="og:url"]/@content)'));
        $this->assertSame($title . ' | CakePHP Plugins', $head->evaluate('string(//meta[@property="og:title"]/@content)'));
        $this->assertSame('summary_large_image', $head->evaluate('string(//meta[@name="twitter:card"]/@content)'));
        $this->assertSame(Router::url('/img/social-card.png', true), $head->evaluate('string(//meta[@property="og:image"]/@content)'));
        $this->assertSame(0, $head->query('//meta[@name="robots"]')->length);
        $this->assertSame('en', $head->evaluate('string(/html/@lang)'));
        $this->assertSame(1, $head->query('//h1')->length);
        $schema = json_decode($head->evaluate('string(//script[@type="application/ld+json"])'), true, flags: JSON_THROW_ON_ERROR);
        $this->assertSame('WebSite', $schema['@graph'][0]['@type']);
        $this->assertSame($url, $schema['@graph'][1]['url']);
        $this->assertSame($head->evaluate('string(//meta[@name="description"]/@content)'), $schema['@graph'][1]['description']);
    }

    /**
     * @return array<string, array{string, string, string, string}>
     */
    public static function publicPages(): array
    {
        return [
            'catalogue' => ['/', '/', 'Discover CakePHP plugins', 'Find CakePHP plugins'],
            'catalogue alias' => ['/packages', '/', 'Discover CakePHP plugins', 'Find CakePHP plugins'],
            'publishers' => ['/publishers', '/publishers', 'Publishers', 'plugin publishers'],
            'publisher profile' => ['/plugins/vendor', '/plugins/vendor', 'Plugins by vendor', '23 CakePHP plugins by vendor'],
            'plugin detail' => ['/plugins/cakedc/users', '/plugins/cakedc/users', 'cakedc/users', 'Users plugin'],
            'requirements' => ['/requirements', '/requirements', 'Listing Requirements', 'Packagist requirements'],
            'requirements alias' => ['/pages/requirements', '/requirements', 'Listing Requirements', 'Packagist requirements'],
        ];
    }

    /**
     * Filter URLs should not be indexed, while tracking parameters do not hide pages.
     */
    public function testQueryVariants(): void
    {
        foreach (['/?search=users', '/plugins/vendor?sort=stars', '/publishers?cakephp=5'] as $path) {
            $this->get($path);
            $this->assertResponseOk();
            $head = $this->document();
            $this->assertSame('noindex,follow', $head->evaluate('string(//meta[@name="robots"]/@content)'));
            $this->assertStringNotContainsString('?', $head->evaluate('string(//link[@rel="canonical"]/@href)'));
        }
        $this->get('/?utm_source=newsletter');
        $head = $this->document();
        $this->assertSame(0, $head->query('//meta[@name="robots"]')->length);
        $this->assertSame(Router::url('/', true), $head->evaluate('string(//link[@rel="canonical"]/@href)'));
    }

    /**
     * Imported descriptions are plain text, escaped once, and cannot close JSON-LD.
     */
    public function testImportedDescriptionEscaping(): void
    {
        $this->fetchTable(PackagesTable::class)->updateAll([
            'description' => '<b>A "quoted" plugin & tools</b> &lt;img src=x onerror=alert(1)&gt; </script> ' . str_repeat('CakePHP ', 30),
        ], ['id' => 25]);
        $this->get('/plugins/cakedc/users');
        $this->assertResponseOk();
        $head = $this->document();
        $description = $head->evaluate('string(//meta[@name="description"]/@content)');
        $this->assertStringStartsWith('A "quoted" plugin & tools CakePHP', $description);
        $this->assertLessThanOrEqual(160, mb_strlen($description));
        $this->assertSame(1, $head->query('//meta[@name="description"]')->length);
        $this->assertSame(1, $head->query('//script[@type="application/ld+json"]')->length);
        $schema = json_decode($head->evaluate('string(//script[@type="application/ld+json"])'), true, flags: JSON_THROW_ON_ERROR);
        $this->assertSame($description, $schema['@graph'][1]['description']);
    }

    /**
     * Error pages preserve their HTTP status and must not be indexed.
     */
    public function testMissingPageIsNotIndexable(): void
    {
        $debug = Configure::read('debug');
        Configure::write('debug', false);
        try {
            $this->get('/plugins/unknown/no-such-package');
            $this->assertResponseCode(404);
            $this->assertResponseContains('class="error-page site-width"');
            $this->assertResponseNotContains('data-ecosystem-snapshot');
            $this->assertResponseContains('x-interact:asset="reload"');
            $this->assertSame('noindex,nofollow', $this->document()->evaluate('string(//meta[@name="robots"]/@content)'));
        } finally {
            Configure::write('debug', $debug);
        }
    }

    /**
     * Parse the actual server-rendered document without executing JavaScript.
     */
    private function document(): DOMXPath
    {
        $document = new DOMDocument();
        $previous = libxml_use_internal_errors(true);
        try {
            $document->loadHTML((string)$this->_response->getBody());
        } finally {
            libxml_clear_errors();
            libxml_use_internal_errors($previous);
        }

        return new DOMXPath($document);
    }
}
