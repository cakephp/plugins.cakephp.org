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
                                    array('admin' => true, 'controller' => 'packages', 'action' => 'disable', $package['Package']['id']),
                                    array('class' => 'btn btn-primary btn-sm'),
                                    'Are you sure you want to disable package #' . $package['Package']['id'] . '?'
                                );
                                echo $this->Html->link(
                                    __('1.2'),
                                    array('admin' => true, 'controller' => 'packages', 'action' => 'version', $package['Package']['id'], '1.2'),
                                    array('class' => 'btn btn-danger btn-sm')
                                );
                                echo $this->Html->link(
                                    __('1.3'),
                                    array('admin' => true, 'controller' => 'packages', 'action' => 'version', $package['Package']['id'], '1.3'),
                                    array('class' => 'btn btn-warning btn-sm')
                                );
                                echo $this->Html->link(
                                    __('2.x'),
                                    array('admin' => true, 'controller' => 'packages', 'action' => 'version', $package['Package']['id'], '2'),
                                    array('class' => 'btn btn-info btn-sm')
                                );
                                echo $this->Html->link(
                                    __('3.x'),
                                    array('admin' => true, 'controller' => 'packages', 'action' => 'version', $package['Package']['id'], '3'),
                                    array('class' => 'btn btn-success btn-sm')
                                );
                            }
                        ?>
						<?php if (!empty($package['Category']['slug'])) : ?>
							<a href="/packages?category=<?php echo $package['Category']['slug']; ?>" class="label category-label" style="background-color:<?php echo $package['Category']['color']; ?>">
								<?php echo $package['Category']['name']; ?>
							</a>&nbsp;
						<?php endif; ?>
                        <?php $tags = explode(',', $package['Package']['tags']); ?>
                        <?php if (in_array('version:3', $tags)) : ?>
                            <span class="label category-label" style="background-color:#27a4dd">3.x</span>
                        <?php endif; ?>
                        <?php if (in_array('version:2', $tags)) : ?>
                            <span class="label category-label" style="background-color:#9dd5c0">2.x</span>
                        <?php endif; ?>
                        <?php if (in_array('version:1.3', $tags)) : ?>
                            <span class="label category-label" style="background-color:#ffaaa5">1.3</span>
                        <?php endif; ?>
                        <?php if (in_array('version:1.2', $tags)) : ?>
                            <span class="label category-label" style="background-color:#ffd3b6">1.2</span>
                        <?php endif; ?>
						<?php echo $this->Text->truncate($package['Package']['description']) ?>
                    </p>
				</td>
				<td class="watchers"><?php echo $package['Package']['watchers']; ?></td>
			</tr>
		<?php endforeach; ?>
	</tbody>
</table>
