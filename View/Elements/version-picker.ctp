<?php
if (!$this->Session->read('Auth.User') || !empty($package['Package']['deleted'])) {
    return;
}
echo $this->Html->link(
    __('Disable'),
    array('admin' => true, 'controller' => 'packages', 'action' => 'disable', $package['Package']['id']),
    array('class' => 'btn btn-primary btn-sm'),
    'Are you sure you want to disable package #' . $package['Package']['id'] . '?'
);
echo $this->Html->link(
    __('1.2'),
    array('admin' => true, 'controller' => 'packages', 'action' => 'version', $package['Package']['id'], '1.2'),
    array('class' => 'btn btn-danger btn-sm')
);
echo $this->Html->link(
    __('1.3'),
    array('admin' => true, 'controller' => 'packages', 'action' => 'version', $package['Package']['id'], '1.3'),
    array('class' => 'btn btn-warning btn-sm')
);
echo $this->Html->link(
    __('2.x'),
    array('admin' => true, 'controller' => 'packages', 'action' => 'version', $package['Package']['id'], '2'),
    array('class' => 'btn btn-info btn-sm')
);
echo $this->Html->link(
    __('3.x'),
    array('admin' => true, 'controller' => 'packages', 'action' => 'version', $package['Package']['id'], '3'),
    array('class' => 'btn btn-success btn-sm')
);
?>
