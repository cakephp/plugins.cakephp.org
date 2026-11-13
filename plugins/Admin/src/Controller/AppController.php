<?php
declare(strict_types=1);

namespace Admin\Controller;

use App\View\AppView;
use Cake\Controller\Controller;

/**
 * Admin pages use shared application helpers and a plugin-owned layout.
 */
class AppController extends Controller
{
    /**
     * Use the shared app view helpers with a plugin-owned layout.
     */
    public function initialize(): void
    {
        parent::initialize();
        $this->loadComponent('Flash');
        $this->loadComponent('Authentication.Authentication');
        $this->viewBuilder()->setClassName(AppView::class)->setLayout('Admin.admin');
    }
}
