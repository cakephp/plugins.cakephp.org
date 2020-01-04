<?php
$hideTitle = $context->deleted ? __('Show') : __('Hide');
$featureTitle = $context->featured ? __('Unfeature') : __('Feature');

echo $this->ButtonGroup->render(implode('', [
    $this->Html->link($featureTitle, ['action' => 'toggleFeature', $context->id], ['class' => 'btn btn-success']),
    $this->Html->link($hideTitle, ['action' => 'toggleHide', $context->id], ['class' => 'btn btn-warning']),
    $this->Html->link('Classify Now', ['action' => 'classify', $context->id], ['class' => 'btn btn-default']),
]), ['style' => 'width:260px']);
