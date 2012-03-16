<?php $this->set('_bodyClass', $_bodyClass . ' page-form'); ?>
<?php echo $this->Form->create('Package', array(
	'class' => 'edit-package-form clearfix',
)); ?>
	<?php echo $this->Form->input('id'); ?>

	<h2>Editing Package :: <?php echo $this->request->data['Package']['name']; ?></h2>

	<section>
		<?php
		echo $this->Form->input('name', array(
			'placeholder' => 'Package Name',
		));
		echo $this->Form->input('description', array(
			'placeholder' => 'Package Description',
		));
		echo $this->Form->input('tags', array(
			'placeholder' => 'Comma Seperated List of Tags',
		));
		echo $this->Form->input('category_id', array(
			'options' => $categories,
		));
		?>
	</section>

	<section>
		<?php
		echo $this->Form->input('repository_url', array(
			'placeholder' => 'http://github.com/user/repo',
		));
		echo $this->Form->input('bakery_article', array(
			'placeholder' => 'http://bakery.cakephp.org/articles/user/',
		));
		echo $this->Form->input('homepage', array(
			'placeholder' => 'http://example.com',
		));
		?>
	</section>
	
	<section class="attributes">
		<h3>Attributes</h3>
		<?php
		$attrs = array(
			'contains_model', 'contains_view', 'contains_controller',
			'contains_behavior', 'contains_helper', 'contains_component',
			'contains_shell', 'contains_theme', 'contains_datasource',
			'contains_vendor', 'contains_test', 'contains_lib', 'contains_resource',
			'contains_config', 'contains_app',
		);
		sort($attrs);
		foreach ($attrs as $field) {
			echo $this->Form->input($field);
		}
		?>
	</section>
	
	<section class="actions">
		<hr/>
		<?php
		echo $this->Form->button(__('Save'), array('class' => 'button solid-green primary', 'div' => false));
		if (!empty($this->request->data['Package']['deleted'])) {
			echo $this->Html->link(
				__('Enable Package'),
				array('action' => 'disable', $this->request->data['Package']['id']),
				array('class' => 'button green')
			);
		} else {
			echo $this->Html->link(
				__('Disable Package'),
				array('action' => 'disable', $this->request->data['Package']['id']),
				array('class' => 'button red')
			);
		}
		echo $this->Html->link(
			__('Back to Packages'),
			array('action' => 'index'),
			array('class' => 'button', 'style' => 'float:right;')
		);
		?>
	</section>
<?php echo $this->Form->end(); ?>