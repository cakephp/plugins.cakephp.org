<?php $this->Html->h2($maintainer['Maintainer']['name'], $maintainer['Maintainer']['username']);?>
<h4><?php echo $this->Maintainer->edit($maintainer['Maintainer']['id'], $maintainer['Maintainer']['username']); ?></h4>
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
		<?php
		if (!empty($maintainer['Maintainer']['url'])) {
			if (!strpos($maintainer['Maintainer']['url'], '://')) {
				$maintainer['Maintainer']['url'] = 'http://' . $maintainer['Maintainer']['url'];
			}
			echo  $this->Html->link($maintainer['Maintainer']['url'], $maintainer['Maintainer']['url']);
		} else {
			echo '&nbsp;';
		}
		?>
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
				<?php echo $this->Resource->package($package['name'], $maintainer['Maintainer']['username']); ?><br />
				<?php echo $package['description'];?>
			</td>
		</tr>
	<?php endforeach; ?>
	</table>
<?php endif; ?>
</div>
<div class="related">
	<?php echo $this->element('rss_reader', array(
		'user' => true,
			'url' => 'http://github.com/' . $maintainer['Maintainer']['username'] . '.atom'))?>
</div>