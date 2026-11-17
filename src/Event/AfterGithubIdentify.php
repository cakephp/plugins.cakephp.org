<?php
declare(strict_types=1);

namespace App\Event;

use ADmad\SocialAuth\Middleware\SocialAuthMiddleware;
use App\Model\Entity\User;
use App\Model\Enum\UserRole;
use App\Model\Table\UsersTable;
use App\Service\GithubApiService;
use Cake\Event\Attribute\EventListener;
use Cake\Event\EventInterface;
use Cake\ORM\Locator\LocatorAwareTrait;

class AfterGithubIdentify
{
    use LocatorAwareTrait;

    /**
     * Resolve the shared GitHub client through the application container.
     */
    public function __construct(private readonly GithubApiService $github)
    {
    }

    /**
     * After identify callback.
     *
     * @param \Cake\Event\EventInterface<object> $event The event instance.
     * @param \App\Model\Entity\User $user The user entity.
     * @return void
     */
    #[EventListener(SocialAuthMiddleware::EVENT_AFTER_IDENTIFY)]
    public function afterIdentify(EventInterface $event, User $user): void
    {
        $token = $user->social_profile->access_token->getToken();
        if (!$token) {
            return;
        }

        $response = $this->github->get('/user/memberships/orgs/cakephp', token: $token);
        if (!$response->isOk() && $response->getStatusCode() !== 404) {
            return;
        }

        $isCakePHPDev = $response->isOk() && ($response->getJson()['state'] ?? null) === 'active';

        $usersTable = $this->fetchTable(UsersTable::class);
        // Organization membership is authoritative, even for the last administrator.
        $user->set('is_cakephp_dev', $isCakePHPDev);
        $user->set('role', $isCakePHPDev ? UserRole::Admin : UserRole::User);
        $usersTable->saveOrFail($user);
    }
}
