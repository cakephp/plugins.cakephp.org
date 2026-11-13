<?php
declare(strict_types=1);

/**
 * CakePHP(tm) : Rapid Development Framework (https://cakephp.org)
 * Copyright (c) Cake Software Foundation, Inc. (https://cakefoundation.org)
 *
 * Licensed under The MIT License
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright Copyright (c) Cake Software Foundation, Inc. (https://cakefoundation.org)
 * @link      https://cakephp.org CakePHP(tm) Project
 * @since     3.0.0
 * @license   https://opensource.org/licenses/mit-license.php MIT License
 */
namespace App\View;

use App\View\Helper\NumberHelper;
use Cake\View\View;

/**
 * Application View
 *
 * Your application's default view class
 *
 * @link https://book.cakephp.org/5/en/views.html#the-app-view
 * @property \Authentication\View\Helper\IdentityHelper $Identity
 * @property \App\View\Helper\UserHelper $User
 * @property \App\View\Helper\NumberHelper $Number
 * @property \Interact\View\Helper\InteractHelper $Interact
 * @property \CakeVite\View\Helper\ViteHelper $Vite
 */
class AppView extends View
{
    /**
     * Initialization hook method.
     *
     * Use this method to add common initialization code like adding helpers.
     *
     * e.g. `$this->addHelper('Html');`
     *
     * @return void
     */
    public function initialize(): void
    {
        $this->loadHelper('Paginator', ['templates' => 'paginator-templates']);
        $this->loadHelper('Form', ['templates' => 'form-templates']);
        $this->loadHelper('Html', ['templates' => 'html-templates']);
        $this->loadHelper('Number', ['className' => NumberHelper::class]);
        $this->loadHelper('Authentication.Identity');
        $this->loadHelper('User');
        $this->loadHelper('Interact.Interact');
        $this->loadHelper('CakeVite.Vite');
    }
}
