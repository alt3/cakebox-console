<?php

use Cake\Utility\Hash;
use Cake\Utility\Inflector;

?>

<!-- left column -->
<div class="col-sm-9 column">

	<!-- Spotlights -->
	<div id="spotlights" class="col-sm-4 column">
		<div class="panel panel-primary">
			<div class="panel-heading">
				<div class="row">
					<div class="col-xs-3">
						<i class="fa fa-code-fork fa-5x"></i>
					</div>
					<div class="col-xs-9 text-right">
						<div class="huge"><?= count($data['apps']) ?></div>
						<div><?= __('Applications') ?></div>
					</div>
				</div>
			</div>
			<a href="#" title="Not implemented">
				<div class="panel-footer">
					<span class="pull-left"><?= __('New Application') ?></span>
					<span class="pull-right"><i class="fa fa-arrow-circle-right"></i></span>
					<div class="clearfix"></div>
				</div>
			</a>
		</div>
	</div>

	<div class="col-sm-4 column">
		<div class="panel panel-green">
			<div class="panel-heading">
				<div class="row">
					<div class="col-xs-3">
						<i class="fa fa-database fa-5x"></i>
					</div>
					<div class="col-xs-9 text-right">
						<div class="huge"><?= $data['counters']['databases'] ?></div>
						<div><?= __('Databases') ?></div>
					</div>
				</div>
			</div>
			<a href="databases">
				<div class="panel-footer">
					<span class="pull-left"><?= __('View Details') ?></span>
					<span class="pull-right"><i class="fa fa-arrow-circle-right"></i></span>
					<div class="clearfix"></div>
				</div>
			</a>
		</div>
	</div>

	<div class="col-sm-4 column">
		<div class="panel panel-yellow">
			<div class="panel-heading">
				<div class="row">
					<div class="col-xs-3">
						<i class="fa fa-link fa-5x"></i>
					</div>
					<div class="col-xs-9 text-right">
						<div class="huge"><?= $data['counters']['sites'] ?></div>
						<div><?= __('Site Files') ?></div>
					</div>
				</div>
			</div>
			<a href="sitefiles">
				<div class="panel-footer">
					<span class="pull-left"><?= __("View Details") ?></span>
					<span class="pull-right"><i class="fa fa-arrow-circle-right"></i></span>
					<div class="clearfix"></div>
				</div>
			</a>
		</div>
	</div>
	<!-- EOF spotlights -->

	<!-- tabs -->
	<div id="tabs" class="col-sm-12 column">
		<div class="tabbable" id="tabs-299824">
			<ul class="nav nav-tabs">
				<li id="tab-apps" class="active"><a href="#panel-apps" data-toggle="tab"><?= __("Apps") ?></a></li>
				<li id="tab-status"><a href="#panel-status" data-toggle="tab"><?= __("Box status") ?></a></li>
				<li id="tab-software"><a href="#panel-software" data-toggle="tab"><?= __("Box software") ?></a></li>
				<li id="tab-usage"><a href="#panel-usage" data-toggle="tab"><?= __("Usage") ?></a></li>
				<li id="tab-credits"><a href="#panel-credits" data-toggle="tab"><?= __("Credits") ?></a></li>
			</ul>
			<div class="tab-content">

				<!-- Apps tab -->
				<div role="tabpanel" id="panel-apps" class="tab-pane active">
					<div class="col-sm-12">
						<?php $columns = $this->Cakebox->columnize($data['apps'], 2) ?>
						<?php foreach ($columns as $column): ?>
							<div class="row">
								<!-- Each app a dedicated panel -->
								<?php foreach ($column as $app): ?>
									<div class="col-sm-6">
										<div class="panel panel-primary">
											<div class="panel-heading app <?= $app['framework'] ?>">
												<h3 class="panel-title">
													<?= $this->Html->link($app['name'], "http://" . $app['name']) ?>
												</h3>
											</div>
											<div class="panel-body">
												<ul class="list-unstyled">
													<li><strong><?= __("Framework") ?>:</strong> <?= Inflector::humanize($app['framework']) ?> <?= $app['framework_version'] ?></li>
													<li><strong><?= __("Directory") ?>:</strong> <?= $app['appdir'] ?></li>
													<li><strong><?= __("Webroot") ?>:</strong> <?= $app['webroot']  ?></li>
												</ul>
											</div>
										</div>
									</div>
								<?php endforeach ?>
							</div>
						<?php endforeach ?>
					</div>
				</div>
				<!-- EOF Apps tab -->

				<!-- Status tab -->
				<div role="tabpanel" id="panel-status" class="tab-pane">

					<div class="ajax-loader text-center">
						<i class="fa fa-spinner fa-spin"></i>
					</div>

					<!-- Tab content -->
					<div class="panel-content col-sm-12 hidden">

						<!-- System panel -->
						<div class="panel panel-primary" id="status-system">
							<div class="panel-heading">
								<h3 class="panel-title"><?= __('System') ?></h3>
							</div>
							<div class="panel-body">
								<ul class="list-unstyled">
									<!-- ajax loaded list -->
								</ul>
							</div>
						</div>

						<!-- Application panel -->
						<div class="panel panel-primary" id="status-application">
							<div class="panel-heading">
								<h3 class="panel-title"><?= __('Application') ?></h3>
							</div>
							<div class="panel-body">
								<ul class="list-unstyled">
									<!-- ajax loaded list -->
								</ul>
							</div>
						</div>

						<!-- Security panel -->
						<div class="panel panel-primary" id="status-security">
							<div class="panel-heading">
								<h3 class="panel-title"><?= __('Security') ?></h3>
							</div>
							<div class="panel-body">
								<ul class="list-unstyled">
									<!-- ajax loaded list -->
								</ul>
							</div>
						</div>

					</div>
					<!-- EOF tab content -->
				</div>
				<!-- EOF Status tab -->


				<!-- Software tab -->
				<div role="tabpanel" id="panel-software" class="tab-pane">

					<div class="ajax-loader text-center">
						<i class="fa fa-spinner fa-spin"></i>
					</div>

					<!-- Operating System -->
					<div class="panel-content col-sm-12 hidden">
						<div class="panel panel-primary">
							<div class="panel-heading">
								<h3 class="panel-title"><?= __("Operating System") ?></h3>
							</div>
							<div class="panel-body">
								<ul class="list-unstyled">
									<li id="os-description"><strong><?= __('Description') ?></strong>: </li>
									<li id="os-codename"><strong><?= __('Codename') ?></strong>: </li>
									<li id="os-architecture"><strong><?= __('Architecture') ?></strong>: </li>
								</ul>
							</div>
						</div>
					</div>

					<!-- Package information -->
					<div class="panel-content col-sm-12 hidden">
						<div class="panel panel-primary">
							<div class="panel-heading">
								<h3 class="panel-title"><?= __("Software") ?></h3>
							</div>
							<div class="panel-body packages">
								<div class="row">
									<!-- ajax-loaded content -->
								</div>
							</div>
						</div>
					</div>

					<!-- PHP modules -->
					<div class="panel-content col-sm-12 hidden">
						<div class="panel panel-primary">
							<div class="panel-heading">
								<h3 class="panel-title"><?= __("PHP Modules") ?></h3>
							</div>
							<div class="panel-body php-modules">
								<div class="row">

								</div>
							</div>
						</div>
					</div>

					<!-- Nginx modules -->
					<div class="panel-content col-sm-12 hidden">
						<div class="panel panel-primary">
							<div class="panel-heading">
								<h3 class="panel-title"><?= __("Nginx Modules") ?></h3>
							</div>
							<div class="panel-body">
								<div class="row">
									<div id="nginx-core-modules" class="col-sm-4">
										<div><strong><?= __('Core') ?></strong></div>
										<ul class="list-unstyled">
											<!-- ajax-loaded list -->
										</ul>
									</div>

									<div id="nginx-3rdparty-modules" class="col-sm-4">
										<div><strong><?= __('3rd Party') ?></strong></div>
										<ul class="list-unstyled">
											<!-- ajax-loaded list -->
										</ul>
									</div>
								</div>
							</div>
						</div>
					</div>

				</div>
				<!-- EOF Software tab -->

				<!-- Credits tab -->
				<div role="tabpanel" id="panel-credits" class="tab-pane">

					<div class="ajax-loader text-center">
						<i class="fa fa-spinner fa-spin"></i>
					</div>

					<div class="panel-content col-sm-12 hidden">
						<div class="panel panel-primary">
							<div class="panel-heading">
								<h3 class="panel-title"><?= __("Contributors") ?></h3>
							</div>
							<div class="panel-body credits">
								<div class="row">
									<!-- ajax-loaded columns -->
								</div>
							</div>
						</div>
					</div>

				</div>
				<!-- EOF Credits tab -->

				<!-- Usage tab -->
				<div role="tabpanel" id="panel-usage" class="tab-pane">
					<?= $this->element('usage-panel') ?>
				</div>
				<!-- EOF Usage tab -->

			</div>
			<!-- EOF tab-content -->
		</div>
		<!-- EOF .tabbable -->
	</div>
	<!-- EOF #tabs -->

</div>
<!-- EOF left column-->

<!-- right column -->
<div class="col-sm-3 column">

	<!-- VM Box -->
	<div class="col-sm-12 column">
		<div class="panel panel-default">
			<div class="panel-heading">
				<h3 class="panel-title">
					Virtual Machine
				</h3>
			</div>
			<div class="panel-body">
				<ul class="list-unstyled">
					<li><strong><?= __("Hostname") ?>:</strong> <?= $data['vm']['hostname'] ?></li>
					<li><strong><?= __("IP address") ?>:</strong> <?= $data['vm']['ip_address'] ?></li>
					<li><strong><?= __("CPUs") ?>:</strong> <?= $data['vm']['cpus'] ?></li>
					<li><strong><?= __("Memory") ?>:</strong> <?= $data['vm']['memory'] ?>MB</li>
					<li><strong><?= __("Uptime") ?>:</strong> <?= $this->Cakebox->getUptimeString($data['vm']['uptime']) ?></li>
				</ul>
			</div>
		</div>
	</div>
	<!-- EOF VM box -->

	<?= $this->element('toolbox-panel') ?>

</div>
<!-- EOF right column -->

<?= $this->Html->script('cakebox-dashboard'); ?>
