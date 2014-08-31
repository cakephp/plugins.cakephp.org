<?php
$likeActive = '';
if ($package['Rating'] && $package['Rating']['value'] == 1) {
	$likeActive = 'is_activated';
}

$this->Html->addCrumb('packages', array('action' => 'index'));
$this->Html->addCrumb($package['Maintainer']['username'], array(
	'plugin' => null,
	'controller' => 'maintainers',
	'action' => 'view',
	'id' => $package['Maintainer']['id'],
	'slug' => $package['Maintainer']['username'],
));
$this->Html->addCrumb($package['Package']['name'], $this->Resource->packageUrl($package['Package']));
?>

<?php echo $this->Html->getCrumbList(array('class' => 'breadcrumb')) ?>

<div class="row">
	<div class="col-md-6">
		<div class="package-description">
			<?php echo $this->Text->truncate(
				$this->Text->autoLink($package['Package']['description']), 100, array('html' => true)
			); ?>
		</div>

		<div class="panel panel-default">
			<div class="panel-heading clearfix">
				<h3 class="panel-title pull-left">
					Stats
				</h3>
				<small class="pull-right">Last fetched: <?php echo $this->Time->timeAgoInWords($package['Package']['modified']) ?></small>
			</div>
			<table class="table">
				<tbody>
					<tr>
						<td>Watchers</td>
						<td><?php echo $package['Package']['watchers'] ?></td>
					</tr>
					<tr>
						<td>Issues</td>
						<td><?php echo $package['Package']['open_issues'] ?></td>
					</tr>
					<tr>
						<td>Forks</td>
						<td><?php echo $package['Package']['forks'] ?></td>
					</tr>
					<tr>
						<td>Maintainer</td>
						<td><?php echo $package['Maintainer']['username'] ?></td>
					</tr>
					<tr>
						<td>Last Updated</td>
						<td><?php echo $this->Time->format('Y-m-d', $package['Package']['last_pushed_at']); ?></td>
					</tr>
					<tr>
						<td>Category</td>
						<td>
							<?php if (!empty($package['Category']['name'])) : ?>
								<?php echo $package['Category']['name']; ?>
							<?php endif; ?>
						</td>
					</tr>
				</tbody>
			</table>
		</div>

		<div class="panel panel-default">
			<div class="panel-heading clearfix">
				<h3 class="panel-title pull-left"><?php echo __('Recent Activity'); ?></h3>
			</div>

			<?php if (!empty($package['Rss']) && is_array($package['Rss'])) : ?>
				<table class="table">
					<thead>
						<tr>
							<th>Date</th>
							<th>Commit Message</th>
						</tr>
					</thead>
					<tbody>
						<?php foreach ($package['Rss'] as $entry) : ?>
							<tr>
								<td><?php echo $this->Time->format('Y-m-d', $entry['updated']); ?></td>
								<td>
									<?php echo $this->Html->link($entry['title'],
										$entry['link'], array('target' => '_blank', 'rel' => 'nofollow', 'escape' => false)
									); ?>
								</td>
							</tr>
						<?php endforeach; ?>
					</tbody>
				</table>
			<?php endif; ?>

		</div>
	</div>
	<div class="col-md-6">
		<div class="panel panel-default">
			<div class="panel-heading clearfix">
				<h3 class="panel-title pull-left">
					Repo Info
				</h3>
			</div>
			<table class="table">
				<tbody>
					<tr>
						<td>Repo Url</td>
						<td>
							<?php echo $this->Resource->github_url(
								$package['Maintainer']['username'],
								$package['Package']['name']
							); ?>
						</td>
					</tr>
					<tr>
						<td class="clone-url">Clone Url:</td>
						<td>
							<form role="form">
								<div class="form-group">
									<?php echo $this->Resource->clone_url(
										$package['Maintainer']['username'],
										$package['Package']['name']
									); ?>
								</div>
							</form>
						</td>
					</tr>
					<?php if ($this->Session->read('Auth.User')) : ?>
						<tr>
							<td>Disable:</td>
							<td>
								<?php echo $this->Html->link('Disable', array(
									'admin' => true, 'action' => 'disable', $package['Package']['id']
								)); ?>
							</td>
						</tr>
					<?php endif; ?>

					<?php // MVP For featured project ?>
					<?php if ($package['Package']['name'] == 'debug_kit') : ?>
						<tr>
							<td>Blog Posts:</td>
							<td>
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
							<td>Related:</td>
							<td>
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
							<td>Videos:</td>
							<td>
								<a href="http://www.dailymotion.com/video/xxhv95_tuto-installer-le-plugin-debugkit-toolbar-cakephp-2-3-0_tech" class="external video-external">DebugKit Installation Video (in French)
								<br />
								<a href="http://www.youtube.com/watch?v=2jF_fSULzIY" class="external video-external">DebugKit Installation Video (in Arabic)</a>
							</td>
						</tr>
					<?php endif; ?>
				</tbody>
			</table>
		</div>
		<div id="disqus_thread"></div>
	</div>
</div>

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

<noscript>Please enable JavaScript to view the <a href="http://disqus.com/?ref_noscript">comments powered by Disqus.</a></noscript>
<a href="http://disqus.com" class="dsq-brlink">blog comments powered by <span class="logo-disqus">Disqus</span></a>
