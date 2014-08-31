<table class="table table-hover table-condensed packages">
	<thead>
		<tr>
			<th>Package</th>
			<th>Watchers</th>
		</tr>
	</thead>
	<tbody>
		<?php foreach ($packages as $package) : ?>
			<tr>
				<td>
					<a href="/p/<?php echo $package['Package']['id']; ?>-<?php echo $package['Package']['name']; ?>" class="package-name">
						<?php echo $package['Maintainer']['username']; ?> / <?php echo $package['Package']['name']; ?>
					</a>
					<p class="description">
						<?php if (!empty($package['Category']['slug'])) : ?>
							<a href="/packages?category=<?php echo $package['Category']['slug']; ?>" class="label category-label" style="background-color:<?php echo $package['Category']['color']; ?>">
								<?php echo $package['Category']['name']; ?>
							</a>&nbsp;
						<?php endif; ?>
						<?php echo $this->Text->truncate($package['Package']['description']) ?>
					</p>
				</td>
				<td class="watchers"><?php echo $package['Package']['watchers']; ?></td>
			</tr>
		<?php endforeach; ?>
	</tbody>
</table>
