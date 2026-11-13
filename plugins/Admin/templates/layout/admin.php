<?php
/**
 * @var \App\View\AppView $this
 */
$theme = $this->getRequest()->getCookie('theme') === 'cakephp-dark' ? 'cakephp-dark' : 'cakephp';
$controller = $this->getRequest()->getParam('controller');
?>
<!DOCTYPE html>
<html lang="en" data-theme="<?= h($theme) ?>">
<head>
    <?= $this->Html->charset() ?>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex,nofollow">
    <title><?= h($this->fetch('title')) ?> · CakePHP Plugins Admin</title>
    <?= $this->Html->meta('icon') ?>
    <?= $this->Vite->css(['files' => ['resources/css/style.css'], 'block' => false, 'attributes' => ['x-interact:asset' => 'reload', 'timestamp' => false]]) ?>
    <?= $this->Vite->script(['files' => ['resources/js/app.js'], 'block' => false, 'attributes' => ['x-interact:asset' => 'reload', 'timestamp' => false]]) ?>
    <?= $this->fetch('script') ?>
</head>
<body class="admin-shell bg-base-200 text-base-content">
    <a class="skip-link" href="#admin-content"><?= __('Skip to content') ?></a>
    <div class="drawer lg:drawer-open" x-data="adminNavigation" @keydown.escape="open = false">
        <input id="admin-drawer" type="checkbox" class="drawer-toggle" x-model="open">
        <div class="drawer-content min-w-0">
            <header class="navbar gap-3 border-b border-base-300 bg-base-100 px-4 md:px-8">
                <button type="button" class="btn btn-square btn-ghost lg:hidden" aria-label="<?= __('Open navigation') ?>"
                        aria-controls="admin-sidebar" :aria-expanded="open" @click="open = !open">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M4 6h16M4 12h16M4 18h16"/></svg>
                </button>
                <span class="font-semibold"><?= __('Administration') ?></span>
                <div class="ml-auto flex items-center gap-3">
                    <?= $this->Interact->render('ThemeToggle', key: 'admin-theme') ?>
                    <span class="hidden text-sm sm:inline">@<?= h($this->User->username()) ?></span>
                    <?= $this->Html->link(__('Sign out'), ['plugin' => false, 'prefix' => false, 'controller' => 'Users', 'action' => 'logout'], ['class' => 'btn btn-ghost btn-sm']) ?>
                </div>
            </header>
            <main id="admin-content" class="mx-auto w-full max-w-7xl p-4 md:p-8" tabindex="-1">
                <?= $this->Flash->render() ?>
                <?= $this->fetch('content') ?>
            </main>
        </div>
        <div class="drawer-side z-40">
            <label for="admin-drawer" aria-label="<?= __('Close navigation') ?>" class="drawer-overlay"></label>
            <aside id="admin-sidebar" class="flex min-h-full w-64 flex-col border-r border-base-300 bg-base-100 p-5">
                <a href="/" class="mb-1 text-lg font-bold text-primary">CakePHP Plugins</a>
                <p class="mb-8 text-sm opacity-60"><?= __('Admin backend') ?></p>
                <nav aria-label="<?= __('Admin navigation') ?>">
                    <ul class="menu w-full gap-1 p-0">
                        <?php foreach (['Users' => ['admin:users', __('Users')], 'FeaturedPlugins' => ['admin:featured', __('Featured plugins')]] as $name => [$route, $label]) : ?>
                            <li><?= $this->Html->link($label, ['_name' => $route], [
                                'class' => $controller === $name ? 'menu-active' : '',
                                'aria-current' => $controller === $name ? 'page' : null,
                            ]) ?></li>
                        <?php endforeach; ?>
                        <li><a href="/" class="mt-5"><?= __('View site') ?> <span aria-hidden="true">↗</span></a></li>
                    </ul>
                </nav>
            </aside>
        </div>
    </div>
</body>
</html>
