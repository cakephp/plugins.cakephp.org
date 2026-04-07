<?php
declare(strict_types=1);

namespace App\Test\TestCase\View\Helper;

use App\View\Helper\UserHelper;
use ArrayObject;
use Authentication\Identity;
use Cake\Http\ServerRequest;
use Cake\TestSuite\TestCase;
use Cake\View\View;

class UserHelperTest extends TestCase
{
    protected UserHelper $User;

    protected function createHelper(?array $identityData = null): UserHelper
    {
        $request = new ServerRequest();
        if ($identityData !== null) {
            $identity = new Identity(new ArrayObject($identityData));
            $request = $request->withAttribute('identity', $identity);
        }
        $view = new View($request);

        return new UserHelper($view);
    }

    public function testUsernameLoggedIn(): void
    {
        $helper = $this->createHelper(['username' => 'markstory']);
        $this->assertSame('markstory', $helper->username());
    }

    public function testUsernameNotLoggedIn(): void
    {
        $helper = $this->createHelper();
        $this->assertNull($helper->username());
    }

    public function testDisplayNameFullName(): void
    {
        $helper = $this->createHelper([
            'username' => 'markstory',
            'first_name' => 'Mark',
            'last_name' => 'Story',
        ]);
        $this->assertSame('Mark Story', $helper->displayName());
    }

    public function testDisplayNameFirstNameOnly(): void
    {
        $helper = $this->createHelper([
            'username' => 'markstory',
            'first_name' => 'Mark',
            'last_name' => '',
        ]);
        $this->assertSame('Mark', $helper->displayName());
    }

    public function testDisplayNameFallsBackToUsername(): void
    {
        $helper = $this->createHelper([
            'username' => 'markstory',
            'first_name' => '',
            'last_name' => '',
        ]);
        $this->assertSame('markstory', $helper->displayName());
    }

    public function testDisplayNameNotLoggedIn(): void
    {
        $helper = $this->createHelper();
        $this->assertNull($helper->displayName());
    }

    public function testAvatarUrl(): void
    {
        $helper = $this->createHelper(['username' => 'markstory']);
        $this->assertSame(
            'https://github.com/markstory.png?size=80',
            $helper->avatarUrl(),
        );
    }

    public function testAvatarUrlCustomSize(): void
    {
        $helper = $this->createHelper(['username' => 'markstory']);
        $this->assertSame(
            'https://github.com/markstory.png?size=200',
            $helper->avatarUrl(200),
        );
    }

    public function testAvatarUrlNotLoggedIn(): void
    {
        $helper = $this->createHelper();
        $this->assertNull($helper->avatarUrl());
    }

    public function testAvatar(): void
    {
        $helper = $this->createHelper(['username' => 'markstory']);
        $result = $helper->avatar();

        $this->assertStringContainsString('<img', $result);
        $this->assertStringContainsString('https://github.com/markstory.png?size=80', $result);
        $this->assertStringContainsString('alt="markstory"', $result);
        $this->assertStringContainsString('loading="lazy"', $result);
        $this->assertStringContainsString('referrerpolicy="no-referrer"', $result);
    }

    public function testAvatarCustomAttrs(): void
    {
        $helper = $this->createHelper(['username' => 'markstory']);
        $result = $helper->avatar(80, ['class' => 'w-full', 'alt' => '']);

        $this->assertStringContainsString('class="w-full"', $result);
        $this->assertStringContainsString('alt=""', $result);
    }

    public function testAvatarNotLoggedIn(): void
    {
        $helper = $this->createHelper();
        $this->assertNull($helper->avatar());
    }
}
