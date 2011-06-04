<?php  echo $this->Html->h2(__('Maintainer', true));?></h2>
<div class="meta-data">
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
</div>
<div class="related">
	<h3><?php __('Actions'); ?></h3>
	<div class="meta-listing">
		<?php echo $this->Clearance->link(sprintf(__('Add %s', true), __('Maintainer', true)), array('action' => 'add_maintainer', $user['User']['login'])); ?>
	</div>
</div>
<div class="clear"></div>