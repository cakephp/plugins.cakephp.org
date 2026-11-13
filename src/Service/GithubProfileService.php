<?php
declare(strict_types=1);

namespace App\Service;

use App\Utility\GithubAvatar;
use Cake\Cache\Cache;
use Psr\Http\Client\ClientExceptionInterface;

/**
 * Cache public GitHub profile details independently of authentication records.
 */
class GithubProfileService
{
    /**
     * Construct without fetching; lazy island rendering owns the network request.
     */
    public function __construct(private readonly GithubApiService $github)
    {
    }

    /**
     * @return array{avatarUrl: string|null, bio: string|null}|null
     */
    public function profile(string $owner): ?array
    {
        $avatarUrl = GithubAvatar::url($owner);
        if ($avatarUrl === null) {
            return null;
        }
        $owner = strtolower($owner);
        $fallback = [
            'avatarUrl' => $avatarUrl,
            'bio' => null,
        ];
        $key = 'github_profile_v2_' . $owner;
        $cache = Cache::pool('github_profiles');
        $cached = $cache->get($key);
        if (is_array($cached)) {
            return $cached !== [] ? [
                'avatarUrl' => $cached['avatarUrl'] ?? $fallback['avatarUrl'],
                'bio' => $cached['bio'] ?? null,
            ] : $fallback;
        }

        $profile = null;
        try {
            $response = $this->github->get('/users/' . rawurlencode($owner));
            $data = $response->isOk() ? $response->getJson() : null;
            if (is_array($data) && is_string($data['login'] ?? null) && strcasecmp($data['login'], $owner) === 0) {
                $avatarUrl = $data['avatar_url'] ?? null;
                if (
                    !is_string($avatarUrl) ||
                    parse_url($avatarUrl, PHP_URL_SCHEME) !== 'https' ||
                    !in_array(parse_url($avatarUrl, PHP_URL_HOST), ['avatars.githubusercontent.com', 'github.com'], true)
                ) {
                    $avatarUrl = $fallback['avatarUrl'];
                }
                $bio = is_string($data['bio'] ?? null) ? trim($data['bio']) : '';
                $profile = [
                    'avatarUrl' => $avatarUrl,
                    'bio' => $bio !== '' ? $bio : null,
                ];
            }
        } catch (ClientExceptionInterface) {
            // Optional enrichment must not prevent the catalogue from rendering.
        }
        $cache->set($key, $profile ?? [], $profile !== null ? 86400 : 900);

        return $profile ?? $fallback;
    }
}
