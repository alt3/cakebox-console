<?php

use Cake\Utility\Hash;
use Cake\Utility\Inflector;

//pr($data);
?>

<div class="col-md-6 col-xs-12">

	<!-- Quick stats widget -->
	<div class="widget stacked">

		<div class="widget-header">
			<i class="fa fa-star"></i>
			<h3><?= __('Stats') ?></h3>
		</div> <!-- /widget-header -->

		<div class="widget-content">

			<div class="row stats">

				<!-- Application counter -->
				<div class="col-xs-4">
					<span class="stat-value"><?= count($data['apps']) ?></span>
					<span class="stat-key"><?= __('Applications'); ?></span>
				</div>

				<div class="col-xs-4">
					<span class="stat-value"><?= $data['counters']['databases'] ?></span>
					<span class="stat-key"><?= __('Databases'); ?></span>
				</div>

				<div class="col-xs-4">
					<span class="stat-value"><?= $data['counters']['sites'] ?></span>
					<span class="stat-key"><?= __('Site Files'); ?></span>
				</div>

			</div> <!-- /stats -->


			<div id="chart-stats" class="stats">

				<div class="stat stat-chart">
					<div id="donut-chart" class="chart-holder"></div> <!-- #donut -->
				</div> <!-- /substat -->

				<div class="stat stat-time">
					<span class="stat-value">
						<span class="uptime-component">
							<span><?= $data['vm']['uptime']['days'] ?></span>
							<span class="uptime-key">d</span>
						</span>

						<span class="uptime-component">
							<span class="uptime-value"><?= $data['vm']['uptime']['hours'] ?></span>
							<span class="uptime-key">h</span>
						</span>

						<span class="uptime-component">
							<span class="uptime-value"><?= $data['vm']['uptime']['minutes'] ?></span>
							<span class="uptime-key">m</span>
						</span>
					</span>
					<div class="uptime-title">
						<?= __('System Uptime') ?>
					</div>
				</div> <!-- /substat -->

			</div> <!-- /substats -->

		</div> <!-- /widget-content -->

	</div> <!-- /widget -->


	<!-- Applications widget -->
	<div class="widget stacked widget-table action-table">

		<div class="widget-header">
			<i class="fa fa-th-list"></i>
			<h3><?= __('Applications') ?></h3>
		</div> <!-- /widget-header -->

		<div class="widget-content">

			<table class="table table-striped table-bordered">
				<tbody>

					<?php foreach ($data['apps'] as $app): ?>
						<tr>
							<td class="app-name">
								<?= $this->Html->link($app['name'], "http://" . $app['name'], ['class' => 'app-link']) ?>
							</td>
							<td class="app-version">
								<?= $app['framework_human'] ?> <?= $app['framework_version'] ?>
							</td>
							<td class="td-actions">
								<a href="javascript:;" class="btn btn-xs btn-primary">
									<i class="btn-icon-only fa fa-share"></i>
								</a>
							</td>
						</tr>
					<?php endforeach ?>

				</tbody>
			</table>

		</div> <!-- /widget-content -->

	</div> <!-- /widget -->

</div> <!-- /span6 -->


