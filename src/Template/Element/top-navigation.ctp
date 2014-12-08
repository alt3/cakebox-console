<?php
/**
* Bootstrap 3 top navigation
*/
?>

<nav class="navbar navbar-default navbar-top" role="navigation">
	<div class="container">
		<div class="col-sm-12 column">
			<div class="navbar-header">
				<a class="navbar-brand" href="dashboards">Cakebox Admin</a>
			</div>
			<div class="navbar-collapse collapse">
				<ul class="nav navbar-nav navbar-right">
					<li>
						<?= $this->Html->link('', [
							'title' => __('Settings'),
							'class' => 'fa fa-gear'
						]) ?>
					</li>
					<li><a title="<?= __('Settings') ?>" href="#" class="fa fa-gear"></a></li>
					<li><a title="<?= __('Logout') ?>" href="#" class="fa fa-sign-out"></a></li>
				</ul>
			</div>
		</div>
	</div>
</nav>
