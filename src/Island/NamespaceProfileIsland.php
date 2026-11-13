<?php
declare(strict_types=1);

namespace App\Island;

use App\Service\GithubProfileService;
use Interact\Attribute\Prop;
use Interact\Island;

/**
 * Enrich namespace identity after the hero has rendered.
 */
class NamespaceProfileIsland extends Island
{
    #[Prop]
    public string $namespace = '';

    #[Prop]
    public string $githubUrl = '';

    /**
     * Resolving the service performs no remote work during placeholder rendering.
     */
    public function __construct(private readonly GithubProfileService $profiles)
    {
    }

    /**
     * @return array<string, mixed>
     */
    public function viewData(): array
    {
        return [
            'profile' => $this->profiles->profile(basename($this->githubUrl)),
        ];
    }
}
