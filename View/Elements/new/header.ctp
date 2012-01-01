<header>
	<div class="container">
		<h1><?php echo $this->Html->link($siteTitle, '/'); ?></h1>

		<nav class="main-nav">
			<ul>
				<li>
					<?php echo $this->Html->link('Packages', array('controller' => 'packages', 'action' => 'index')); ?>
				</li>
				<li>
					<?php echo $this->Html->link('Suggest', array('controller' => 'packages', 'action' => 'suggest')); ?>
				</li>
				<?php if (!empty($userData)) : ?>
					<li>
						<?php echo $this->Html->link('Logout', array('controller' => 'users', 'action' => 'logout')); ?>
					</li>
				<?php endif; ?>
			</ul>
		</nav>

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