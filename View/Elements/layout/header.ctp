<header id="header">
	<div class="top-bar">
		<div class="inner-container">
			<div class="login">
				<?php 
					if (!$this->Session->check('Auth.User.id')) {
						echo $this->Html->link(__('Login'), array('admin' => false, 'plugin' => false, 'controller' => 'pkg_users', 'action' => 'login')) . ' ';
						echo $this->Html->link(__('Register'), array('admin' => false, 'plugin' => false, 'controller' => 'users', 'action' => 'register')) . ' ';
					} else {
						echo $this->Gravatar->image($userData['email'], array('size' => 20));
						echo $this->Html->link($userData['username'], array('admin' => false, 'plugin' => false, 'controller' => 'users', 'action' => 'edit')) . ' ';
						echo $this->Html->link(__('Logout'), array('admin' => false, 'plugin' => false, 'controller' => 'users', 'action' => 'logout')) . ' ';
					}
				?>
			</div>
		</div>
	</div>

	<div class="inner-container nav-bar">
		<h1><?php echo $this->Html->link(__('Package Indexer'), '/'); ?></h1>
		<?php echo $this->element('layout/nav'); ?>
		<?php
			echo $this->Form->create('Video', array(
				'id' => 'global-search',
				'url' => array(
					'admin' => false,
					'plugin' => null,
					'controller' => 'videos',
					'action' => 'index')));
			echo $this->Form->input('search', array(
				'div' => false,
				'label' => false));
			echo $this->Form->submit(__('search', true), array(
				'div' => false));
			echo $this->Form->end();
		?>
	</div>

</header>