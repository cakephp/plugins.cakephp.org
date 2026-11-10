<?php
/**
 * @var \App\View\AppView $this
 * @var bool|null $minimal
 */
$minimal = $minimal ?? false;
$requirements = $this->getRequest()->getPath() === '/requirements';
$loggedIn = !$minimal && $this->Identity->isLoggedIn();
$links = [
    ['label' => __('Browse plugins'), 'url' => '/', 'active' => !$requirements],
    ['label' => __('Listing requirements'), 'url' => '/requirements', 'active' => $requirements],
];
$loginUrl = [
    'prefix' => false, 'plugin' => 'ADmad/SocialAuth', 'controller' => 'Auth', 'action' => 'login', 'provider' => 'github',
    '?' => ['redirect' => $this->getRequest()->getQuery('redirect')],
];
?>
<header class="site-header" x-data="siteNavigation" @keydown.escape="closeMenu($event)" @click="followLink($event)">
    <div class="site-nav site-width">
        <?= $this->element('brand') ?>
        <nav class="desktop-nav" aria-label="<?= __('Main navigation') ?>">
            <?php foreach ($links as $link) : ?>
                <?= $this->Html->link($link['label'], $link['url'], [
                    'class' => 'nav-link',
                    'aria-current' => $link['active'] ? 'page' : null,
                    'x-interact:navigate.hover' => '',
                ]) ?>
            <?php endforeach; ?>
        </nav>
        <?php if (!$minimal) : ?>
            <div class="header-actions">
                <?= $this->Interact->render('ThemeToggle', key: 'theme-toggle') ?>
                <?php if ($loggedIn) : ?>
                    <details class="navigation-menu desktop-account">
                        <summary class="icon-button" aria-label="<?= __('User menu') ?>">
                            <?= $this->User->avatar(80, ['class' => 'user-avatar']) ?>
                        </summary>
                        <div class="menu-panel">
                            <p class="menu-identity">
                                <?= h($this->User->displayName()) ?>
                                <span>@<?= h($this->User->username()) ?></span>
                            </p>
                            <?= $this->Html->link(__('Sign out'), ['controller' => 'Users', 'action' => 'logout'], ['class' => 'nav-link']) ?>
                        </div>
                    </details>
                <?php else : ?>
                    <div class="desktop-account">
                        <?= $this->Form->postLink(
                            $this->element('github_icon') . '<span>' . __('Sign in') . '</span>',
                            $loginUrl,
                            ['class' => 'action-link', 'escape' => false],
                        ) ?>
                    </div>
                <?php endif; ?>
                <details class="navigation-menu mobile-menu">
                    <summary class="icon-button" aria-label="<?= __('Open menu') ?>">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                            <path d="M4 6h16M4 12h16M4 18h16" stroke-linecap="round"></path>
                        </svg>
                    </summary>
                    <nav class="menu-panel" aria-label="<?= __('Mobile navigation') ?>">
                        <?php foreach ($links as $link) : ?>
                            <?= $this->Html->link($link['label'], $link['url'], [
                                'class' => 'nav-link',
                                'aria-current' => $link['active'] ? 'page' : null,
                                'x-interact:navigate.hover' => '',
                            ]) ?>
                        <?php endforeach; ?>
                        <?php if ($loggedIn) : ?>
                            <p class="menu-identity"><?= h($this->User->displayName()) ?></p>
                            <?= $this->Html->link(__('Sign out'), ['controller' => 'Users', 'action' => 'logout'], ['class' => 'nav-link']) ?>
                        <?php else : ?>
                            <?= $this->Form->postLink(__('Sign in with GitHub'), $loginUrl, ['class' => 'action-link']) ?>
                        <?php endif; ?>
                    </nav>
                </details>
            </div>
        <?php endif; ?>
    </div>
</header>
