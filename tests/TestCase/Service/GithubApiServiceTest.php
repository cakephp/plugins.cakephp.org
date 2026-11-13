<?php
declare(strict_types=1);

namespace App\Test\TestCase\Service;

use App\Service\GithubApiService;
use Cake\Http\Client;
use Cake\Http\Client\Response;
use Cake\TestSuite\TestCase;
use InvalidArgumentException;

/**
 * Shared request settings never leak a login token into another request.
 */
class GithubApiServiceTest extends TestCase
{
    /**
     * Public requests set GitHub defaults without requiring authentication.
     */
    public function testPublicRequestUsesSharedDefaults(): void
    {
        $response = new Response(['HTTP/1.1 200 OK'], '{}');
        $client = $this->createMock(Client::class);
        $client->expects($this->once())->method('get')->with('https://api.github.com/users/example', [], [
            'timeout' => 3,
            'headers' => [
                'Accept' => 'application/vnd.github+json',
                'User-Agent' => 'plugins.cakephp.org',
                'X-GitHub-Api-Version' => '2022-11-28',
            ],
        ])->willReturn($response);
        $this->assertSame($response, new GithubApiService($client)->get('/users/example'));
    }

    /**
     * User-specific credentials override the application token for one request only.
     */
    public function testRequestTokenDoesNotLeakIntoSubsequentRequests(): void
    {
        $client = $this->createMock(Client::class);
        $requests = [];
        $client->expects($this->exactly(3))->method('get')->willReturnCallback(
            static function (string $url, array $query, array $options) use (&$requests): Response {
                $requests[] = [$url, $query, $options['headers']['Authorization'] ?? null];

                return new Response(['HTTP/1.1 200 OK'], '{}');
            },
        );
        $github = new GithubApiService($client, 'application-token');
        $github->get('/user/memberships/orgs/cakephp', token: 'user-token');
        $github->get('/users/example', ['page' => 2]);
        $github->get('/users/example', token: '');
        $this->assertSame([
            ['https://api.github.com/user/memberships/orgs/cakephp', [], 'Bearer user-token'],
            ['https://api.github.com/users/example', ['page' => 2], 'Bearer application-token'],
            ['https://api.github.com/users/example', [], null],
        ], $requests);
    }

    /**
     * A caller cannot direct the configured application token to another host.
     */
    public function testAbsoluteUrlsAreRejected(): void
    {
        $client = $this->createMock(Client::class);
        $client->expects($this->never())->method('get');
        $this->expectException(InvalidArgumentException::class);
        new GithubApiService($client, 'application-token')->get('https://example.com/users');
    }
}
