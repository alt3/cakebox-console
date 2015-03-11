
<div class="col-sm-10 column">

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

<!-- Stats -->
<div class="col-sm-2 column">
	<div class="widget stacked widget-table action-table">
		<div class="widget-header">
			<i class="fa fa-star"></i>
			<h3><?= __('Stats') ?></h3>
		</div>

		<div class="widget-content">
			<div class="panel-body">
		</div>
	</div>
</div>
