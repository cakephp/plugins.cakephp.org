<?php
$title = $this->Html->link(
	$this->Text->truncate($data['Package']['name'], 35),
	array('plugin' => null, 'controller' => 'packages', 'action' => 'view', $data['Maintainer']['username'], $data['Package']['name']),
	array('title' => $data['Package']['name'], 'class' => 'info')
);
$description = h($data['Package']['description']);
?>
<div class="preview">
	<div class="info">
	<?php
		echo $this->Html->div('watchers',
			$this->Html->tag('span', $data['Package']['watchers'])
		);
		echo $this->Html->div('rating',
			$this->Html->tag('span', 3)
		);
		echo $this->Html->div('summary',
			$this->Html->tag(
				'span',
				$this->Html->para('', $description) .
				$this->element('packages/labels', array('package' => $data, 'full' => false)) .
				'<div class="border"></div><div class="arrow"></div>'
			)
		);
	?>
	</div>
	<h3>
		<?php echo $title; ?>
	</h3>
	<div class="author">
		<?php
			$author = $this->Html->link($data['Maintainer']['username'], array(
				'controller' => 'maintainers',
				'action' => 'view',
				$data['Maintainer']['username']
			));
		?>
		<?php printf(__('by %s'), $author)?>
	</div>
	<?php if (!empty($description)) : ?>
		<div class="summary">
			<?php echo $this->Text->truncate($description, 120); ?>
		</div>
	<?php endif;?>
	<ul class="labels">
		<?php echo $this->element('packages/labels', array('package' => $data, 'full' => true, 'limit' => 6)); ?>
	</ul>
</div>