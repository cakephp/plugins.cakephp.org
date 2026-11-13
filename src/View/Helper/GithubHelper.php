<?php
declare(strict_types=1);

namespace App\View\Helper;

use App\Utility\GithubAvatar;
use Cake\View\Helper;
use Override;

/**
 * Renders public GitHub avatars without performing profile lookups.
 *
 * @property \Cake\View\Helper\HtmlHelper $Html
 */
class GithubHelper extends Helper
{
    #[Override]
    protected array $helpers = ['Html'];

    /**
     * URL generation remains independent of authenticated identities and profile metadata.
     */
    public function avatarUrl(string $owner, int $size = 192): ?string
    {
        return GithubAvatar::url($owner, $size);
    }

    /**
     * Render the avatar inside a caller-owned DaisyUI avatar wrapper.
     *
     * @param string $name Display name used for the initials fallback.
     * @param string|null $githubUrl Unambiguous GitHub profile URL, when known.
     * @param array{class?: string, size?: int, requestSize?: int, avatarUrl?: string|null, loading?: bool, imageAttributes?: array<string, mixed>} $options Presentation options.
     */
    public function avatar(string $name, ?string $githubUrl, array $options = []): string
    {
        $options += [
            'class' => 'namespace-avatar',
            'size' => 96,
            'avatarUrl' => null,
            'loading' => false,
            'imageAttributes' => [],
        ];
        $url = $options['avatarUrl'];
        if (
            $url === null && $githubUrl !== null &&
            parse_url($githubUrl, PHP_URL_SCHEME) === 'https' && parse_url($githubUrl, PHP_URL_HOST) === 'github.com'
        ) {
            $owner = trim((string)parse_url($githubUrl, PHP_URL_PATH), '/');
            $url = $this->avatarUrl($owner, $options['requestSize'] ?? $options['size'] * 2);
        }

        $content = $this->Html->tag('span', mb_strtoupper(mb_substr($name, 0, 2)), ['escape' => true]);
        if ($url !== null) {
            $content .= $this->Html->image($url, $options['imageAttributes'] + [
                'alt' => '',
                'width' => $options['size'],
                'height' => $options['size'],
                'decoding' => 'async',
                'referrerpolicy' => 'no-referrer',
                'x-show' => $options['loading'] ? '$server.error && !failed' : '!failed',
                '@error' => 'failed = true',
                'x-init' => 'failed = $el.complete && $el.naturalWidth === 0',
            ]);
        }
        if ($options['loading']) {
            $content .= $this->Html->tag('div', '', [
                'class' => 'skeleton absolute inset-0 rounded-[inherit] motion-reduce:animate-none',
                'x-show' => '!$server.error',
            ]);
        }

        return $this->Html->tag('div', $content, [
            'class' => $options['class'],
            'x-data' => '{ failed: false }',
            'aria-hidden' => 'true',
        ]);
    }
}
