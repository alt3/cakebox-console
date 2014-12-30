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
			<a class="navbar-brand" href="dashboards">Cakebox Admin<span class="logo-version">0.1.0</span></a>
		</div>

		<!-- Collect the nav links, forms, and other content for toggling -->
		<div class="collapse navbar-collapse navbar-ex1-collapse">
			<ul class="nav navbar-nav navbar-right">
				<li class="dropdown">

					<a href="javscript:;" class="dropdown-toggle" data-toggle="dropdown">
						<i class="fa fa-cog"></i>
						Settings
						<b class="caret"></b>
					</a>

					<ul class="dropdown-menu">
						<li><a href="./account.html"><?= __('Account Settings'); ?></a></li>
						<li><a href="javascript:;"><?= __('Privacy Settings'); ?></a></li>
						<li class="divider"></li>
						<li><a href="javascript:;"><?= __('Help'); ?></a></li>
					</ul>

				</li>

				<li class="dropdown">

					<a href="javscript:;" class="dropdown-toggle" data-toggle="dropdown">
						<i class="fa fa-user"></i>
						<?= __('Username'); ?>
						<b class="caret"></b>
					</a>

					<ul class="dropdown-menu">
						<li><a href="javascript:;"><?= __('Logout'); ?></a></li>
					</ul>

				</li>
			</ul>

			<form class="navbar-form navbar-right" role="search">
				<div class="form-group">
					<input type="text" class="form-control input-sm search-query" placeholder="Search">
				</div>
			</form>
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
								'<i class="fa fa-home"></i><span />' . __('Dashboard'),
								['controller' => 'dashboards', 'action' => 'index'],
								['class' => 'shortcut', 'escape' => false]
							);
						?>
					</li>

					<li class="dropdown">
						<a href="javascript:;" class="dropdown-toggle" data-toggle="dropdown">
							<i class="fa fa-th"></i>
							<span><?= __('Components'); ?></span>
							<b class="caret"></b>
						</a>

						<ul class="dropdown-menu">
							<li><a href="./elements.html"><?= __('Elements'); ?></a></li>
							<li><a href="./forms.html"><?= __('Form Styles'); ?></a></li>
						</ul>
					</li>

					<li class="dropdown">
						<a href="javascript:;" class="dropdown-toggle" data-toggle="dropdown">
							<i class="fa fa-copy"></i>
							<span><?= __('Sample Pages'); ?></span>
							<b class="caret"></b>
						</a>

						<ul class="dropdown-menu">
							<li><a href="./pricing.html"><?= __('Pricing Plans'); ?></a></li>
							<li><a href="./faq.html"><?= __('FAQ\'s'); ?></a></li>
							<li><a href="./gallery.html"><?= __('Gallery'); ?></a></li>
						</ul>
					</li>

					<li class="dropdown">
						<a href="javascript:;" class="dropdown-toggle" data-toggle="dropdown">
							<i class="fa fa-external-link"></i>
							<span><?= __('Extra Pages'); ?></span>
							<b class="caret"></b>
						</a>

						<ul class="dropdown-menu">
							<li><a href="./login.html">Login</a></li>
							<li><a href="./signup.html">Signup</a></li>
							<li><a href="./error.html">Error</a></li>
							<li class="dropdown-submenu">
								<a tabindex="-1" href="#">More options</a>
								<ul class="dropdown-menu">
									<li><a tabindex="-1" href="#">Second level</a></li>

									<li><a href="#">Second level</a></li>
									<li><a href="#">Second level</a></li>
								</ul>
							</li>
						</ul>
					</li>

					<!-- Backups -->
					<li>
						<?php
							echo $this->Html->link(
								'<i class="fa fa-cloud-download"></i><span />' . __('Backups'),
								['controller' => 'dashboards', 'action' => 'index'],
								['class' => 'shortcut', 'escape' => false]
							);
						?>
					</li>


				</ul>
			</div> <!-- /.subnav-collapse -->

		</div> <!-- /container -->

	</div> <!-- /subnavbar-inner -->

</div> <!-- /subnavbar -->
