
<div class="col-sm-12 column">

	<!-- Sitefiles widget -->
	<div class="widget stacked widget-table action-table">

		<div class="widget-header">
			<i class="fa fa-file-text-o"></i>
			<h3><?= __('Framework Applications') ?></h3>
		</div> <!-- /widget-header -->

		<div class="widget-content">
			<div class="panel-body">
				<table class="table collection">
					<thead>
						<tr>
							<th><?= __("Name") ?></th>
							<th><?= __("Framework") ?></th>
							<th></th>
						</tr>
					</thead>
					<tbody>
						<?php foreach ($data['apps'] as $app): ?>
							<tr class="app">
								<td>
									<?= $this->Html->link($app['name'], "http://" . $app['name']) ?>
								</td>
								<td>
									<?= $app['framework_human'] ?> <?= $app['framework_version'] ?>
								</td>
								<td class="actions">
									<div class="btn-group pull-right">
										<button type="button" class="btn btn-default btn-sm" data-toggle="modal" data-target="#fileModal">
											<?= __('View') ?>
										</button>
										<button type="button" class="btn btn-danger btn-sm">
											<?= __('Delete') ?>
										</button>
									</div>
								</td>
							</tr>
						<?php endforeach ?>
					</tbody>
				</table>
			</div>

		</div> <!-- /widget-content -->

	</div> <!-- /widget -->

</div>

<!-- Modal -->
<div class="modal fade" id="fileModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
	<div class="modal-dialog">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
				<h4 class="modal-title" id="myModalLabel">ajax-loaded-title</h4>
			</div>
			<div class="modal-body">
				ajax-loaded-content
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
			</div>
		</div>
	</div>
</div>

<script>
$('#fileModal').on('show.bs.modal', function (event) {
	var modal = $(this)
	var button = $(event.relatedTarget) // Button that triggered the modal
	var filename = button.closest('tr').find('td.filename').html()
	$('.modal-title').html('/etc/nginx/sites-available/' + filename)
	var jqxhr = $.getJSON( 'sitefiles/file/' + filename + '.json', function(data) {
		console.log(modal)
		modal.find('.modal-body').html('<pre>' + data.content + '</pre>')
	})
})
.fail(function() {
	alert( 'So sorry, something went wrong fetching the file...' )
})
</script>
