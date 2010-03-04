<?php echo $this->Html->h2(__('Dashboard', true)); ?>
<?php echo $this->Session->flash(); ?>
<?php if (Authsome::get('group') == 'administrator') : ?>
<ul class="actions">
	<li>
		<?php echo $this->Html->link(__('Moderation Index', true),
				array('controller' => 'administrations', 'action' => 'index')); ?>
	</li>
</ul>
<?php endif; ?>
<ul class="actions">
	<li><?php echo $this->Html->link(__('Change Password', true),
				array('controller' => 'users', 'action' => 'change_password')); ?></li>
	<li><?php echo $this->Html->link(__('Logout', true),
				array('controller' => 'users', 'action' => 'logout')); ?></li>
</ul>