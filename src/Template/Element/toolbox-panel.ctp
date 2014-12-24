
<!-- Toolbox -->
<div class="col-sm-12 column">
	<div class="panel panel-default">
		<div class="panel-heading">
			<h3 class="panel-title">
				Toolbox
			</h3>
		</div>
		<div class="panel-body">
			<ul class="list-unstyled">
				<?php
					// Dirty rehack of https to http until figured out how to do otherwise
					$kibanaLink = $this->Html->link(__("Kibana"), ['_port' => '5601', 'controller' => false, 'action' => false]);
					$elasticsearchLink = $this->Html->link(__("Elasticsearch"), ['_port' => '9200', 'controller' => false, 'action' => false]);
				?>
				<li><?= str_replace('https', 'http', $kibanaLink) ?></li>
				<li><?= str_replace('https', 'http', $elasticsearchLink) ?></li>
				<? if ($data['update']): ?>
				<li>
					<p class="text-danger"><?= __('Self-update available') ?> <i class="fa fa-exclamation-circle"></i></p>
				</li>
			<? endif ?>
		</ul>
	</div>
</div>
</div>
