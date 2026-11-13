<?php
declare(strict_types=1);

namespace App\Test\TestCase\Event;

use ADmad\SocialAuth\Middleware\SocialAuthMiddleware;
use ADmad\SocialAuth\Model\Entity\SocialProfile;
use App\Event\AfterGithubIdentify;
use App\Model\Enum\UserRole;
use App\Model\Table\UsersTable;
use Cake\Event\Event;
use Cake\Http\TestSuite\HttpClientTrait;
use Cake\TestSuite\TestCase;
use Override;
use PHPUnit\Framework\Attributes\DataProvider;
use SocialConnect\OAuth2\AccessToken;

class AfterGithubIdentifyTest extends TestCase
{
    use HttpClientTrait;

    /**
     * @var list<string>
     */
    #[Override]
    protected array $fixtures = ['app.Users'];

    /**
     * Login synchronizes both the persisted account and the identified entity.
     */
    #[DataProvider('membershipProvider')]
    public function testSynchronizesMembership(
        int $id,
        int $status,
        string $body,
        UserRole $expectedRole,
        bool $expectedDeveloper,
    ): void {
        $users = $this->fetchTable(UsersTable::class);
        $user = $users->get($id);
        $user->set('is_cakephp_dev', $user->role === UserRole::Admin);
        $users->saveOrFail($user);
        $user->set('social_profile', new SocialProfile([
            'access_token' => new AccessToken(['access_token' => 'test-token']),
        ]));
        $this->mockClientGet(
            'https://api.github.com/user/memberships/orgs/cakephp',
            $this->newClientResponse($status, ['Content-Type: application/json'], $body),
        );

        $listener = new AfterGithubIdentify();
        $listener->setTableLocator($this->getTableLocator());
        $listener->afterIdentify(new Event(SocialAuthMiddleware::EVENT_AFTER_IDENTIFY), $user);

        $this->assertSame($expectedRole, $user->role);
        $this->assertSame($expectedDeveloper, $user->is_cakephp_dev);
        $saved = $users->get($id);
        $this->assertSame($expectedRole, $saved->role);
        $this->assertSame($expectedDeveloper, $saved->is_cakephp_dev);
        $this->assertTrue($saved->is_active);
    }

    /**
     * @return array<string, array{int, int, string, UserRole, bool}>
     */
    public static function membershipProvider(): array
    {
        return [
            'promote member' => [2, 200, '{"state":"active","role":"member"}', UserRole::Admin, true],
            'retain member admin' => [1, 200, '{"state":"active"}', UserRole::Admin, true],
            'demote last admin' => [1, 404, '{"message":"Not Found"}', UserRole::User, false],
            'retain nonmember user' => [2, 404, '{"message":"Not Found"}', UserRole::User, false],
            'pending invitation is not membership' => [1, 200, '{"state":"pending"}', UserRole::User, false],
            'API outage preserves access' => [1, 503, '{}', UserRole::Admin, true],
            'forbidden preserves access' => [1, 403, '{}', UserRole::Admin, true],
            'failed request cannot promote' => [2, 401, '{}', UserRole::User, false],
        ];
    }
}
