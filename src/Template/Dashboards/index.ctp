<?php

use Cake\Utility\Hash;
use Cake\Utility\Inflector;

//pr($data);
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
			<a href="#">
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
				<li class="active"><a href="#panel-apps" data-toggle="tab"><?= __("Apps") ?></a></li>
				<li><a href="#panel-status" data-toggle="tab"><?= __("Box status") ?></a></li>
				<li><a href="#panel-software" data-toggle="tab"><?= __("Box software") ?></a></li>
				<li><a href="#panel-usage" data-toggle="tab"><?= __("Pro tips!") ?></a></li>
			</ul>
			<div class="tab-content">

				<!-- Apps tab -->
				<div role="tabpanel" class="tab-pane active" id="panel-apps">
					<div class="col-sm-12">
						<?php $columns = $this->Cakebox->columnize($data['apps'], 2) ?>
						<?php foreach ($columns as $column): ?>
							<div class="row">
								<!-- Each app a dedicated panel -->
								<?php foreach ($column as $app): ?>
									<div class="col-sm-6">
										<div class="panel panel-default">
											<div class="panel-heading app <?= $app['framework'] ?>">
												<?= $this->Html->link($app['name'], "http://" . $app['name']) ?>
											</div>
											<div class="panel-body">
												<ul class="list-unstyled">
													<li><strong><?= __("Framework") ?>:</strong> <?= Inflector::humanize($app['framework']) ?> <?= $app['framework_version'] ?></li>
													<li><strong><?= __("Directory") ?>:</strong> <?= $app['appdir'] ?></li>
													<li><strong><?= __("Webroot") ?>:</strong> <?= $app['webroot']  ?></li>
													<li><strong><?= __("Database") ?>:</strong>To be implemented</li>
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
				<div class="tab-pane" id="panel-status">
					<?php foreach ($data['checks'] as $category => $checks): ?>

						<div class="col-sm-12">
							<div class="panel panel-default">
								<?php $failCount = count(Hash::extract($checks, '{s}[pass=0]')) ?>
								<div class="panel-heading <?= $failCount ? 'danger' : 'success' ?>"><?= __(Inflector::humanize($category)) ?></div>
								<div class="panel-body">
									<ul class="list-unstyled">
										<?php foreach ($checks as $check): ?>
											<li>
												<i class="fa <?= $check['pass'] ? 'fa-check' : 'fa-times' ?>"></i>
												<?= $check['message'] ?>
											</li>
										<?php endforeach; ?>
									</ul>
								</div>
							</div>
						</div>

					<?php endforeach ?>

				</div>
				<!-- EOF Status tab -->


				<!-- Software tab -->
				<div class="tab-pane" id="panel-software">

					<!-- Operating System -->
					<div class="col-sm-12">
						<div class="panel panel-default">
							<div class="panel-heading"><?= __("Operating System") ?></div>
							<div class="panel-body">
								<ul class="list-unstyled">
									<li><strong>Description</strong>:
										<?= $this->Html->link(preg_replace('/"/', '', $data['operating_system']['DISTRIB_DESCRIPTION']), "https://wiki.ubuntu.com/LTS") ?>
									<li><strong>Codename</strong>: <?= $data['operating_system']['DISTRIB_CODENAME'] ?></li>
									<li><strong>Architecture</strong>: <?= $data['operating_system']['architecture'] ?></li>
								</ul>
							</div>
						</div>
					</div>
					<!-- EOF Operating System -->

					<!-- Package information -->
					<div class="col-sm-12">
						<div class="panel panel-default">
							<div class="panel-heading"><?= __("Software") ?></div>
							<div class="panel-body">
								<div class="row">
									<?php $columns = $this->Cakebox->divideEvenly($data['packages'], 3) ?>
									<?php foreach ($columns as $column): ?>
										<div class="col-sm-4">
											<ul class="list-unstyled">
												<?php foreach ($column as $package): ?>
													<?php if(!empty($package['link'])): ?>
														<li>
															<a href="<?= $package['link'] ?>" title="<?= $package['name'] ?>" target="blank"><?= $package['name'] ?> <?php echo $package['version'] ? $package['version'] : '<i class="fa fa-times" title="Could not detect version"></i>'; ?></a>
														</li>
													<?php else: ?>
														<li><?= $package['name'] ?></li>
													<?php endif ?>
												<?php endforeach ?>
											</ul>
										</div>
									<?php endforeach ?>
								</div>
							</div>
						</div>
					</div>
					<!-- EOF Package information-->

					<!-- PHP modules -->
					<div class="col-sm-12">
						<div class="panel panel-default">
							<div class="panel-heading"><?= __("PHP Modules") ?></div>
							<div class="panel-body">
								<div class="row">
									<?php $columns = $this->Cakebox->divideEvenly($data['php_modules'], 3) ?>
									<?php foreach ($columns as $column): ?>
										<div class="col-sm-4">
											<ul class="list-unstyled">
												<?php foreach ($column as $module): ?>
													<?php if(!empty($module['link'])): ?>
														<li>
															<?= $this->Html->link($module['name'], $module['link']) ?>
														</li>
													<?php else: ?>
														<li>
															<?= $module['name'] ?>
														</li>
													<?php endif ?>
												<?php endforeach ?>
											</ul>
										</div>
									<?php endforeach ?>
								</div>
							</div>
						</div>
					</div>


					<!-- Nginx modules -->
					<div class="col-sm-12">
						<div class="panel panel-default">
							<div class="panel-heading"><?= __("Nginx Modules") ?></div>
							<div class="panel-body">
								<div class="row">
									<?php foreach($data['nginx_modules'] as $category => $modules): ?>
										<div class="col-sm-4">
											<div><strong><?= __(Inflector::Humanize($category)) ?>:</strong></div>
											<ul class="list-unstyled">
												<?php foreach($modules as $module): ?>
													<li>
														<?= $this->Html->link($module['short_name'], $module['link']) ?>
													</li>
												<?php endforeach ?>
											</ul>
										</div>
									<?php endforeach ?>
								</div>
							</div>
						</div>
					</div>

				</div>
				<!-- EOF Software tab -->

				<!-- Usage tab -->
				<div role="tabpanel" class="tab-pane" id="panel-usage">
					<?= $this->element('usage-panel') ?>
				</div>
				<!-- EOF Usage tab -->

			</div>
			<!-- EOF tab-content -->
		</div>
		<!-- EOF .tabeale -->
	</div>
	<!-- EOF #tabs -->

</div>
<!-- EOF left column-->

<!-- Right column -->
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
				</ul>
			</div>
		</div>
	</div>
	<!-- EOF box-info -->

</div>
<!-- EOF right column -->
