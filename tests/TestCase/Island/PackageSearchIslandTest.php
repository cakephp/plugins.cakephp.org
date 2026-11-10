<?php
declare(strict_types=1);

namespace App\Test\TestCase\Island;

use Cake\Http\Exception\BadRequestException;
use Cake\TestSuite\TestCase;
use Interact\Runtime\RedirectResult;
use Interact\TestSuite\IslandTestTrait;

/**
 * Tests PHP-rendered suggestions and the enhanced search destination.
 */
class PackageSearchIslandTest extends TestCase
{
    use IslandTestTrait;

    /**
     * @var list<string>
     */
    protected array $fixtures = ['app.Packages', 'plugin.Tags.Tags', 'plugin.Tags.Tagged'];

    /**
     * Short and unmatched drafts produce no result options.
     */
    public function testShortAndUnmatchedQueriesHaveNoSuggestions(): void
    {
        $this->testIsland('PackageSearch')
            ->assertDontSee('role="option"')
            ->set('query', 'a')->assertDontSee('role="option"')
            ->set('query', 'unmatched-package-name')->assertDontSee('role="option"');
    }

    /**
     * Suggestions preserve the existing eight-result finder and safe links.
     */
    public function testSuggestionsAreBoundedAndRendered(): void
    {
        $island = $this->testIsland('PackageSearch')->set('query', 'package');
        $this->assertSame(8, substr_count($island->result()->html, 'role="option"'));
        $island->set('query', 'asset')->assertSee('markstory/asset_compress')->assertSee('noopener noreferrer');
    }

    /**
     * Submitting trims the draft and preserves the current version selection.
     */
    public function testSubmitUsesEnhancedRedirectWithAppliedFilters(): void
    {
        $island = $this->testIsland('PackageSearch')->set('query', ' asset ')
            ->call('submit', ['cakephp_slugs' => ['cakephp-5-4'], 'php_slugs' => ['php-8-5']]);
        $redirect = $island->result();
        $this->assertInstanceOf(RedirectResult::class, $redirect);
        $this->assertTrue($redirect->navigate);
        $this->assertStringContainsString('search=asset', $redirect->destination);
        $this->assertStringNotContainsString('page=', $redirect->destination);
    }

    /**
     * The POST transport cannot bypass the search-length bound.
     */
    public function testOverlongSearchIsRejected(): void
    {
        $island = $this->testIsland('PackageSearch');
        $this->expectException(BadRequestException::class);
        $island->set('query', str_repeat('a', 101));
    }
}
