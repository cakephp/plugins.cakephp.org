<?php
declare(strict_types=1);

namespace App\Service;

use Cake\Http\Client;
use Cake\Http\Client\Response;
use InvalidArgumentException;

/**
 * Shares GitHub request defaults without retaining a user's OAuth token.
 */
class GithubApiService
{
    /**
     * The optional application token is used only when no per-request token is supplied.
     */
    public function __construct(
        private readonly Client $client,
        private readonly string $token = '',
    ) {
    }

    /**
     * @param string $path API path relative to api.github.com, starting with a slash.
     * @param array<string, mixed> $query Query parameters.
     * @param string|null $token OAuth token for this request only.
     */
    public function get(string $path, array $query = [], ?string $token = null): Response
    {
        if (!str_starts_with($path, '/') || str_starts_with($path, '//')) {
            throw new InvalidArgumentException('GitHub API requests require a relative path starting with one slash.');
        }
        $headers = [
            'Accept' => 'application/vnd.github+json',
            'User-Agent' => 'plugins.cakephp.org',
            'X-GitHub-Api-Version' => '2022-11-28',
        ];
        $token ??= $this->token;
        if ($token !== '') {
            $headers['Authorization'] = 'Bearer ' . $token;
        }

        return $this->client->get('https://api.github.com' . $path, $query, [
            'timeout' => 3,
            'headers' => $headers,
        ]);
    }
}
