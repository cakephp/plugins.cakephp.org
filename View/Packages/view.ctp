<h2><?php echo $package['Package']['name']; ?></h2>

<section class="summary clearfix">
	<?php echo $this->Resource->description($package['Package']['description']); ?>

	<p class="button">
		<?php echo $this->Html->link('Download Zip', array(
			'controller' => 'packages', 'action' => 'download', 'branch' => 'master', $package['Package']['id']
		), array('rel' => 'nofollow')); ?>
	</p>
</section>

<div class="clearfix">
	<section class="package">
		<h3>Installation</h3>

		<div>
			<h4>Github URL</h4>
			<?php echo $this->Resource->github_url($package['Maintainer']['username'], $package['Package']['name']); ?>
		</div>

		<div>
			<h4>Clone Url</h4>
			<?php echo $this->Resource->clone_url($package['Maintainer']['username'], $package['Package']['name']); ?>
		</div>

		<?php if (!empty($rss) && is_array($rss)) : ?>
		<div class="rss">
			<h3><?php echo __('Recent Activity'); ?></h3>
			<ul>
				<?php foreach ($rss as $entry) : ?>
					<li>
						<?php echo $this->Html->link(
							$this->Time->format('Y-m-d', $entry['Entry']['updated']) . ' ' . $entry['Entry']['title'],
							$entry['Entry']['link'], array('target' => '_blank', 'rel' => 'nofollow')
						); ?>
					</li>
				<?php endforeach; ?>
			</ul>
			<?php endif; ?>
		</div>
	</section>

	<aside class="stats">
		<h3>Project Stats</h3>
		<p><strong>Watchers:</strong>&nbsp;<?php echo $package['Package']['watchers']; ?></p>
		<p><strong>Issues:</strong>&nbsp;<?php echo $package['Package']['open_issues']; ?></p>
		<p><strong>Forks:</strong>&nbsp;<?php echo $package['Package']['forks']; ?></p>
		<p><strong>Maintainer:</strong>&nbsp;<?php echo $this->Resource->maintainer(
				$package['Maintainer']['username'],
				$package['Maintainer']['name']
			); ?>
		</p>
		<p><strong>Last Updated:</strong> 
			<?php echo $this->Time->format('Y-m-d', $package['Package']['last_pushed_at']); ?>
		</p>
	</aside>
</div>