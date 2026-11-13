<?php
/**
 * @var array<string, list<string>> $errors
 * @var string $message
 */
?>
<?php if ($message !== '') : ?>
    <div role="status" class="alert alert-success mb-5"><?= h($message) ?></div>
<?php endif; ?>
<?php if ($errors !== []) : ?>
    <div role="alert" class="alert alert-error mb-5">
        <ul><?php foreach ($errors as $messages) :
            ?><?php foreach ($messages as $error) : ?>
            <li><?= h($error) ?></li>
            <?php endforeach; ?><?php
            endforeach; ?></ul>
    </div>
<?php endif; ?>
<div role="alert" class="alert alert-error mb-5" x-show="$server.error" x-cloak>
    <?= __('The request failed. Reload this page to check your access and try again.') ?>
</div>
<span class="sr-only" role="status" x-show="$server.pending()" x-cloak><?= __('Saving or loading…') ?></span>