<div class="col-md-6">


	<div class="widget stacked">

		<div class="widget-header">
			<i class="fa fa-bookmark"></i>
			<h3><?= __('Shortcuts') ?></h3>
		</div> <!-- /widget-header -->

		<div class="widget-content">

			<div class="shortcuts">
				<?php
					// Applications
					 echo $this->Html->link(
						'<i class="shortcut-icon fa fa-code-fork"></i><span class="shortcut-label" />' . __('Applications'),
					  	'#',
						['class' => 'shortcut', 'escape' => false, 'title' => 'Not implemented yet']
					 );

					// Databases
					echo $this->Html->link(
						'<i class="shortcut-icon fa fa-database"></i><span class="shortcut-label" />' . __('Databases'),
						['controller'=>'databases', 'action'=>'index'],
						['class' => 'shortcut', 'escape' => false, 'title' => 'Not implemented yet']
					);

					// Site files
					echo $this->Html->link(
						'<i class="shortcut-icon fa fa-file-text-o"></i><span class="shortcut-label" />' . __('Site Files'),
						['controller'=>'sitefiles', 'action'=>'index'],
						['class' => 'shortcut', 'escape' => false]
					);

					// Pro Tips
					echo $this->Html->link(
						'<i class="shortcut-icon fa fa-lightbulb-o"></i><span class="shortcut-label" />' . __('Pro Tips'),
						'#',
						['class' => 'shortcut', 'escape' => false]
					);

					// Virtual Machine
					echo $this->Html->link(
						'<i class="shortcut-icon fa fa-cube"></i><span class="shortcut-label" />' . __('Virtual Machine'),
						'dashboards/vm',
						//['class' => 'shortcut', 'data-toggle' => 'modal', 'data-target' => '#modal-vm', 'escape' => false]
						['class' => 'shortcut', 'escape' => false]
					);

					// Kibana: dirty https tp http hack until figured out how to do otherwise
					$kibanaLink = $this->Html->link(
						'<i class="shortcut-icon fa fa-bar-chart"></i><span class="shortcut-label" />' . __('Kibana'),
						[
							'_port' => '5601',
							'controller' => false,
							'action' => false
						],
						['class' => 'shortcut', 'escape' => false]
					);
					echo str_replace('https', 'http', $kibanaLink);

					// Elasticsearch: dirty https tp http hack until figured out how to do otherwise
					$elasticSearchLink = $this->Html->link(
						'<i class="shortcut-icon fa fa-search"></i><span class="shortcut-label" />' . __('Elasticsearch'),
						[
							'_port' => '9200',
							'controller' => false,
							'action' => false
						],
						['class' => 'shortcut', 'escape' => false]
					);
					echo str_replace('https', 'http', $elasticSearchLink);

					// Credits
					echo $this->Html->link(
						'<i class="shortcut-icon fa fa-thumbs-up"></i><span class="shortcut-label" />' . __('Credits'),
						'#',
						['class' => 'shortcut', 'escape' => false]
					);
				?>
			</div> <!-- /shortcuts -->

		</div> <!-- /widget-content -->

	</div> <!-- /widget -->



	<!-- Sponsors widget -->
	<div class="widget sponsors stacked">

		<div class="widget-header">
			<button type="button" class="close" id="close-sponsors" data-dismiss="widget" aria-label="Close"><span aria-hidden="true">&times;</span></button>
			<i class="fa fa-bullhorn"></i>
			<h3><?= __('Our Sponsors') ?></h3>
		</div> <!-- /widget-header -->

		<div class="widget-content">
			<p>Theme license donated by Rod Howard from <?= $this->Html->link('Jumpstart Themes', 'http://jumpstartthemes.com') ?></p>
			<p>Box image cdn-hosted by our friends at <?= $this->Html->link('Your Name Here', 'http://google.com') ?></p>
		</div> <!-- /widget-content -->

	</div> <!-- /widget -->


	<!-- Recent Contributions widget -->
	<div class="widget widget-nopad stacked">

		<div class="widget-header">
			<i class="fa fa-user"></i>
			<h3><?= __('Recent Contributions') ?></h3>
		</div> <!-- /widget-header -->

		<div class="widget-content">

			<ul class="commit-items list-unstyled">
				<?php if (count($data['commits']) == 0): ?>
						<li class="commit-item api-failure">
							<p class="text-danger"><?= __('Looks the Github API is having an off day'); ?><i class="fa fa-exclamation-circle"></i></p>
						</li>
				<?php else: ?>
					<?php foreach($data['commits'] as $commit): ?>
						<li class="commit-item">
							<div class="row row-list">
								<div class="col-xs-1 avatar">
									<?= $this->Html->image($commit['author']['avatar_url'] . '&size=40', ['alt' => $commit['author']['login']]) ?>
								</div>
								<div class="col-xs-9 details">
									<!-- <span> -->
										<?= $this->Html->link($commit['author']['login'], $commit['author']['html_url']) ?>
										<p class="message">
											<?= $commit['commit']['message'] ?>
											<?= $this->Html->link('(' . substr($commit['sha'], 0, 7) . ')', $commit['html_url'], ['class' => 'sha-link']) ?>
										</p>
									<!-- </span> -->
								</div>
								<div class="col-xs-2 date pull-right">
									<span class="commit-day"><?= (new DateTime($commit['commit']['committer']['date']))->format("d") ?></span>
									<span class="commit-month"><?= (new DateTime($commit['commit']['committer']['date']))->format("M") ?></span>
								</div>
							</div>
						</li>
					<?php endforeach ?>

				<?php endif ?>
			</ul>

		</div> <!-- /widget-content -->

	</div> <!-- /widget -->


	<!-- Virtual Machine Modal -->
	<div class="modal fade" id="modal-vm-DIS" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
		<div class="modal-dialog">
			<div class="modal-content">
				<div class="modal-header">
					<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
					<h4 class="modal-title" id="myModalLabel"><?= __('Virtual Machine') ?></h4>
				</div>
				<div class="modal-body">
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
				<div class="modal-footer">
					<button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
				</div>
			</div>
		</div>
	</div>

