<?php
declare(strict_types=1);

namespace App\Event;

use ADmad\SocialAuth\Middleware\SocialAuthMiddleware;
use App\Model\Entity\User;
use App\Model\Enum\UserRole;
use Cake\Event\EventInterface;
use Cake\Event\EventListenerInterface;
use Cake\Http\Client;
use Cake\ORM\Locator\LocatorAwareTrait;

class AfterGithubIdentify implements EventListenerInterface
{
    use LocatorAwareTrait;

    /**
     * Returns a list of events this object is interested in.
     *
     * @return array
     */
    public function implementedEvents(): array
    {
        return [
            SocialAuthMiddleware::EVENT_AFTER_IDENTIFY => 'afterIdentify',
        ];
    }

    /**
     * After identify callback.
     *
     * @param \Cake\Event\Event $event The event instance.
     * @param \App\Model\Entity\User $user The user entity.
     * @return void
     */
    public function afterIdentify(EventInterface $event, User $user): void
    {
        $token = $user->social_profile->access_token->getToken();
        if (!$token) {
            return;
        }

        $http = new Client([
            'headers' => [
                'Authorization' => 'Bearer ' . $token,
                'Accept' => 'application/vnd.github+json',
                'User-Agent' => 'plugins.cakephp.org',
            ],
        ]);

        $response = $http->get('https://api.github.com/user/memberships/orgs/cakephp');
        if (!$response->isOk() && $response->getStatusCode() !== 404) {
            return;
        }

        $isCakePHPDev = $response->isOk() && ($response->getJson()['state'] ?? null) === 'active';

        $usersTable = $this->getTableLocator()->get('Users');
        // Organization membership is authoritative, even for the last administrator.
        $user->set('is_cakephp_dev', $isCakePHPDev);
        $user->set('role', $isCakePHPDev ? UserRole::Admin : UserRole::User);
        $usersTable->saveOrFail($user);
    }
}
