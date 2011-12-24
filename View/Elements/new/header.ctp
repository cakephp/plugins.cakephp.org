<header>
	<div class="container">
		<h1><?php echo $this->Html->link(__('Package Indexer'), '/'); ?></h1>

		<?php
			echo $this->Form->create(false, array(
				'url' => array(
					'admin' => false,
					'plugin' => null,
					'controller' => 'packages',
					'action' => 'index'
				),
				'inputDefaults' => array('div' => false, 'label' => false),
			));
			echo $this->Form->input('query', array('placeholder' => 'search for packages'));
			echo $this->Form->submit(__('search'), array('div' => false));
			echo $this->Form->end();
		?>
	</div>
</header>