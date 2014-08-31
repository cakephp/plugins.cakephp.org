<h2 class="search-title">Search For Packages</h2>

<?php echo $this->element('search-bar'); ?>

<div class="row">
	<div class="col-md-4">
		<h4 class="site-description"><em>CakePackages</em> is a directory of Plugins and Tools for your CakePHP projects</h4>
		<?php echo $this->element('suggest') ?>
	</div>
	<div class="col-md-4">
		<?php echo $this->element('popular-packages') ?>
	</div>
	<div class="col-md-4">
		<?php echo $this->element('search-legend') ?>
	</div>
</div>
