<?php
/**
 * Main navigation bar element.
 *
 * @var \App\View\AppView $this
 */
?>
<div class="sticky top-0 z-40 border-b border-cake-red/70 bg-cake-red backdrop-blur-md">
    <div class="navbar container mx-auto px-4 sm:px-6 lg:px-8 gap-5">
        <div class="navbar-start">
            <div class="flex items-center gap-6">
                <a class="text-xl" href="<?= $this->Url->build('/') ?>">
                    <img src="/img/cake-logo.png" class="w-28" alt="CakePHP Logo"/>
                </a>
                <nav class="hidden items-center gap-1 lg:flex">
                    <?= $this->Html->link('Requirements', '/requirements', [
                        'class' => 'rounded-lg px-3 py-1.5 text-sm font-medium text-white/80 transition hover:bg-white/10 hover:text-white',
                    ]) ?>
                    <?= $this->Html->link('Docs', 'https://book.cakephp.org/', [
                        'target' => '_blank',
                        'rel' => 'noopener',
                        'class' => 'rounded-lg px-3 py-1.5 text-sm font-medium text-white/80 transition hover:bg-white/10 hover:text-white',
                    ]) ?>
                    <?= $this->Html->link('API', 'https://api.cakephp.org/', [
                        'target' => '_blank',
                        'rel' => 'noopener',
                        'class' => 'rounded-lg px-3 py-1.5 text-sm font-medium text-white/80 transition hover:bg-white/10 hover:text-white',
                    ]) ?>
                </nav>
            </div>
        </div>
        <div class="navbar-end">
            <?= $this->element('search') ?>
            <?php if ($this->Identity->isLoggedIn()) : ?>
                <div class="ml-4 dropdown dropdown-end">
                    <label tabindex="0" class="btn btn-ghost btn-circle avatar cursor-pointer" aria-label="<?= __('User menu') ?>">
                        <div class="w-9 rounded-full ring-2 ring-white/30 overflow-hidden">
                            <?= $this->User->avatar(80, ['class' => 'w-full h-full object-cover']) ?>
                        </div>
                    </label>
                    <ul tabindex="0" class="menu dropdown-content z-50 mt-3 w-56 rounded-2xl border border-base-200 bg-base-100 p-2 shadow-xl text-base-content">
                        <li class="px-3 py-2.5 pointer-events-none">
                            <div class="flex items-center gap-3">
                                <div class="w-8 h-8 rounded-full overflow-hidden flex-none">
                                    <?= $this->User->avatar(80, ['class' => 'w-full h-full object-cover', 'alt' => '']) ?>
                                </div>
                                <div class="flex flex-col min-w-0">
                                    <span class="text-sm font-semibold truncate"><?= h($this->User->displayName()) ?></span>
                                    <span class="text-xs text-base-content/50 truncate">@<?= h($this->User->username()) ?></span>
                                </div>
                            </div>
                        </li>
                        <li role="separator" class="p-0"><hr class="divider my-0" /></li>
                        <li>
                            <?= $this->Html->link('Sign out', [
                                'controller' => 'Users',
                                'action' => 'logout',
                            ]) ?>
                        </li>
                    </ul>
                </div>
            <?php else : ?>
                <div class="hidden lg:block lg:ml-4">
                    <?= $this->Form->postLink(
                        '<svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24" fill="currentColor"><path d="M12 0C5.37 0 0 5.37 0 12c0 5.31 3.435 9.795 8.205 11.385.6.105.825-.255.825-.57 0-.285-.015-1.23-.015-2.235-3.015.555-3.795-.735-4.035-1.41-.135-.345-.72-1.41-1.23-1.695-.42-.225-1.02-.78-.015-.795.945-.015 1.62.87 1.845 1.23 1.08 1.815 2.805 1.305 3.495.99.105-.78.42-1.305.765-1.605-2.67-.3-5.46-1.335-5.46-5.925 0-1.305.465-2.385 1.23-3.225-.12-.3-.54-1.53.12-3.18 0 0 1.005-.315 3.3 1.23.96-.27 1.98-.405 3-.405s2.04.135 3 .405c2.295-1.56 3.3-1.23 3.3-1.23.66 1.65.24 2.88.12 3.18.765.84 1.23 1.905 1.23 3.225 0 4.605-2.805 5.625-5.475 5.925.435.375.81 1.095.81 2.22 0 1.605-.015 2.895-.015 3.3 0 .315.225.69.825.57A12.02 12.02 0 0 0 24 12c0-6.63-5.37-12-12-12z"/></svg> Sign in'
                        ,
                        [
                            'prefix' => false,
                            'plugin' => 'ADmad/SocialAuth',
                            'controller' => 'Auth',
                            'action' => 'login',
                            'provider' => 'github',
                            '?' => ['redirect' => $this->request->getQuery('redirect')],
                        ],
                        [
                            'class' => 'btn btn-sm bg-white/10 text-white hover:bg-white/25 gap-2',
                            'escape' => false,
                        ],
                    ) ?>
                </div>
            <?php endif; ?>

            <!-- Mobile menu -->
            <div class="dropdown dropdown-end lg:hidden">
                <label tabindex="0" class="btn btn-ghost btn-circle hover:bg-white/10 text-white" aria-label="<?= __('Open menu') ?>">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                    </svg>
                </label>
                <ul tabindex="0" class="menu menu-sm dropdown-content z-50 mt-3 w-48 rounded-2xl border border-base-200 bg-base-100 p-2 shadow-xl text-base-content">
                    <li><?= $this->Html->link('Requirements', '/requirements') ?></li>
                    <li><?= $this->Html->link('Docs', 'https://book.cakephp.org/', ['target' => '_blank', 'rel' => 'noopener']) ?></li>
                    <li><?= $this->Html->link('API', 'https://api.cakephp.org/', ['target' => '_blank', 'rel' => 'noopener']) ?></li>
                    <?php if (!$this->Identity->isLoggedIn()) : ?>
                        <li role="separator" class="p-0"><hr class="divider my-0" /></li>
                        <li>
                            <?= $this->Form->postLink(
                                'Sign in with GitHub',
                                [
                                    'prefix' => false,
                                    'plugin' => 'ADmad/SocialAuth',
                                    'controller' => 'Auth',
                                    'action' => 'login',
                                    'provider' => 'github',
                                    '?' => ['redirect' => $this->request->getQuery('redirect')],
                                ],
                            ) ?>
                        </li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </div>
</div>
