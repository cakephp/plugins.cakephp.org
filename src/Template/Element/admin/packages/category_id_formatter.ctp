<?php

echo $this->Form->create(null, ['class' => 'form-inline', 'url' => ['action' => 'categorize', $context->id]]);

echo $this->Form->input('id', ['label' => false, 'value' => $context->id, 'type' => 'hidden']);
echo $this->Form->input('category_id', ['label' => false, 'value' => $value, 'empty' => '(no category specified)', 'style' => 'width:236px']);

echo $this->Form->submit(null, ['class' => 'btn-primary pull-right']);
echo $this->Form->end();
