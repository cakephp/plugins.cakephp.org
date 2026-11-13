<?php
declare(strict_types=1);

namespace App\Test\TestCase\Service;

use App\Service\GithubApiService;
use App\Service\GithubProfileService;
use Cake\Cache\Cache;
use Cake\Http\Client\Exception\ClientException;
use Cake\Http\Client\Response;
use Cake\TestSuite\TestCase;
use PHPUnit\Framework\Attributes\DataProvider;

/**
 * Public enrichment remains optional, cached, and independent of login data.
 */
class GithubProfileServiceTest extends TestCase
{
    /**
     * Keep cache hits and failures independent between examples.
     */
    protected function setUp(): void
    {
        parent::setUp();
        Cache::clear('github_profiles');
    }

    /**
     * Normalize the owner and retain only public presentation fields.
     */
    public function testProfileIsCachedAcrossRequestsAndCasing(): void
    {
        $client = $this->createMock(GithubApiService::class);
        $client->expects($this->once())->method('get')->with('/users/cakedc')->willReturn(new Response(
            ['HTTP/1.1 200 OK'],
            json_encode([
                'login' => 'CakeDC',
                'type' => 'Organization',
                'avatar_url' => 'https://avatars.githubusercontent.com/u/123',
                'bio' => '  CakePHP development and consulting.  ',
                'email' => 'private@example.com',
            ], JSON_THROW_ON_ERROR),
        ));
        $expected = [
            'avatarUrl' => 'https://avatars.githubusercontent.com/u/123',
            'bio' => 'CakePHP development and consulting.',
        ];
        $this->assertSame($expected, new GithubProfileService($client)->profile('CakeDC'));
        $this->assertSame($expected, new GithubProfileService($client)->profile('cakedc'));
    }

    /**
     * Missing, throttled, and invalid responses all use a cached fallback.
     */
    #[DataProvider('unavailableProfiles')]
    public function testUnavailableProfileUsesCachedFallback(string $status, string $body): void
    {
        $client = $this->createMock(GithubApiService::class);
        $client->expects($this->once())->method('get')->willReturn(new Response([$status], $body));
        $service = new GithubProfileService($client);
        $this->assertSame([
            'avatarUrl' => 'https://github.com/example.png?size=192',
            'bio' => null,
        ], $service->profile('example'));
        $this->assertSame([
            'avatarUrl' => 'https://github.com/example.png?size=192',
            'bio' => null,
        ], $service->profile('example'));
    }

    /**
     * @return array<string, array{string, string}>
     */
    public static function unavailableProfiles(): array
    {
        return [
            'missing' => ['HTTP/1.1 404 Not Found', '{}'],
            'rate limited' => ['HTTP/1.1 403 Forbidden', '{}'],
            'server error' => ['HTTP/1.1 503 Unavailable', '{}'],
            'malformed JSON' => ['HTTP/1.1 200 OK', '<html>Unavailable</html>'],
            'wrong profile' => ['HTTP/1.1 200 OK', '{"login":"somebody-else"}'],
        ];
    }

    /**
     * Network failures do not repeatedly retry on subsequent catalogue visits.
     */
    public function testNetworkFailureUsesCachedFallback(): void
    {
        $client = $this->createMock(GithubApiService::class);
        $client->expects($this->once())->method('get')->willThrowException(new ClientException('Connection failed'));
        $service = new GithubProfileService($client);
        $this->assertSame([
            'avatarUrl' => 'https://github.com/example.png?size=192',
            'bio' => null,
        ], $service->profile('example'));
        $this->assertSame([
            'avatarUrl' => 'https://github.com/example.png?size=192',
            'bio' => null,
        ], $service->profile('example'));
    }

    /**
     * Do not render non-GitHub avatar URLs or blank biographies.
     */
    public function testUnsafeAvatarAndEmptyBioAreOmitted(): void
    {
        $client = $this->createStub(GithubApiService::class);
        $client->method('get')->willReturn(new Response(
            ['HTTP/1.1 200 OK'],
            '{"login":"example","avatar_url":"javascript:alert(1)","bio":"   "}',
        ));
        $this->assertSame(['avatarUrl' => 'https://github.com/example.png?size=192', 'bio' => null], new GithubProfileService($client)->profile('example'));
    }

    /**
     * Invalid owner values never become outbound request paths.
     */
    public function testInvalidOwnerDoesNotFetch(): void
    {
        $client = $this->createMock(GithubApiService::class);
        $client->expects($this->never())->method('get');
        $this->assertNull(new GithubProfileService($client)->profile('../users'));
    }

    /**
     * Directory avatars do not consume API quota.
     */
    public function testAvatarUrlDoesNotFetchMetadata(): void
    {
        $github = $this->createMock(GithubApiService::class);
        $github->expects($this->never())->method('get');
        $service = new GithubProfileService($github);
        $this->assertSame('https://github.com/example.png?size=96', $service->avatarUrl('Example', 96));
        $this->assertNull($service->avatarUrl('invalid_owner'));
    }

    /**
     * Existing cached failures must not hide otherwise accessible public avatars.
     */
    public function testCachedRateLimitStillProvidesPublicAvatar(): void
    {
        Cache::pool('github_profiles')->set('github_profile_v2_lordsimal', [], 900);
        $client = $this->createMock(GithubApiService::class);
        $client->expects($this->never())->method('get');
        $this->assertSame([
            'avatarUrl' => 'https://github.com/lordsimal.png?size=192',
            'bio' => null,
        ], new GithubProfileService($client)->profile('LordSimal'));
    }
}
