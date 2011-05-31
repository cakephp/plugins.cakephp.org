<?php $this->Html->for_layout('h2', '<span class="package_name">' . $package['Package']['name'] . '</span>'); ?>
<?php $this->Html->for_layout('h3', "by " . $this->Resource->maintainer($package['Maintainer']['username'], $package['Maintainer']['username'])); ?>
<?php $this->Html->for_layout('title', sprintf("%s by %s", 
    $package['Package']['name'],
    ($package['Maintainer']['username']) ? $package['Maintainer']['username'] : $package['Maintainer']['username'])); ?>
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
<div class="description">
	<p><?php echo $package['Package']['description']; ?></p>
	<?php echo $this->element('rss_reader', array(
		'url' =>  "https://github.com/{$package['Maintainer']['username']}/{$package['Package']['name']}/commits/master.atom"))?>
</div>
<div class="meta-data">
	<div class="meta-package border-radius">
		<div style="icons-container">
			<?php echo $this->element('icons', array(
			    'package' => $package['Package'], 'search' => false, 'meta' => false)); ?>
		</div>
		<dl><?php $i = 0; $class = ' class="altrow"';?>
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