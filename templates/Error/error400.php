<?php
/**
 * @var \App\View\AppView $this
 * @var string $message
 * @var string $url
 */
use Cake\Core\Configure;

$this->setLayout('error');

if (Configure::read('debug')) :
    $this->setLayout('dev_error');

    $this->assign('title', $message);
    $this->assign('templateName', 'error400.php');

    $this->start('file');
    echo $this->element('auto_table_warning');
    $this->end();
endif;
?>
<div class="error-page site-width">
    <h1><?= h($message) ?></h1>
    <p>
        <strong><?= __d('cake', 'Error') ?>: </strong>
        <?= __d('cake', 'The requested address {0} was not found on this server.', "<strong>'" . h($url) . "'</strong>") ?>
    </p>
    <?= $this->Html->link(__('Browse plugins'), '/', ['class' => 'action-link']) ?>
</div>
