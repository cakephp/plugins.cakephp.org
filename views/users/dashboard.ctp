<h2 class="secondary-title">
	Dashboard
</h2>

<?php echo $this->Session->flash(); ?>

<article>
	<?php if (Authsome::get('group') == 'admin') : ?>
	<div class="meta_listing information">
		<div><?php echo $this->Html->link(__('Github User Index', true),
				array('controller' => 'github', 'action' => 'index')); ?></div>
	<?php endif; ?>
		<div><?php echo $this->Clearance->link(__('Change Password', true),
				array('controller' => 'users', 'action' => 'change_password')); ?></div>
		<div><?php echo $this->Clearance->link(__('Logout', true),
				array('controller' => 'users', 'action' => 'logout')); ?></div>
	</div>
</article>