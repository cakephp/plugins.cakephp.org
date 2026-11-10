<?php
/**
 * @var \App\View\AppView $this
 * @var array $params
 * @var string $message
 */
echo $this->element('flash/message', ['params' => $params, 'message' => $message, 'type' => 'success']);
