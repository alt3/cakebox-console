<?php
/**
 * Bootstrap 3 top navigation
 */
//pr($data);
?>

<nav class="navbar navbar-inverse" role="navigation">

	<div class="container">
		<!-- Brand and toggle get grouped for better mobile display -->
		<div class="navbar-header">
			<button type="button" class="navbar-toggle" data-toggle="collapse" data-target=".navbar-ex1-collapse">
				<span class="sr-only"><?= __('Toggle navigation'); ?></span>
				<i class="fa fa-cog"></i>
			</button>
			<?php
                echo $this->Html->link(
                    "Cakebox Dashboard<span class='logo-version'>v$version</span>",
                    ['controller' => 'Dashboards', 'action' => 'index'],
                    ['class' => 'navbar-brand', 'escape' => false]
                );
            ?>
		</div>

		<!-- Collect the nav links, forms, and other content for toggling -->
		<div class="collapse navbar-collapse navbar-ex1-collapse">
			<ul class="nav navbar-nav navbar-right">

				<!-- notifications -->
				<?php if (empty($data['notifications'])) : ?>
					<li class="navbar-notifier">
						<a>Messages <span class="badge">0</span></a>
					</li>
				<?php else : ?>
					<li class="navbar-notifier has-messages">
						<a data-toggle="collapse" href="#collapseNotifications" aria-expanded="false" aria-controls="collapseExample">Messages <span class="badge"><?= count($data['notifications']) ?></span></a>
					</li>
				<?php endif ?>

				<!-- logout button -->
				<li>
					<a href="#" class="todo">
						<?= __('Logout'); ?>
						<i class="fa fa-sign-out"></i>
					</a>
				</li>
			</ul>

		</div><!-- /.navbar-collapse -->

		<!-- Collapsable Notification Well -->
		<?php if (!empty($data['notifications'])) : ?>
			<div class="collapse" id="collapseNotifications">
				<div class="well">
					<ul class="list-unstyled">
						<?php foreach($data['notifications'] as $notification) : ?>

							<li class="notification">
								<i class="fa fa-bell-o"></i>
								<?php if (!empty($notification['link'])) : ?>
									<?php
										echo sprintf ($notification['message'], $this->Html->link(
											$notification['link']['text'],
											$notification['link']['url']
										));
									?>
								<?php else : ?>
									<?= $notification['message'] ?>
								<?php endif ?>
							</li>

						<?php endforeach ?>
					</ul>
				</div>
			</div>
		<?php endif ?>

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
                                ['controller' => 'Dashboards', 'action' => 'index'],
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

					<!-- Contribute -->
					<li>
						<?php
                            echo $this->Html->link(
                                '<i class="fa fa-github"></i><span></span>' . __('Contribute'),
                                'https://cakebox.readthedocs.org/en/latest/additional/contributing/',
                                ['class' => 'shortcut', 'escape' => false]
                            );
                        ?>
					</li>

					<!-- Help -->
					<li>
						<?php
                            echo $this->Html->link(
                                '<i class="fa fa-book"></i><span></span>' . __('Docs'),
                                'https://cakebox.readthedocs.org',
                                ['class' => 'shortcut todo', 'escape' => false]
                            );
                        ?>
					</li>

				</ul>
			</div> <!-- /.subnav-collapse -->

		</div> <!-- /container -->

	</div> <!-- /subnavbar-inner -->





</div> <!-- /subnavbar -->
