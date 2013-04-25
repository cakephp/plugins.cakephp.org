<?php
$likeActive = '';
if ($package['Rating'] && $package['Rating']['value'] == 1) {
	$likeActive = 'is_activated';
}

?>

<section class="page-title clearfix">
	<h2><?php echo $package['Package']['name']; ?></h2>

	<!-- <ul class="activity button-group">
		<li>
			<?php echo $this->Html->link('Like', array(
				'controller' => 'packages', 'action' => 'like', $package['Package']['id']
			), array('class' => 'ajax-toggle button primary pill icon like is_like ' . $likeActive)); ?>
		</li>
		<li>
			<?php echo $this->Html->link('Bookmark', array(
				'controller' => 'packages', 'action' => 'bookmark', $package['Package']['id']
			), array('class' => 'ajax-toggle button primary pill icon tag is_tag ' . ($package['Favorite'] ? 'is_activated' : ''))); ?>
		</li>
	</ul> -->
</section>

<section class="summary clearfix">
	<?php echo $this->Resource->description($package['Package']['description']); ?>

	<p class="button">
		<?php echo $this->Html->link('Download Zip', array(
			'controller' => 'packages', 'action' => 'download', 'branch' => 'master', $package['Package']['id']
		), array('rel' => 'nofollow', 'class' => 'download-link', 'package-id' => $package['Package']['id'])); ?>
	</p>
</section>

<div class="package-info clearfix">
	<section class="package">
		<div class="package-section">
			<h3>Important Links</h3>

			<table class="data">
				<tbody>
					<tr>
						<td class="name">Github Url:</td>
						<td class="mobile-block">
							<?php echo $this->Resource->github_url(
								$package['Maintainer']['username'],
								$package['Package']['name']
							); ?>
						</td>
					</tr>
					<tr>
						<td class="name">Clone Url:</td>
						<td class="mobile-block">
							<?php echo $this->Resource->clone_url(
								$package['Maintainer']['username'],
								$package['Package']['name']
							); ?>
						</td>
					</tr>
					<?php // MVP For featured project ?>
					<?php if ($package['Package']['name'] == 'debug_kit') : ?>
					<tr>
						<td class="name">Blog Posts:</td>
						<td class="mobile-block">
							<a href="http://mark-story.com/posts/view/extending-debugkit-the-new-javascript-features" class="external blog-external">Extending DebugKit - The new Javascript features</a>
							<br />
							<a href="http://grahamweldon.com/posts/view/element-debugging-with-cakephps-debugkit" class="external blog-external">Element Debugging with CakePHP's DebugKit</a>
							<br />
							<a href="http://mark-story.com/posts/view/debugkit-updates" class="external blog-external">DebugKit Updates</a>
							<br />
							<a href="http://mark-story.com/posts/view/making-elements-drag-resizable-with-javascript" class="external blog-external">Making elements drag resizable with Javascript</a>
							<br />
							<a href="http://cakebaker.42dh.com/2008/10/30/debugkit-for-cakephp/" class="external blog-external">DebugKit for CakePHP</a>
						</td>
					</tr>
					<tr>
						<td class="name">Related:</td>
						<td class="mobile-block">
							<?php echo $this->Resource->github_url(
								'kamisama',
								'DebugKitEx',
								'DebugKitEx Plugin (Cache, NoSQL, and CakeResque panels)'
							); ?>
							<br />
							<?php echo $this->Resource->github_url(
								'steinkel',
								'LogMail',
								'LogMail Plugin (store and view sent emails in the database)'
							); ?>
							<br />
							<?php echo $this->Resource->github_url(
								'oldskool',
								'DebugPlus',
								'DebugPlus Plugin (Logs and Model viewing panels)'
							); ?>
						</td>
					</tr>
					<tr>
						<td class="name">Videos:</td>
						<td class="mobile-block">
							<a href="http://www.dailymotion.com/video/xxhv95_tuto-installer-le-plugin-debugkit-toolbar-cakephp-2-3-0_tech" class="external video-external">DebugKit Installation Video (in French)
							<br />
							<a href="http://www.youtube.com/watch?v=2jF_fSULzIY" class="external video-external">DebugKit Installation Video (in Arabic)</a>
						</td>
					</tr>
					<?php endif; ?>
				</tbody>
			</table>
		</div>

		<div class="rss package-section">
			<h3><?php echo __('Recent Activity'); ?></h3>
			<?php if (!empty($package['Rss']) && is_array($package['Rss'])) : ?>
				<ul>
					<?php foreach ($package['Rss'] as $entry) : ?>
						<li>
							<?php echo $this->Html->link(
								$this->Time->format('Y-m-d', $entry['updated']) . ' ' . $entry['title'],
								$entry['link'], array('target' => '_blank', 'rel' => 'nofollow', 'escape' => false)
							); ?>
						</li>
					<?php endforeach; ?>
				</ul>
			<?php endif; ?>
		</div>
	</section>

	<aside class="package-sidebar">
		<div class="stats package-section">
			<h3>Project Stats</h3>
			<table class="data">
				<tbody>
					<tr>
						<td class="name">Watchers:</td>
						<td>&nbsp;<?php echo $package['Package']['watchers'] ?></td>
					</tr>
					<tr>
						<td class="name">Issues:</td>
						<td>&nbsp;<?php echo $package['Package']['open_issues'] ?></td>
					</tr>
					<tr>
						<td class="name">Forks:</td>
						<td>&nbsp;<?php echo $package['Package']['forks'] ?></td>
					</tr>
					<tr>
						<td class="name">Maintainers:</td>
						<td>
							&nbsp;<?php echo $this->Resource->maintainer(
								$package['Maintainer']['username'],
								$package['Maintainer']['name']
							); ?>
						</td>
					</tr>
					<tr>
						<td class="name">Last Updated:</td>
						<td>
							&nbsp;<?php echo $this->Time->format('Y-m-d', $package['Package']['last_pushed_at']); ?>
						</td>
					</tr>
				</tbody>
			</table>
		</div>
	</aside>
</div>

<div id="disqus_thread"></div>
<noscript>Please enable JavaScript to view the <a href="http://disqus.com/?ref_noscript">comments powered by Disqus.</a></noscript>
<a href="http://disqus.com" class="dsq-brlink">blog comments powered by <span class="logo-disqus">Disqus</span></a>
