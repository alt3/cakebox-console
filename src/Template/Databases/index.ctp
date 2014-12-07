<?php
use Cake\Cache\Cache;
use Cake\Datasource\ConnectionManager;
use Cake\Filesystem\File;
use Cake\Filesystem\Folder;
use Cake\Utility\Hash;
use Cake\Utility\Inflector;

?>

<!-- DEVELOPMENT column -->
<div class="col-sm-12 column">
	<?php
		//pr($boxApps);
		//pr ($this->blocks());
	?>
</dev>

<!-- main column -->
<div class="col-sm-12 column">
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

</div>
<!-- EOF main column -->
