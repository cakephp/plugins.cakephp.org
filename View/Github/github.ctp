<h2>
	<?php echo $user['User']['login']; ?>
</h2>

<?php echo $this->Session->flash(); ?>

<article class="meta-data">
	<?php echo $this->Html->link(sprintf(__('Add %s'), __('Maintainer')), array('action' => 'add_maintainer', $user['User']['login'])); ?>

	<div class="meta-maintainer border-radius">
		<dl><?php $i = 0; $class = ' class="altrow"';?>
			<dt<?php if ($i % 2 == 0) echo $class;?>><?php __('Gravatar ID'); ?></dt>
			<dd<?php if ($i++ % 2 == 0) echo $class;?>>
				<?php echo $user['User']['gravatar_id']; ?>
				&nbsp;
			</dd>
			<dt<?php if ($i % 2 == 0) echo $class;?>><?php __('Login'); ?></dt>
			<dd<?php if ($i++ % 2 == 0) echo $class;?>>
				<?php echo $user['User']['login']; ?>
				&nbsp;
			</dd>
			<dt<?php if ($i % 2 == 0) echo $class;?>><?php __('Name'); ?></dt>
			<dd<?php if ($i++ % 2 == 0) echo $class;?>>
				<?php echo (isset($user['User']['name'])) ? $user['User']['name'] : ''; ?>
				&nbsp;
			</dd>
			<dt<?php if ($i % 2 == 0) echo $class;?>><?php __('Email'); ?></dt>
			<dd<?php if ($i++ % 2 == 0) echo $class;?>>
				<?php echo (isset($user['User']['email'])) ? $user['User']['email'] : ''; ?>
				&nbsp;
			</dd>
			<dt<?php if ($i % 2 == 0) echo $class;?>><?php __('Url'); ?></dt>
			<dd<?php if ($i++ % 2 == 0) echo $class;?>>
				<?php echo (isset($user['User']['blog'])) ? $user['User']['blog'] : ''; ?>
				&nbsp;
			</dd>
			<dt<?php if ($i % 2 == 0) echo $class;?>><?php __('Company'); ?></dt>
			<dd<?php if ($i++ % 2 == 0) echo $class;?>>
				<?php echo (isset($user['User']['company'])) ? $user['User']['company'] : ''; ?>
				&nbsp;
			</dd>
			<dt<?php if ($i % 2 == 0) echo $class;?>><?php __('Location'); ?></dt>
			<dd<?php if ($i++ % 2 == 0) echo $class;?>>
				<?php echo (isset($user['User']['location'])) ? $user['User']['location'] : ''; ?>
				&nbsp;
			</dd>
		</dl>
		<div class="clear"></div>
	</div>
</article>