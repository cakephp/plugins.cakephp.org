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
                    <?php if ($this->Session->read('Auth.User')) : ?>
                        - <?php echo $this->Resource->githubUrl(
                            $package['Maintainer']['username'],
                            $package['Package']['name']
                        ); ?>
                    <?php endif; ?>
					<p class="description">
                        <?php
                            if ($this->Session->read('Auth.User') && empty($package['Package']['deleted'])) {
                                echo $this->Html->link(
                                    __('Disable'),
                                    array('admin' => true, 'action' => 'disable', $package['Package']['id']),
                                    array('class' => 'btn btn-primary btn-sm'),
                                    'Are you sure you want to disable package #' . $package['Package']['id'] . '?'
                                );
                            }
                        ?>
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
