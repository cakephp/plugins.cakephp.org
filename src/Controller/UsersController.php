<?php
declare(strict_types=1);

namespace App\Controller;

use Cake\Http\Response;

/**
 * Users Controller
 *
 * @property \App\Model\Table\UsersTable $Users
 */
class UsersController extends AppController
{
    /**
     * @return \Cake\Http\Response
     */
    public function logout(): Response
    {
        $this->Authentication->logout();

        return $this->redirect(['controller' => 'Packages', 'action' => 'index']);
    }
}
