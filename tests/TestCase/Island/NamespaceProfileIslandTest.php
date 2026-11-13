<?php
declare(strict_types=1);

namespace App\Test\TestCase\Island;

use App\Service\GithubProfileService;
use Cake\TestSuite\TestCase;
use Interact\TestSuite\IslandTestTrait;

/**
 * Public profile enrichment uses signed props and escaped presentation.
 */
class NamespaceProfileIslandTest extends TestCase
{
    use IslandTestTrait;

    /**
     * Composer namespace and GitHub owner can differ.
     */
    public function testProfileUsesRepositoryOwnerAndEscapesBio(): void
    {
        $service = $this->createMock(GithubProfileService::class);
        $service->expects($this->once())->method('profile')->with('github-owner')->willReturn([
            'avatarUrl' => 'https://avatars.githubusercontent.com/u/123',
            'bio' => '<script>Example</script>',
        ]);
        $this->mockService(GithubProfileService::class, static fn(): GithubProfileService => $service);
        $island = $this->testIsland('NamespaceProfile', [
            'namespace' => 'composer-vendor',
            'githubUrl' => 'https://github.com/github-owner',
        ])->assertSee('composer-vendor')->assertSee('<script>Example</script>')->assertSee('https://avatars.githubusercontent.com/u/123');
        $this->assertStringNotContainsString('<script>Example</script>', $island->result()->html);
        $this->assertStringNotContainsString('skeleton', $island->result()->html);
    }

    /**
     * Unavailable enrichment renders initials and the same generic description.
     */
    public function testUnavailableProfileKeepsIdentityAndDescription(): void
    {
        $service = $this->createStub(GithubProfileService::class);
        $service->method('profile')->willReturn(null);
        $this->mockService(GithubProfileService::class, static fn(): GithubProfileService => $service);
        $this->testIsland('NamespaceProfile', [
            'namespace' => 'example',
            'githubUrl' => 'https://github.com/example',
        ])->assertSee('EX')
            ->assertSee('Plugins for your CakePHP projects.')->assertSee('https://github.com/example.png?size=192');
    }

    /**
     * Accounts with an avatar but no bio get the generic description.
     */
    public function testMissingBioUsesFallback(): void
    {
        $service = $this->createStub(GithubProfileService::class);
        $service->method('profile')->willReturn([
            'avatarUrl' => 'https://avatars.githubusercontent.com/u/123',
            'bio' => null,
        ]);
        $this->mockService(GithubProfileService::class, static fn(): GithubProfileService => $service);
        $this->testIsland('NamespaceProfile', [
            'namespace' => 'example',
            'githubUrl' => 'https://github.com/example',
        ])->assertSee('Plugins for your CakePHP projects.')->assertSee('data-namespace-avatar');
    }
}
