<?php
declare(strict_types=1);

namespace App\Test\TestCase\Service;

use App\Service\GithubProfileService;
use Cake\Cache\Cache;
use Cake\Http\Client;
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
        $client = $this->createMock(Client::class);
        $client->expects($this->once())->method('get')->with('https://api.github.com/users/cakedc')->willReturn(new Response(
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
        $client = $this->createMock(Client::class);
        $client->expects($this->once())->method('get')->willReturn(new Response([$status], $body));
        $service = new GithubProfileService($client);
        $this->assertNull($service->profile('example'));
        $this->assertNull($service->profile('example'));
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
        $client = $this->createMock(Client::class);
        $client->expects($this->once())->method('get')->willThrowException(new ClientException('Connection failed'));
        $service = new GithubProfileService($client);
        $this->assertNull($service->profile('example'));
        $this->assertNull($service->profile('example'));
    }

    /**
     * Do not render non-GitHub avatar URLs or blank biographies.
     */
    public function testUnsafeAvatarAndEmptyBioAreOmitted(): void
    {
        $client = $this->createStub(Client::class);
        $client->method('get')->willReturn(new Response(
            ['HTTP/1.1 200 OK'],
            '{"login":"example","avatar_url":"javascript:alert(1)","bio":"   "}',
        ));
        $this->assertSame(['avatarUrl' => null, 'bio' => null], new GithubProfileService($client)->profile('example'));
    }

    /**
     * Invalid owner values never become outbound request paths.
     */
    public function testInvalidOwnerDoesNotFetch(): void
    {
        $client = $this->createMock(Client::class);
        $client->expects($this->never())->method('get');
        $this->assertNull(new GithubProfileService($client)->profile('../users'));
    }
}