</div> <!-- /span6 -->

</div> <!-- closes row from layout.ctp -->

</div> <!-- / closes .content in layout.ctp -->

</div> <!-- /closes .container in layout.ctp -->



		<div class="extra">

			<div class="container">

				<div class="row">

					<div class="col-md-3">

						<h4>About</h4>

						<ul>
							<li><a href="javascript:;">About Us</a></li>
							<li><a href="javascript:;">Twitter</a></li>
							<li><a href="javascript:;">Facebook</a></li>
							<li><a href="javascript:;">Google+</a></li>
						</ul>

					</div> <!-- /span3 -->

					<div class="col-md-3">

						<h4>Support</h4>

						<ul>
							<li><a href="javascript:;">Frequently Asked Questions</a></li>
							<li><a href="javascript:;">Ask a Question</a></li>
							<li><a href="javascript:;">Video Tutorial</a></li>
							<li><a href="javascript:;">Feedback</a></li>
						</ul>

					</div> <!-- /span3 -->

					<div class="col-md-3">

						<h4>Legal</h4>

						<ul>
							<li><a href="javascript:;">License</a></li>
							<li><a href="javascript:;">Terms of Use</a></li>
							<li><a href="javascript:;">Privacy Policy</a></li>
							<li><a href="javascript:;">Security</a></li>
						</ul>

					</div> <!-- /span3 -->

					<div class="col-md-3">

						<h4>Settings</h4>

						<ul>
							<li><a href="javascript:;">Consectetur adipisicing</a></li>
							<li><a href="javascript:;">Eiusmod tempor </a></li>
							<li><a href="javascript:;">Fugiat nulla pariatur</a></li>
							<li><a href="javascript:;">Officia deserunt</a></li>
						</ul>

					</div> <!-- /span3 -->



<?php
	// Count the number of app per unique framework to feed the donut
	$frameworks = array_values(array_unique(Hash::extract($data['apps'], '{n}.framework_human')));
	foreach ($frameworks as $framework) {
		$frameworkCount = count(Hash::extract($data['apps'], "{n}[framework_human=/$framework/].name"));
		$flotData[] = [
			'label' => $framework,
			'data' => $frameworkCount
		];
	}

	// create inline var "data" so it becomes available in the donut script
	echo $this->Html->scriptBlock(
		"var donutData = " . json_encode($flotData) ,
		['inline' => false]
	);

	// load the dashboard js
	echo $this->Html->script('pages/dashboards');
?>
