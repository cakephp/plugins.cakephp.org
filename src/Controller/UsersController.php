<?php
declare(strict_types=1);

namespace App\Controller;

use Cake\Http\Response;
use Cake\Routing\Attribute\Route;

/**
 * Users Controller
 *
 * @property \App\Model\Table\UsersTable $Users
 */
class UsersController extends AppController
{
    /**
     * End the current session and return to the directory.
     */
    #[Route('/users/logout', methods: ['GET', 'POST'])]
    public function logout(): ?Response
    {
        $this->Authentication->logout();

        return $this->redirect(['controller' => 'Packages', 'action' => 'index']);
    }
}
