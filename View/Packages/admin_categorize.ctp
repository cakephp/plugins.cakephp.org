<section class="page-title clearfix">
	<h2><?php echo $package['Package']['name']; ?></h2>
	<?php echo $this->Resource->description($package['Package']['description']); ?>
</section>

<section>
	<h3>Select a category for this package</h3>
	<?php foreach ($categories as $category_id => $category_name) : ?>
		<?php echo $this->Form->create('Package'); ?>
			<?php echo $this->Form->input('Package.id', array(
				'type' => 'hidden',
				'value' => $package['Package']['id'],
			)); ?>
			<?php echo $this->Form->input('Package.category_id', array(
				'type' => 'hidden',
				'value' => $category_id,
			)); ?>
			<?php echo $this->Form->button($category_name, array(
				'div' => false,
				'class' => 'button big'
			)); ?>
		<?php echo $this->Form->end(); ?>
	<?php endforeach; ?>
</section>