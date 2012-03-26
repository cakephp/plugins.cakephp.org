<?php $this->set('_bodyClass', $_bodyClass . ' page-form'); ?>

<?php echo $this->Form->create('Package', array(
	'url' => array('action' => 'jobs'),
	'class' => 'edit-package-form clearfix',
)); ?>

	<h2>The Hand Job</h2>

	<section>
		<select name="data[Package][job]" class="start-job">
			<option value="">- Select a Job -</option>
			<?php foreach ($jobs as $key => $val): ?>
				<option value="<?php echo $key; ?>" data-args="<?php
					echo implode('|', $val);
				?>"><?php
					echo $key . '(' . implode(', ', $val) . ')';
				?></option>
			<?php endforeach; ?>
		</select>
	</section>

	<section class="job-fields"></section>

	<section class="actions">
		<hr/>
		<?php
		echo $this->Form->button(
			__('Fire'),
			array('class' => 'button solid-green primary', 'div' => false, 'style' => 'float:right;')
		);
		?>
	</section>

<?php echo $this->Form->end(); ?>

<?php echo $this->Html->scriptStart(array('inline' => false)); ?>
$(function() {
	var createInput = function(field) {
		var input = $('<div />').addClass('input text');
		input.append( $('<label />').text(field) );
		input.append( $('<input />').attr('name', 'data[Package][' + field + ']') );
		$('.job-fields').append(input);
	};
	$('.start-job').change(function() {
		var job = $(this).val();
		var args = $(this).find('option:selected').data('args').split('|');
		$('.job-fields').empty();
		$.each(args, function(key, val) {
			createInput(val);
		});
	});
});
<?php echo $this->Html->scriptEnd(); ?>