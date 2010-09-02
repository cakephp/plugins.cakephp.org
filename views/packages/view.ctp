<?php $this->Html->for_layout($package['Package']['name'], 'h2'); ?>
<?php $this->Html->for_layout("by " . $this->Resource->maintainer($package['Maintainer']['username'], $package['Maintainer']['username']), 'h3'); ?>
<?php $this->Html->for_layout("{$package['Package']['name']} | ", 'title'); ?>
<h4>
	<?php
		echo $this->Clearance->link(sprintf(__('Edit %s', true), __('Package', true)), array(
			'action' => 'edit', $package['Package']['id']));
	?>
</h4>
<h4>
	<?php
		echo $this->Clearance->link(sprintf(__('Delete %s', true), __('Package', true)), array(
			'action' => 'delete', $package['Package']['id']), null,
			sprintf(__('Are you sure you want to delete # %s?', true), $package['Package']['id']
	)); ?>
</h4>
<div class="grid_6 alpha">
	<p class="description"><?php echo $package['Package']['description']; ?></p>
	<?php echo $this->element('rss_reader', array('url' => $package['Package']['homepage'] . '/commits/master.atom'))?>
</div>
<div class="grid_6 omega">
	<div class="meta_package border_radius">
		<div style="margin-left:auto;margin-right:auto">
			<?php echo $this->element('icons', array('package' => $package['Package'])); ?>
		</div>
		<dl><?php $i = 0; $class = ' class="altrow"';?>
			<dt<?php if ($i % 2 == 0) echo $class;?>><?php __('Maintainer'); ?></dt>
			<dd<?php if ($i++ % 2 == 0) echo $class;?>>
				<?php echo $this->Resource->maintainer($package['Maintainer']['name'], $package['Maintainer']['username']); ?>
			</dd>
			<dt<?php if ($i % 2 == 0) echo $class;?>><?php __('Github Url'); ?></dt>
			<dd<?php if ($i++ % 2 == 0) echo $class;?>>
				<?php echo $this->Resource->repository($package['Maintainer']['username'], $package['Package']['name']); ?>
				&nbsp;
			</dd>
			<dt<?php if ($i % 2 == 0) echo $class;?>><?php __('Clone Url'); ?></dt>
			<dd<?php if ($i++ % 2 == 0) echo $class;?>>
				<?php echo $this->Resource->clone_url($package['Maintainer']['username'], $package['Package']['name']); ?>
				&nbsp;
			</dd>
		</dl>
	</div>
</div>
<div class="clear"></div>