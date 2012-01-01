<h2>
	<?php echo $this->Resource->gravatar(
		$maintainer['Maintainer']['username'],
		$maintainer['Maintainer']['gravatar_id']
	); ?>
	<span><?php echo $this->Resource->maintainer_name(
		$maintainer['Maintainer']['username'],
		$maintainer['Maintainer']['name']
	); ?></span>
</h2>

<?php if ($maintainer['Maintainer']['has_summary']): ?>
	<section class="summary clearfix">
		<?php if (!empty($maintainer['Maintainer']['url'])) : ?>
			<p><strong>Url:</strong>&nbsp;<?php echo $this->Html->link(
					$maintainer['Maintainer']['url'],
					$maintainer['Maintainer']['url']
				); ?>
			</p>
		<?php endif; ?>

		<?php if (!empty($maintainer['Maintainer']['company'])) : ?>
			<p><strong>Company:</strong>&nbsp;<?php echo $maintainer['Maintainer']['company']; ?></p>
		<?php endif; ?>

		<?php if (!empty($maintainer['Maintainer']['location'])) : ?>
			<p><strong>Location:</strong>&nbsp;<?php echo $maintainer['Maintainer']['location']; ?></p>
		<?php endif; ?>

		<?php if (!empty($maintainer['Maintainer']['twitter_username'])) : ?>
			<p><strong>Twitter:</strong>&nbsp;<?php echo $maintainer['Maintainer']['twitter_username']; ?></p>
		<?php endif; ?>

		<?php if (!empty($maintainer['Maintainer']['package_count'])) : ?>
			<p><strong>Packages:</strong>&nbsp;<?php echo $maintainer['Maintainer']['package_count']; ?></p>
		<?php endif; ?>

	</section>
<?php endif; ?>

<section class="packages">
	<?php if (!empty($maintainer['Package'])):?>
		<?php foreach ($maintainer['Package'] as $package): ?>
			<article>
				<?php echo $this->element('preview', array(
					'package' => $package,
					'maintainer' => $maintainer['Maintainer'],
					'showMaintainer' => false,
					'showReadMoreLink' => false,
					'showDate' => false,
				)); ?>
			</article>
		<?php endforeach; ?>
	<?php endif; ?>
</section>