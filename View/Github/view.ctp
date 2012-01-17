<h2>
	Maintainer
</h2>

<?php echo $this->Session->flash(); ?>

<article>
	<dl class="description"><?php $i = 0; $class = ' class="altrow"';?>
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
</article>
<br />
<article>
	<div class="meta-package border-radius">
		<h3><?php  __('New Packages');?></h3>
		<?php if (!empty($repositories)) : ?>
			<table cellpadding="0" cellspacing="0">
			<tr>
					<th><?php __('Title'); ?></th>
					<th class="actions"><?php __('Actions');?></th>
			</tr>
				<?php $i = 0; foreach ($repositories as $repository): ?>
				<tr<?php echo ($i++ % 2 == 0) ? ' class="altrow"' : '';?>>
					<td>
						<?php echo $this->Html->link($repository['Repository']['name'], "http://github.com/{$user['User']['login']}/{$repository['Repository']['name']}"); ?>
					</td>
					<td class="actions">
						<?php echo $this->Html->link(__('Add'), array(
							'action' => 'add_package', $user['User']['login'], $repository['Repository']['name'])); ?>
					</td>
				</tr>
				<?php endforeach; ?>
			</table>
		<?php else : ?>
			<div id="flashMessage" class="notice">
				<?php echo $user['User']['login'] . __(' has no new packages!'); ?>
			</div>
		<?php endif; ?>
	</div>
	<br />

	<div class="meta-package border-radius">
		<h3><?php  __('Existing Packages');?></h3>
		<?php if (!empty($existing['Package'])) : ?>
			<table cellpadding="0" cellspacing="0">
			<tr>
					<th><?php __('Title'); ?></th>
			</tr>
				<?php $i = 0; foreach ($existing['Package'] as $package): ?>
				<tr<?php echo ($i++ % 2 == 0) ? ' class="altrow"' : '';?>>
					<td><?php echo $this->Github->package($package['name'], $user['User']['login']); ?></td>
				</tr>
				<?php endforeach; ?>
			</table>
		<?php else : ?>
			<div id="flashMessage" class="notice">
				<?php echo $user['User']['login'] . __(' has no existing packages!'); ?>
			</div>
		<?php endif; ?>
	</div>
</article>