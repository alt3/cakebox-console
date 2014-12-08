
<div class="col-sm-12 column">

	<!-- Main databases panel -->
	<div class="panel panel-default">
		<div class="panel-heading"><?= __("Application databases") ?></div>
		<div class="panel-body">
			<ul class="list-unstyled">
				<?php foreach ($databases as $database): ?>
					<li>
						<?= $database ?>
					</li>
				<?php endforeach ?>
			</ul>
		</div>
	</div>
	<!-- EOF main databases panel -->

</div>
