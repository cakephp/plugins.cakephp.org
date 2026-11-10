<?php
/**
 * @var \App\View\AppView $this
 */
$this->assign('title', 'Listing Requirements');
?>
<section class="docs-page site-width">
    <header class="page-intro">
        <h1><?= __('Listing requirements') ?></h1>
        <p><?= __('How packages become part of the CakePHP plugin directory.') ?></p>
    </header>
    <section class="docs-section">
        <h2><?= __('What your package needs') ?></h2>
        <p>Packages are discovered from Packagist. They must be active CakePHP plugins with an explicit Composer dependency on CakePHP or an official split package.</p>
        <ul>
            <li>Published on Packagist with the type <?= $this->element('code_chip', ['text' => 'cakephp-plugin']) ?>.</li>
            <li>Not marked as abandoned.</li>
            <li>At least 10 downloads on packagist.org.</li>
            <li>
                A <?= $this->element('code_chip', ['text' => 'require']) ?> dependency on
                <?= $this->element('code_chip', ['text' => 'cakephp/cakephp']) ?> or an official split package,
                such as <?= $this->element('code_chip', ['text' => 'cakephp/orm']) ?>.
            </li>
        </ul>
    </section>
    <section class="docs-section">
        <h2><?= __('What does not qualify') ?></h2>
        <p>A generic library depending only on PSR interfaces does not qualify. A README that mentions CakePHP support is not enough without a Composer dependency.</p>
        <p class="muted">Only <?= $this->element('code_chip', ['text' => 'require']) ?> dependencies are considered for compatibility tags.</p>
    </section>
    <section class="docs-section">
        <h2><?= __('Composer examples') ?></h2>
        <div class="composer-examples">
            <div>
                <h3><?= __('Framework package') ?></h3>
                <pre><code>{
  "name": "myusername/my-package-name",
  "type": "cakephp-plugin",
  "require": {
    "cakephp/cakephp": "^5.0"
  }
}</code></pre>
            </div>
            <div>
                <h3><?= __('Split package') ?></h3>
                <pre><code>{
  "name": "myusername/my-package-name",
  "type": "cakephp-plugin",
  "require": {
    "cakephp/orm": "^5.0"
  }
}</code></pre>
            </div>
        </div>
    </section>
    <?= $this->Html->link(__('Browse plugins'), '/', ['class' => 'action-link', 'x-interact:navigate' => '']) ?>
</section>
