<?php $this->Html->h2($maintainer['Maintainer']['name'], $maintainer['Maintainer']['username']);?>
<dl><?php $i = 0; $class = ' class="altrow"';?>
	<dt<?php if ($i % 2 == 0) echo $class;?>><?php __('Github Username'); ?></dt>
	<dd<?php if ($i++ % 2 == 0) echo $class;?>>
		<?php echo $this->Html->link($maintainer['Maintainer']['username'], "http://github.com/{$maintainer['Maintainer']['username']}"); ?>
		&nbsp;
	</dd>
	<dt<?php if ($i % 2 == 0) echo $class;?>><?php __('Alias'); ?></dt>
	<dd<?php if ($i++ % 2 == 0) echo $class;?>>
		<?php echo $maintainer['Maintainer']['alias']; ?>
		&nbsp;
	</dd>
	<dt<?php if ($i % 2 == 0) echo $class;?>><?php __('Url'); ?></dt>
	<dd<?php if ($i++ % 2 == 0) echo $class;?>>
		<?php echo $this->Html->link($maintainer['Maintainer']['url'], $maintainer['Maintainer']['url']); ?>
		&nbsp;
	</dd>
	<dt<?php if ($i % 2 == 0) echo $class;?>><?php __('Twitter Username'); ?></dt>
	<dd<?php if ($i++ % 2 == 0) echo $class;?>>
		<?php echo $maintainer['Maintainer']['twitter_username']; ?>
		&nbsp;
	</dd>
</dl>
<div class="related">
	<h3><?php __('Packages');?></h3>
	<?php if (!empty($maintainer['Package'])):?>
	<table cellpadding = "0" cellspacing = "0">
	<?php $i = 0; foreach ($maintainer['Package'] as $package): ?>
		<tr<?php echo ($i++ % 2 == 0) ? ' class="altrow"' : '';?>>
			<td>
				<?php echo $this->Resource->package($maintainer['Maintainer']['username'], $package['name']); ?><br />
				<?php echo $package['description'];?>
			</td>
		</tr>
	<?php endforeach; ?>
	</table>
<?php endif; ?>
</div>
<div class="actions">
	<h3><?php __('Actions'); ?></h3>
	<ul>
		<li><?php echo $this->Maintainer->edit($maintainer['Maintainer']['id'], $maintainer['Maintainer']['username']); ?></li>
	</ul>
</div>