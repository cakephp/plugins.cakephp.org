<?php $this->set('_bodyClass', $_bodyClass . ' page-form'); ?>
<?php echo $this->Form->create('Package', array(
	'class' => 'edit-package-form clearfix',
)); ?>
	<?php echo $this->Form->input('id'); ?>

	<h2>Editing Package :: <?php echo $this->request->data['Package']['name']; ?></h2>

	<section>
		<?php
		echo $this->Form->input('name', array(
			'div' => ['class' => 'form-group'],
			'class' => 'form-control',
			'placeholder' => 'Package Name',
		));
		echo $this->Form->input('description', array(
			'div' => ['class' => 'form-group'],
			'class' => 'form-control',
			'placeholder' => 'Package Description',
		));
		echo $this->Form->input('tags', array(
			'div' => ['class' => 'form-group'],
			'class' => 'form-control',
			'placeholder' => 'Comma Seperated List of Tags',
		));
		echo $this->Form->input('category_id', array(
			'div' => ['class' => 'form-group'],
			'class' => 'form-control',
			'options' => $categories,
		));
		?>
	</section>

	<section>
		<?php
		echo $this->Form->input('repository_url', array(
			'div' => ['class' => 'form-group'],
			'class' => 'form-control',
			'placeholder' => 'http://github.com/user/repo',
		));
		echo $this->Form->input('bakery_article', array(
			'div' => ['class' => 'form-group'],
			'class' => 'form-control',
			'placeholder' => 'http://bakery.cakephp.org/articles/user/',
		));
		echo $this->Form->input('homepage', array(
			'div' => ['class' => 'form-group'],
			'class' => 'form-control',
			'placeholder' => 'http://example.com',
		));
		?>
	</section>

	<section class="attributes">
		<h3>Attributes</h3>
		<?php
		sort($validTypes);
		foreach ($validTypes as $key => $type) {
			$checkbox = $this->Form->input('Package.contains.' . $key, array(
				'div' => false,
				'type' => 'checkbox',
				'label' => false, // 'Contains ' . Inflector::humanize($type),
				'value' => $type,
				'checked' => $this->request->data['Package']['contains_' . $type],
			));
			$label = $this->Form->label('Package.contains.' . $key, $checkbox . 'Contains ' . Inflector::humanize($type));
			echo '<div class="checkbox">' . $label . '</div>';
		}
		?>
	</section>

	<section class="actions">
		<hr/>
		<?php
		echo $this->Form->button(__('Save'), array('class' => 'btn btn-primary', 'div' => false));
		echo '&nbsp;&nbsp;&nbsp;';
		if (!empty($this->request->data['Package']['deleted'])) {
			echo $this->Html->link(
				__('Enable Package'),
				array('action' => 'disable', $this->request->data['Package']['id']),
				array('class' => 'btn btn-success')
			);
		} else {
			echo $this->Html->link(
				__('Disable Package'),
				array('action' => 'disable', $this->request->data['Package']['id']),
				array('class' => 'btn btn-warning')
			);
		}
		echo $this->Html->link(
			__('Back to Packages'),
			array('action' => 'index'),
			array('class' => 'btn btn-default', 'style' => 'float:right;')
		);
		?>
	</section>
<?php echo $this->Form->end(); ?>
