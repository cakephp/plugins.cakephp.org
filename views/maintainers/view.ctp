<?php $this->Html->for_layout($maintainer['Maintainer']['username'], 'h2'); ?>
<?php $this->Html->for_layout($maintainer['Maintainer']['name'], 'h3'); ?>
<div class="grid_6 alpha">
	<div class="meta_maintainer border_radius">
		<?php echo $this->Html->image('https://secure.gravatar.com/avatar/' . $maintainer['Maintainer']['gravatar_id'], array('alt' => sprintf('Gravatar for %s', $maintainer['Maintainer']['username']), 'class' => 'gravatar')); ?>
		<dl><?php $i = 0; $class = ' class="altrow"';?>
			<dt<?php if ($i % 2 == 0) echo $class;?>><?php __('Github Username'); ?></dt>
			<dd<?php if ($i++ % 2 == 0) echo $class;?>>
				<?php echo $this->Html->link($maintainer['Maintainer']['username'], "http://github.com/{$maintainer['Maintainer']['username']}"); ?>
				&nbsp;
			</dd>
			<?php if (!empty($maintainer['Maintainer']['alias'])) : ?>
				<dt<?php if ($i % 2 == 0) echo $class;?>><?php __('Alias'); ?></dt>
				<dd<?php if ($i++ % 2 == 0) echo $class;?>>
					<?php echo $maintainer['Maintainer']['alias']; ?>
					&nbsp;
				</dd>
			<?php endif; ?>
			<?php if (!empty($maintainer['Maintainer']['url'])) : ?>
				<dt<?php if ($i % 2 == 0) echo $class;?>><?php __('Url'); ?></dt>
				<dd<?php if ($i++ % 2 == 0) echo $class;?>>
					<?php
						if (!strpos($maintainer['Maintainer']['url'], '://')) {
							$maintainer['Maintainer']['url'] = 'http://' . $maintainer['Maintainer']['url'];
						}
						echo  $this->Html->link($maintainer['Maintainer']['url'], $maintainer['Maintainer']['url']);
					?>
				&nbsp;
				</dd>
			<?php endif; ?>
			<?php if (!empty($maintainer['Maintainer']['twitter_username'])) : ?>
				<dt<?php if ($i % 2 == 0) echo $class;?>><?php __('Twitter Username'); ?></dt>
				<dd<?php if ($i++ % 2 == 0) echo $class;?>>
					<?php echo $maintainer['Maintainer']['twitter_username']; ?>
					&nbsp;
				</dd>
			<?php endif; ?>
		</dl>
		<div class="clear"></div>
	</div>
</div>
<div class="grid_6 omega">
	<div class="related">
		<h3><?php __('Packages');?></h3>
		<?php if (!empty($maintainer['Package'])):?>
		<?php $i = 0; foreach ($maintainer['Package'] as $package): ?>
			<div class="meta_listing">
				<?php echo $this->Resource->package($package['name'], $maintainer['Maintainer']['username']); ?><br />
				<p><?php echo $package['description'];?></p>
			</div>
		<?php endforeach; ?>
	<?php endif; ?>
	</div>
</div>
<div class="clear"></div>