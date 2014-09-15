<div class="col-lg-12 user-details">
	<div class="user-image">
		<?php echo $this->Resource->gravatar(
			$maintainer['Maintainer']['username'],
			$maintainer['Maintainer']['avatar_url'],
			$maintainer['Maintainer']['gravatar_id']
		); ?>
	</div>
	<div class="user-info-block">
		<div class="user-heading">
			<h3>
				<?php
					if (strlen($maintainer['Maintainer']['name'])) {
						echo $maintainer['Maintainer']['username'] . '&nbsp;(' . $maintainer['Maintainer']['name'] . ')';
					} else {
						echo $maintainer['Maintainer']['username'];
					}
				?>
			</h3>
			<span class="help-block">
				<?php if (!empty($maintainer['Maintainer']['location'])) : ?>
					<p><?php echo $maintainer['Maintainer']['location']; ?></p>
				<?php endif; ?>
			</span>
		</div>
    </div>
</div>

<section class="col-lg-12 packages">
	<?php
		if (!empty($maintainer['Package'])) {
			$packages = array();
			foreach ($maintainer['Package'] as $package) {
				$packages[] = array(
					'Maintainer' => $maintainer['Maintainer'],
					'Package' => $package,
					'Category' => $package['Category'],
				);
			}
			echo $this->element('package-results', array('packages' => $packages));
		}
	?>
</section>
