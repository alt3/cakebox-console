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
						<?= $this->Html->link('', '#', ['title' => __('Settings'), 'class' => 'fa fa-gear']) ?>
					</li>
					<li>
						<?= $this->Html->link('', 'http://cakebox.readthedocs.org/en/latest/', ['title' => __('Help'), 'class' => 'fa fa-question-circle']) ?>
					</li>
					<li>
						<?= $this->Html->link('', '#', ['title' => __('Logout'), 'class' => 'fa fa-sign-out']) ?>
					</li>
				</ul>
			</div>
		</div>
	</div>
</nav>
