<?php
declare(strict_types=1);

namespace App\Test\Fixture;

use Cake\TestSuite\Fixture\TestFixture;

class UsersFixture extends TestFixture
{
    /**
     * Deterministic GitHub accounts for access-management tests.
     */
    public function init(): void
    {
        $this->records = [
            ['id' => 1, 'username' => 'admin', 'email' => 'admin@example.com', 'first_name' => 'Site', 'last_name' => 'Admin', 'role' => 'admin', 'is_active' => true, 'created' => '2026-01-01 00:00:00'],
            ['id' => 2, 'username' => 'member', 'email' => 'member@example.com', 'first_name' => 'Normal', 'last_name' => 'Member', 'role' => 'user', 'is_active' => true, 'created' => '2026-01-02 00:00:00'],
            ['id' => 3, 'username' => 'suspended', 'email' => 'suspended@example.com', 'role' => 'user', 'is_active' => false, 'created' => '2026-01-03 00:00:00'],
        ];
        parent::init();
    }
}
