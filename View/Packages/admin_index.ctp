<div class="clearfix columns">
	<table class="datatable table table-striped table-hover table-condensed">
		<thead><?php
			echo $this->Html->tableHeaders(array(
				'Id', 'Name', 'By', 'Added On', 'Last Updated', 'Enabled?', 'Actions',
			));
		?></thead>
		<tbody>
			<tr>
				<td colspan="6" class="dataTables_empty">Loading...</td>
			</tr>
		</tbody>
	</table>
</div>
<?php echo $this->Html->script('jquery.dataTables.min', array('inline' => false)); ?>
<?php echo $this->Html->scriptStart(array('inline' => false)); ?>
$(function() {
	$('.datatable').dataTable({
		sAjaxSource: '<?php echo $this->Html->url(array('action' => 'index')); ?>',
		bProcessing: true,
		bServerSide: true,
		bStateSave: true
	});
});
<?php echo $this->Html->scriptEnd(); ?>
