<?php
declare(strict_types=1);

namespace Admin\Controller;

use Cake\Http\Response;
use Cake\Routing\Attribute\Route;
use Cake\Routing\Attribute\Scope;

#[Scope('/admin', namePrefix: 'admin:')]
class UsersController extends AppController
{
    /**
     * Use user management as the initial admin page.
     */
    #[Route('/', name: 'home', methods: ['GET'])]
    public function home(): ?Response
    {
        return $this->redirect(['_name' => 'admin:users']);
    }

    /**
     * The island owns user management interactions.
     */
    #[Route('/users', name: 'users', methods: ['GET'])]
    public function index(): void
    {
    }
}
