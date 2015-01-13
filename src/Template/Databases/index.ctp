<div class="col-sm-10 column">

	<!-- Databases widget -->
	<div class="widget stacked widget-table action-table">

		<div class="widget-header">
			<i class="fa fa-database"></i>
			<h3><?= __('Application Databases') ?></h3>
		</div> <!-- /widget-header -->

		<div class="widget-content">
			<div class="panel-body">

				<table class="table collection">
					<caption><?= __('Excluding system databases') ?></caption>
					<thead>
						<tr>
							<th>#</th>
							<th><?= __("Name") ?></th>
							<th></th>
						</tr>
					</thead>
					<tbody>
						<?php foreach ($databases as $key => $database): ?>
							<tr>
								<td><?= $key + 1 ?></td>
								<td><?= $database['name'] ?></td>
								<td class="actions">
									<div class="btn-group pull-right">
										<button type="button" class="btn btn-danger btn-sm todo">
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
</div> <!-- col-sm-10 -->

<!-- Actions -->
<div class="col-sm-2 column">
	<div class="actions">
		<a href="#" class="todo btn btn-default btn-block btn-sm" alt="<?= __('New Database') ?>"><?= __('New Database') ?></a>
	</div>
</div>
