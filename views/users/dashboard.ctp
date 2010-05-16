<?php echo $this->Html->h2(__('Dashboard', true)); ?>
<?php echo $this->Session->flash(); ?>
<?php if (Authsome::get('group') == 'admin') : ?>
<ul class="actions">
	<li>
		<?php echo $this->Html->link(__('Github User Index', true),
				array('controller' => 'github', 'action' => 'index')); ?>
	</li>
</ul>
<?php endif; ?>
<ul class="actions">
	<li><?php echo $this->Clearance->link(__('Change Password', true),
				array('controller' => 'users', 'action' => 'change_password')); ?></li>
	<li><?php echo $this->Clearance->link(__('Logout', true),
				array('controller' => 'users', 'action' => 'logout')); ?></li>
</ul>