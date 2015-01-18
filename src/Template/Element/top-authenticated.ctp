<?php
/**
* Bootstrap 3 top navigation
*/
?>

<nav class="navbar navbar-inverse" role="navigation">

	<div class="container">
		<!-- Brand and toggle get grouped for better mobile display -->
		<div class="navbar-header">
			<button type="button" class="navbar-toggle" data-toggle="collapse" data-target=".navbar-ex1-collapse">
				<span class="sr-only"><?= __('Toggle navigation'); ?></span>
				<i class="fa fa-cog"></i>
			</button>
			<a class="navbar-brand" href="dashboards">Cakebox Dashboard<span class="logo-version">v<?= $version ?></span></a>
		</div>

		<!-- Collect the nav links, forms, and other content for toggling -->
		<div class="collapse navbar-collapse navbar-ex1-collapse">
			<ul class="nav navbar-nav navbar-right">
				<li>
					<a href="#" class="todo" data-toggle="dropdown">
						<i class="fa fa-sign-out"></i>
						<?= __('Logout'); ?>
					</a>
				</li>
			</ul>

		</div><!-- /.navbar-collapse -->
	</div> <!-- /.container -->
</nav>



<div class="subnavbar">

	<div class="subnavbar-inner">

		<div class="container">

			<a href="javascript:;" class="subnav-toggle" data-toggle="collapse" data-target=".subnav-collapse">
				<span class="sr-only"><?= __('Toggle Navigation'); ?></span>
				<i class="icon-reorder"></i>
			</a>

			<div class="collapse subnav-collapse">
				<ul class="mainnav">

					<!-- Home -->
					<li class="active">
						<?php
							echo $this->Html->link(
								'<i class="fa fa-home"></i><span></span>' . __('Dashboard'),
								['controller' => 'dashboards', 'action' => 'index'],
								['class' => 'shortcut', 'escape' => false]
							);
						?>
					</li>

					<!-- Backups -->
					<li>
						<?php
							echo $this->Html->link(
								'<i class="fa fa-cloud-download"></i><span></span>' . __('Backups'),
								'#',
								['class' => 'shortcut todo', 'escape' => false]
							);
						?>
					</li>

					<!-- Settings -->
					<li>
						<?php
							echo $this->Html->link(
								'<i class="fa fa-cog"></i><span></span>' . __('Settings'),
								'#',
								['class' => 'shortcut todo', 'escape' => false]
							);
						?>
					</li>

					<!-- Help -->
					<li>
						<?php
							echo $this->Html->link(
								'<i class="fa fa-question-circle"></i><span></span>' . __('Help'),
								'#',
								['class' => 'shortcut todo', 'escape' => false]
							);
						?>
					</li>

					<!-- Contribute -->
					<li>
						<?php
							echo $this->Html->link(
								'<i class="fa fa-github"></i><span></span>' . __('Contribute'),
								'https://www.github.com/alt3/cakebox-console',
								['class' => 'shortcut', 'escape' => false]
							);
						?>
					</li>

				</ul>
			</div> <!-- /.subnav-collapse -->

		</div> <!-- /container -->

	</div> <!-- /subnavbar-inner -->

</div> <!-- /subnavbar -->
