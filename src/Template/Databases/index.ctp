<div class="col-sm-12 column">

	<!-- Main databases panel -->
	<div class="panel panel-primary">
		<div class="panel-heading">
			<h3 class="panel-title"><?= __("Application databases") ?></h3>
		</div>
		<div class="panel-body">
			<table class="table">
				<caption><?= __('Excluding system databases') ?></caption>
				<thead>
					<tr>
						<th>#</th>
						<th><?= __("Name") ?></th>
					</tr>
				</thead>
				<tbody>
					<?php foreach ($databases as $key => $database): ?>
						<tr>
							<td><?= $key + 1 ?></td>
							<td><?= $database['name'] ?></td>
						</tr>
					<?php endforeach ?>
				</tbody>
			</table>

		</div>
	</div>
	<!-- EOF main databases panel -->

</div>
