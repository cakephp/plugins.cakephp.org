<?php $this->set('_bodyClass', $_bodyClass . ' page-form'); ?>
<h2 class="search-title">Suggest a New Package for CakePackages</h2>

<?php echo $this->Session->flash(); ?>

<article>
	<p class="description">Use this form to suggest new packages from GitHub:</p>

	<?php
		echo $this->Form->create('Package', array(
			'class' => 'PackageSuggestForm form-inline',
			'inputDefaults' => array('label' => false),
			'role' => 'form',
			'url' => array('controller' => 'packages', 'action' => 'suggest'),
		));
	?>
	<div class="form-group">
		<?php
			echo $this->Form->input('Package.github', array(
				'class' => 'form-control github',
				'div' => false,
				'label' => false,
				'placeholder' => __('github repository url'),
			));
		?>
	</div>
	<?php
		echo $this->Form->button(__('Suggest!'), array(
			'class' => 'btn btn-default',
			'div' => false,
		));

		echo $this->Form->end();
	?>
</article>
