<?php
declare(strict_types=1);

namespace App\Test\TestCase\View\Helper;

use App\View\Helper\GithubHelper;
use Cake\TestSuite\TestCase;
use Cake\View\View;
use PHPUnit\Framework\Attributes\DataProvider;

/**
 * Avatar presentation is shared by ordinary views and isolated island renders.
 */
class GithubHelperTest extends TestCase
{
    /**
     * Public URLs need no profile metadata or API service.
     */
    #[DataProvider('avatarOwners')]
    public function testAvatarUrl(string $owner, ?string $expected): void
    {
        $this->assertSame($expected, new GithubHelper(new View())->avatarUrl($owner, 96));
    }

    /**
     * @return array<string, array{string, string|null}>
     */
    public static function avatarOwners(): array
    {
        return [
            'canonical account' => ['Example-Owner', 'https://github.com/example-owner.png?size=96'],
            'empty account' => ['', null],
            'invalid username' => ['invalid_owner', null],
            'invalid path' => ['../users', null],
        ];
    }

    /**
     * Prefer supplied API metadata and escape the initials fallback as text.
     */
    public function testAvatarMetadataAndEscaping(): void
    {
        $result = new GithubHelper(new View())->avatar('<script>', 'https://github.com/example', [
            'avatarUrl' => 'https://avatars.githubusercontent.com/u/123',
            'imageAttributes' => ['data-namespace-avatar' => ''],
        ]);
        $this->assertStringContainsString('&lt;S', $result);
        $this->assertStringNotContainsString('<script>', $result);
        $this->assertStringContainsString('https://avatars.githubusercontent.com/u/123', $result);
        $this->assertStringNotContainsString('https://github.com/example.png', $result);
        $this->assertStringContainsString('data-namespace-avatar', $result);
        $this->assertStringContainsString('referrerpolicy="no-referrer"', $result);
    }

    /**
     * Composer namespaces must not be guessed to be GitHub account names.
     */
    public function testPublicFallbackUsesResolvedOwner(): void
    {
        $helper = new GithubHelper(new View());
        $result = $helper->avatar('composer-vendor', 'https://github.com/github-owner', [
            'class' => 'publisher-avatar',
            'size' => 64,
            'requestSize' => 96,
            'imageAttributes' => ['loading' => 'lazy'],
        ]);
        $this->assertStringContainsString('https://github.com/github-owner.png?size=96', $result);
        $this->assertStringContainsString('width="64"', $result);
        $this->assertStringContainsString('loading="lazy"', $result);
        $this->assertStringContainsString('publisher-avatar', $result);
        $this->assertStringNotContainsString('<img', $helper->avatar('composer-vendor', null));
        $this->assertStringNotContainsString('<img', $helper->avatar('composer-vendor', 'https://example.com/github-owner'));
    }

    /**
     * Deferred profile avatars keep a skeleton until success or a recoverable failure.
     */
    public function testLoadingAvatarIncludesPublicFallbackAndSkeleton(): void
    {
        $result = new GithubHelper(new View())->avatar('example', 'https://github.com/example', ['loading' => true]);
        $this->assertStringContainsString('skeleton', $result);
        $this->assertStringContainsString('https://github.com/example.png?size=192', $result);
        $this->assertStringContainsString('$server.error &amp;&amp; !failed', $result);
        $this->assertStringContainsString('@error="failed = true"', $result);
    }
}
