<section class="search">
	<?php if (!empty($packages)) : ?>
		<h2 class="search-title"><?php echo $title; ?></h2>
	<?php endif; ?>
	<div>
		<?php echo $this->element('search-bar'); ?>
		<div class="search-details clearfix">
			<span class="total">
				<?php
					$packageCount = count($packages);
					echo $packageCount . __n(' Package', ' Packages', $packageCount);
				?>
			</span>

			<div class="sort-group">
				<strong>Sort: </strong>
				<?php echo $this->Resource->sort($order); ?>
				<!-- <a class="active" href="#sort-name">Name</a>
				<a href="#sort-installs">Installs</a>
				<a href="#sort-lastmodified">Last Modified</a> -->
			</div>
		</div>
	</div>
</section>

<?php echo $this->element('package-results', array('packages' => $packages)) ?>
