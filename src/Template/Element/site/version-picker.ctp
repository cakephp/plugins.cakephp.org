<?php
if (!$this->request->getSession()->read('Auth.User') || !empty($package->deleted)) {
    return;
}
echo $this->Html->link(
    __('Disable'),
    ['prefix' => 'admin', 'controller' => 'packages', 'action' => 'disable', $package->id],
    ['class' => 'btn btn-primary btn-sm'],
    'Are you sure you want to disable package #' . $package->id . '?'
);
echo $this->Html->link(
    __('1.2'),
    ['prefix' => 'admin', 'controller' => 'packages', 'action' => 'version', $package->id, '1.2'],
    ['class' => 'btn btn-danger btn-sm']
);
echo $this->Html->link(
    __('1.3'),
    ['prefix' => 'admin', 'controller' => 'packages', 'action' => 'version', $package->id, '1.3'],
    ['class' => 'btn btn-warning btn-sm']
);
echo $this->Html->link(
    __('2.x'),
    ['prefix' => 'admin', 'controller' => 'packages', 'action' => 'version', $package->id, '2'],
    ['class' => 'btn btn-info btn-sm']
);
echo $this->Html->link(
    __('3.x'),
    ['prefix' => 'admin', 'controller' => 'packages', 'action' => 'version', $package->id, '3'],
    ['class' => 'btn btn-success btn-sm']
);
echo $this->Html->link(
    __('Clear versions'),
    ['prefix' => 'admin', 'controller' => 'packages', 'action' => 'clear_version', $package->id],
    ['class' => 'btn btn-default btn-sm']
);
