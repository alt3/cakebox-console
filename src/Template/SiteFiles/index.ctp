
<div class="panel panel-default">
	<div class="panel-heading"><?= __("Nginx site configuration files") ?></div>
	<div class="panel-body">
		<table class="table">
			<caption><?= __('As found in ') . $data['directories']['sites-available'] ?></caption>
			<thead>
				<tr>
					<th>#</th>
					<th><?= __("File name") ?></th>
					<th><?= __("Enabled") ?></th>
					<th><?= __("Last Modified") ?></th>
				</tr>
			</thead>
			<tbody>
				<?php foreach ($data['sitefiles'] as $key => $file): ?>
					<tr>
						<td><?= $key + 1 ?></td>
						<td><?= $file['name'] ?></td>
						<td><?= $file['enabled'] ? __('Yes') : __('No') ?></td>
						<td><?= $this->Time->format($file['modified'], 'YYYY-MM-dd'); ?></td>
					</tr>
				<?php endforeach ?>
			</tbody>
		</table>

	</div>
</div>
