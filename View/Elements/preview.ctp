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

if (!isset($showLastPushedAt)) {
	$showLastPushedAt = true;
}

$hasDetails =  $showMaintainer || $showDate || $showLastPushedAt;

$title = $this->Html->link($this->Text->truncate($package['name'], 35), array(
	'plugin' => null,
	'controller' => 'packages', 
	'action' => 'view',
	$maintainer['username'], $package['name']
), array('title' => $package['name']));

$package['description'] = trim($package['description']);
if (empty($package['description'])) {
	$package['description'] = 'No description available';
}

?>
<div class="preview">
	<h3><?php echo $title; ?></h3>
	<div class="info">
		<?php if ($showDescription) : ?>
			<p class="description"><?php echo h($package['description']) ?></p>
		<?php endif; ?>


		<?php if ($hasDetails) : ?>
			<div class="details">

				<?php
					if ($showMaintainer) {
						echo 'by ' . $this->Html->link($maintainer['username'], array(
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

				<?php if ($showLastPushedAt) : ?>
					<span class="date">
						last updated at <?php echo $this->Time->format('Y-m-d', $package['last_pushed_at']); ?>
					</span>
				<?php endif; ?>

			</div>
		<?php endif; ?>

	</div>
</div>