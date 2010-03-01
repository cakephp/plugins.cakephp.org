<?php $this->Html->h2( __('Tag', true));?>
<dl><?php $i = 0; $class = ' class="altrow"';?>
	<dt<?php if ($i % 2 == 0) echo $class;?>><?php __('Name'); ?></dt>
	<dd<?php if ($i++ % 2 == 0) echo $class;?>>
		<?php echo $tag['Tag']['name']; ?>
		&nbsp;
	</dd>
</dl>
<div class="related">
	<h3><?php printf(__('Related %s', true), __('Packages', true));?></h3>
	<?php if (!empty($tag['Package'])):?>
	<table cellpadding = "0" cellspacing = "0">
	<tr>
		<th><?php __('Name'); ?></th>
	</tr>
	<?php
		$i = 0;
		foreach ($tag['Package'] as $package):
			$class = null;
			if ($i++ % 2 == 0) {
				$class = ' class="altrow"';
			}
		?>
		<tr<?php echo $class;?>>
			<td>
				<?php echo $this->Resource->package($package['name'], $package['Maintainer']['username']); ?>
				<br />
				<?php echo $this->Resource->description($package['description']);?>&nbsp;
			</td>
		</tr>
	<?php endforeach; ?>
	</table>
<?php endif; ?>
</div>