<?php
declare(strict_types=1);

namespace App\Test\TestCase\Controller;

use Cake\TestSuite\IntegrationTestTrait;
use Cake\TestSuite\TestCase;

/**
 * @link \App\Controller\PagesController
 */
class PagesControllerTest extends TestCase
{
    use IntegrationTestTrait;

    /**
     * @return void
     */
    public function testRequirementsPage(): void
    {
        $this->get('/requirements');

        $this->assertResponseOk();
        $this->assertResponseContains('Minimum requirements for being listed');
        $this->assertResponseContains('cakephp/cakephp');
        $this->assertResponseContains('cakephp/orm');
    }
}
