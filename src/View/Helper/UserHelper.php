<?php
declare(strict_types=1);

namespace App\View\Helper;

use Cake\View\Helper;

/**
 * User helper
 *
 * Provides convenience methods for displaying user identity information
 * such as GitHub avatars and display names.
 *
 * @property \Cake\View\Helper\HtmlHelper $Html
 * @property \Authentication\View\Helper\IdentityHelper $Identity
 */
class UserHelper extends Helper
{
    protected array $helpers = ['Html', 'Authentication.Identity'];

    /**
     * Get the username of the logged-in user.
     *
     * @return string|null
     */
    public function username(): ?string
    {
        if (!$this->Identity->isLoggedIn()) {
            return null;
        }

        return (string)$this->Identity->get('username') ?: null;
    }

    /**
     * Get the display name of the logged-in user.
     *
     * Falls back to username if first/last name are not set.
     *
     * @return string|null
     */
    public function displayName(): ?string
    {
        if (!$this->Identity->isLoggedIn()) {
            return null;
        }

        $firstName = (string)$this->Identity->get('first_name');
        $lastName = (string)$this->Identity->get('last_name');
        $fullName = trim($firstName . ' ' . $lastName);

        return $fullName ?: $this->username();
    }

    /**
     * Get the GitHub avatar URL for the logged-in user.
     *
     * @param int $size Image size in pixels.
     * @return string|null
     */
    public function avatarUrl(int $size = 80): ?string
    {
        $username = $this->username();
        if ($username === null) {
            return null;
        }

        return 'https://github.com/' . urlencode($username) . '.png?size=' . $size;
    }

    /**
     * Render an avatar `<img>` tag for the logged-in user.
     *
     * @param int $size Image size in pixels.
     * @param array<string, mixed> $attrs Additional HTML attributes for the img tag.
     * @return string|null
     */
    public function avatar(int $size = 80, array $attrs = []): ?string
    {
        $url = $this->avatarUrl($size);
        if ($url === null) {
            return null;
        }

        $attrs += [
            'alt' => $this->username(),
            'loading' => 'lazy',
            'referrerpolicy' => 'no-referrer',
        ];

        return $this->Html->image($url, $attrs);
    }
}
