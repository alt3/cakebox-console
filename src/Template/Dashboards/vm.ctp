<?php

use Cake\Utility\Hash;
use Cake\Utility\Inflector;

//pr($data);
?>

<div class="col-sm-12 column">
	<div id="tabs" class="col-sm-12 column">
		<div class="tabbable" id="tabs-vm">

			<!-- Tabs declaration -->
			<ul class="nav nav-tabs">
				<li id="tab-summary" class="active"><a href="#panel-summary" data-toggle="tab"><?= __("Summary") ?></a></li>
				<li id="tab-status"><a href="#panel-status" data-toggle="tab"><?= __("Box status") ?></a></li>
				<li id="tab-software"><a href="#panel-software" data-toggle="tab"><?= __("Box software") ?></a></li>
			</ul>

			<!-- Tab content -->
			<div class="tab-content">

				<!-- Summary  tab -->
				<div role="tabpanel" id="panel-summary" class="tab-pane active">

					<div class="widget stacked widget-table action-table">
						<div class="widget-header">
							<i class="fa fa-cube"></i>
							<h3><?= __('Virtual Machine') ?></h3>
						</div> <!-- /widget-header -->

						<div class="widget-content">
							<div class="panel-body">
								<ul class="pair list-unstyled">
									<li>
										<span class="key"><?= __("Hostname") ?>:</span>
										<span class="value"><?= $data['vm']['hostname'] ?></span>
									</li>
									<li>
										<span class="key"><?= __("IP address") ?>:</span>
										<span class="value"><?= $data['vm']['ip_address'] ?></span>
									</li>
									<li>
										<span class="key"><?= __("CPUs") ?>:</span>
										<span class="value"><?= $data['vm']['cpus'] ?></span>
									</li>
									<li>
										<span class="key"><?= __("Memory") ?>:</span>
										<span class="value"><?= $data['vm']['memory'] ?>MB</span>
									</li>
								</ul>
							</div>
						</div>
					</div>
				</div>

				<!-- Status tab -->
				<div role="tabpanel" id="panel-status" class="tab-pane">

					<div class="ajax-loader text-center">
						<i class="fa fa-spinner fa-spin"></i>
					</div>

					<!-- System checks -->
					<div id="status-system" class="widget stacked widget-table hidden">
						<div class="widget-header">
							<i class="fa fa-desktop"></i>
							<h3><?= __('System') ?></h3>
						</div>

						<div class="widget-content">
							<div class="panel-body">
								<ul class="list-unstyled">
									<!-- ajax loaded list -->
								</ul>
							</div>
						</div>
					</div>

					<!-- Application checks -->
					<div id="status-application" class="widget stacked widget-table hidden">
						<div class="widget-header">
							<i class="fa fa-code"></i>
							<h3><?= __('Application') ?></h3>
						</div>

						<div class="widget-content">
							<div class="panel-body">
								<ul class="list-unstyled">
									<!-- ajax loaded list -->
								</ul>
							</div>
						</div>
					</div>

					<!-- Security checks -->
					<div id="status-security" class="widget stacked widget-table hidden">
						<div class="widget-header">
							<i class="fa fa-lock"></i>
							<h3><?= __('Security') ?></h3>
						</div>

						<div class="widget-content">
							<div class="panel-body">
								<ul class="list-unstyled">
									<!-- ajax loaded list -->
								</ul>
							</div>
						</div>
					</div>

				</div>
				<!-- EOF Status tab -->

				<!-- Software tab -->
				<div role="tabpanel" id="panel-software" class="tab-pane">

					<div class="ajax-loader text-center">
						<i class="fa fa-spinner fa-spin"></i>
					</div>

					<!-- Operating System -->
					<div id="software-system" class="widget stacked widget-table hidden">
						<div class="widget-header">
							<i class="fa fa-linux"></i>
							<h3><?= __('Operating System') ?></h3>
						</div>

						<div class="widget-content">
							<div class="panel-body">
								<ul class="pair list-unstyled">
									<li>
										<span class="key"><?= __('Description') ?>:</span>
										<span class="value os-description"></span>
									</li>
									<li>
										<span class="key"><?= __('Codename') ?>:</span>
										<span class="value os-codename"></span>
									</li>
									<li>
										<span class="key"><?= __('Architecture') ?>:</span>
										<span class="value os-architecture"></span>
									</li>
								</ul>
							</div>
						</div>
					</div>

					<!-- Software -->
					<div id="software-packages" class="widget stacked widget-table hidden">
						<div class="widget-header">
							<i class="fa fa-file-text-o"></i>
							<h3><?= __('Software') ?></h3>
						</div>

						<div class="widget-content">
							<div class="panel-body packages">
								<div class="row">
									<!-- ajax-loaded content -->
								</div>
							</div>
						</div>
					</div>

					<!-- PHP Modules -->
					<div id="software-php-modules" class="widget stacked widget-table hidden">
						<div class="widget-header">
							<i class="fa fa-plug"></i>
							<h3><?= __('PHP Modules') ?></h3>
						</div>

						<div class="widget-content">
							<div class="panel-body php-modules">
								<div class="row">
									<!-- ajax-loaded content -->
								</div>
							</div>
						</div>
					</div>

					<!-- Nginx Modules -->
					<div id="software-nginx-modules" class="widget stacked widget-table hidden">
						<div class="widget-header">
							<i class="fa fa-plug"></i>
							<h3><?= __('Nginx Modules') ?></h3>
						</div>

						<div class="widget-content">
							<div class="panel-body nginx-modules">
								<div class="row">
									<div id="nginx-core-modules" class="col-sm-3">
										<div><strong><?= __('Core') ?>:</strong></div>
										<ul class="list-unstyled">
											<!-- ajax-loaded list -->
										</ul>
									</div>

									<div id="nginx-3rdparty-modules" class="col-sm-3">
										<div><strong><?= __('3rd Party') ?>:</strong></div>
										<ul class="list-unstyled">
											<!-- ajax-loaded list -->
										</ul>
									</div>
								</div>
							</div>
						</div>
					</div>

				</div><!-- EOF software tab -->

			</div>
		</div>
	</div>
</div>






<?php echo $this->Html->script('pages/dashboards.vm'); ?>
