<?php $this->Html->h2($package['Package']['name']);?>
<p><?php echo $package['Package']['description']; ?></p>
<dl><?php $i = 0; $class = ' class="altrow"';?>
	<dt<?php if ($i % 2 == 0) echo $class;?>><?php __('Maintainer'); ?></dt>
	<dd<?php if ($i++ % 2 == 0) echo $class;?>>
		<?php echo $this->Resource->maintainer($package['Maintainer']['name'], $package['Maintainer']['username']); ?>
	</dd>
	<dt<?php if ($i % 2 == 0) echo $class;?>><?php __('Bakery Article'); ?></dt>
	<dd<?php if ($i++ % 2 == 0) echo $class;?>>
		<?php echo $package['Package']['bakery_article']; ?>
		&nbsp;
	</dd>
	<dt<?php if ($i % 2 == 0) echo $class;?>><?php __('Clone Url'); ?></dt>
	<dd<?php if ($i++ % 2 == 0) echo $class;?>>
		<?php echo $this->Resource->repository($package['Maintainer']['username'], $package['Package']['name']); ?>
		&nbsp;
	</dd>
	<dt<?php if ($i % 2 == 0) echo $class;?>><?php __('Homepage'); ?></dt>
	<dd<?php if ($i++ % 2 == 0) echo $class;?>>
		<?php echo $this->Html->link($package['Package']['homepage'], $package['Package']['homepage']); ?>
		&nbsp;
	</dd>
</dl>
<div class="related">
	<?php if (!empty($package['Tag'])):?>
		<h3><?php printf(__('Related %s', true), __('Tags', true));?></h3>
		<table cellpadding = "0" cellspacing = "0">
		<tr>
			<th><?php __('Tag'); ?></th>
		</tr>
		<?php
			$i = 0;
			foreach ($package['Tag'] as $tag):
				$class = null;
				if ($i++ % 2 == 0) {
					$class = ' class="altrow"';
				}
			?>
			<tr<?php echo $class;?>>
				<td class="actions">
					<?php echo $this->Html->link($tag['name'], array('controller' => 'tags', 'action' => 'index', $tag['name'])); ?>
				</td>
			</tr>
		<?php endforeach; ?>
		</table>
	<?php endif; ?>
</div>
<?php if (Configure::read() != 0) : ?>
<div class="actions">
	<h3><?php __('Actions'); ?></h3>
	<ul>
		<li><?php echo $this->Html->link(sprintf(__('Edit %s', true), __('Package', true)), array('action' => 'edit', $package['Package']['id'])); ?> </li>
		<li><?php echo $this->Html->link(sprintf(__('Delete %s', true), __('Package', true)), array('action' => 'delete', $package['Package']['id']), null, sprintf(__('Are you sure you want to delete # %s?', true), $package['Package']['id'])); ?> </li>
	</ul>
</div>
<?php endif; ?>