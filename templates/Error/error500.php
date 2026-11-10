<?php
/**
 * @var \App\View\AppView $this
 * @var string $message
 * @var string $url
 * @var object $error
 */
use Cake\Core\Configure;
use Cake\Error\Debugger;

$this->setLayout('error');

if (Configure::read('debug')) :
    $this->setLayout('dev_error');

    $this->assign('title', $message);
    $this->assign('templateName', 'error500.php');

    $this->start('file');
    ?>
    <?php if ($error instanceof Error) : ?>
        <?php $file = $error->getFile() ?>
        <?php $line = $error->getLine() ?>
    <strong>Error in: </strong>
        <?= $this->Html->link(sprintf('%s, line %s', Debugger::trimPath($file), $line), Debugger::editorUrl($file, $line)); ?>
    <?php endif; ?>
    <?php
    echo $this->element('auto_table_warning');

    $this->end();
endif;
?>
<div class="error-page site-width">
    <h1><?= __d('cake', 'An Internal Error Has Occurred.') ?></h1>
    <p>
        <strong><?= __d('cake', 'Error') ?>: </strong>
        <?= h($message) ?>
    </p>
    <?= $this->Html->link(__('Browse plugins'), '/', ['class' => 'action-link']) ?>
</div>
