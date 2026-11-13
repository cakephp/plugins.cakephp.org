<?php
declare(strict_types=1);

namespace Admin\Controller;

use Cake\Http\Response;

class UsersController extends AppController
{
    /**
     * Use user management as the initial admin page.
     */
    public function home(): ?Response
    {
        return $this->redirect(['_name' => 'admin:users']);
    }

    /**
     * The island owns user management interactions.
     */
    public function index(): void
    {
    }
}
