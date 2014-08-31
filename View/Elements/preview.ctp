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
	'id' => $package['id'], 'slug' => $package['name']
), array('title' => $package['name']));

?>
<div class="article">
	<div class="preview">
		<h3><?php echo $title; ?></h3>
		<div class="info">
			<?php if ($showDescription) : ?>
				<p class="description"><?php echo h($package['description']) ?></p>
			<?php endif; ?>


			<?php if ($hasDetails) : ?>
				<div class="details">

					<?php if ($showMaintainer) : ?>
						<strong>By:</strong> <?php echo $this->Html->link($maintainer['username'], array(
								'controller' => 'maintainers',
								'action' => 'view',
								'id' => $maintainer['id'],
								'slug' => $maintainer['username']
							), array('class' => 'author')); ?>
					<?php endif; ?>

					<?php if ($this->Session->read('Auth.User')) : ?>
						<strong>Github:</strong> <?php echo $this->Resource->github_url(
								$maintainer['username'],
								$package['name']
							); ?>
						<?php echo $this->Html->link('Disable', array('admin' => true, 'action' => 'disable', $package['id'])); ?>
					<?php endif; ?>

					<?php if ($showDate) : ?>
						<span class="date">
							<strong>Added On:</strong> <?php echo $this->Time->format('Y-m-d', $package['created']); ?>
						</span>
					<?php endif; ?>

					<?php if ($showLastPushedAt) : ?>
						<span class="date">
							<strong>Last Updated At:</strong> <?php echo $this->Time->format('Y-m-d', $package['last_pushed_at']); ?>
						</span>
					<?php endif; ?>

				</div>
			<?php endif; ?>

		</div>
	</div>
</div>
