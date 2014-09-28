<div class="panel panel-default search-legend">
	<div class="panel-heading clearfix">
		<h3 class="panel-title pull-left">Missing a package from Github?</h3>
	</div>
	<div class="panel-body">
		<p>Let us know about it!</p>

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
	</div>
</div>
