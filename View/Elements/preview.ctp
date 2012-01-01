<?php
if (!isset($showDate)) {
	$showDate = true;
}
if (!isset($showDescription)) {
	$showDescription = true;
}
if (!isset($showMaintainer)) {
	$showMaintainer = true;
}
if (!isset($showReadMoreLink)) {
	$showReadMoreLink = true;
}

$title = $this->Html->link($this->Text->truncate($package['name'], 35), array(
	'plugin' => null,
	'controller' => 'packages', 
	'action' => 'view',
	$maintainer['username'], $package['name']
), array('title' => $package['name']));

?>
<div class="preview">
	<h3><?php echo $title; ?></h3>
	<div class="info">
		<?php
			if ($showMaintainer) {
				echo $this->Html->link($maintainer['username'], array(
					'controller' => 'maintainers',
					'action' => 'view',
					$maintainer['username']
				), array('class' => 'author'));
			}
		?>

		<?php if ($showDate) : ?>
			<span class="date">
				added on <?php echo $this->Time->format('Y-m-d', $package['created']); ?>
			</span>
		<?php endif; ?>

		<?php if ($showDescription) : ?>
			<p><?php echo h($package['description']) ?></p>
		<?php endif; ?>

		<?php if ($showReadMoreLink) : ?>
			<?php echo $this->Html->link('Read more', array(
				'plugin' => null,
				'controller' => 'packages',
				'action' => 'view',
				$maintainer['username'], $package['name']
			), array('class' => 'read-more')); ?>
		<?php endif; ?>

	</div>
</div>