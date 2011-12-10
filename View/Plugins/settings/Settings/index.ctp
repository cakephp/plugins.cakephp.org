<?php $this->Html->h2(__('Site Settings')); ?>
<?php echo $this->Form->create('Setting'); ?>
	<?php foreach($this->data as $key => &$setting) : ?>
		<?php echo $this->Form->input("Setting." . $setting['Setting']['id'] . ".id", array(
			'value' => $setting['Setting']['id'])); ?>
		<?php $params = array(
			'label' => $setting['Setting']['title'],
			'type' => $setting['Setting']['input_type'],
			'value' => $setting['Setting']['value']); ?>
		<?php if (isset($selects[$setting['Setting']['key']])) : ?>
			<?php $params = array_merge(array('options' => $selects[$setting['Setting']['key']]), $params); ?>
		<?php endif; ?>
		<?php echo $this->Form->input("Setting." . $setting['Setting']['id'] . ".value", $params); ?>
	<?php endforeach; ?>
	<?php echo $this->Form->submit(__('Change Settings'),
		array('div' => 'submit cancel')); ?> or 
	<?php echo $this->Clearance->link(__('go to dashboard'),
		array('controller' => 'users', 'action' => 'dashboard'),
		array('class' => 'cancel-action')); ?>
<?php echo $this->Form->end(); ?>