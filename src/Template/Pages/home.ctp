<?php
/**
 * Placeholder login view.
 *
 * @todo move once hooked into Auth
 */
?>
<div id="loginbox" class="row col-md-4 col-md-offset-4">
	<div class="panel panel-default">
		<div class="panel-heading">
			<div class="panel-title"><?= __('Unleash your box!') ?></div>
		</div>
		<div class="panel-body">

			<!-- Login form -->
			<form id="loginform" class="form-horizontal" role="form" accept-charset="UTF-8">

				<!-- Username/password -->
				<div style="margin-bottom: 25px" class="input-group">
					<span class="input-group-addon"><i class="fa fa-user"></i></span>
					<input id="login-username" class="form-control" name="username" value="" placeholder="<?= __('username') ?>" type="text">
				</div>

				<div style="margin-bottom: 25px" class="input-group">
					<span class="input-group-addon"><i class="fa fa-lock"></i></span>
					<input id="login-password" class="form-control" name="password" placeholder="<?= __('password') ?>" type="password">
				</div>

				<!-- Button -->
				<div style="margin-top:10px" class="form-group pull-right">
					<div class="col-sm-12 controls">
						<?= $this->Html->link(__('Login'), 'dashboards', ['id' => 'btn-login', 'class' => 'btn btn-success']) ?>
					</div>
				</div>

				<!-- Credits -->
				<div class="form-group">
					<div class="col-md-12 control">
						<div class="login-credits">
							Powered by <?= $this->Html->link('CakePHP 3', 'http://cakephp.org') ?>
						</div>
					</div>
				</div>

			</form>
			<!-- End of login form -->
		</div>
		<!-- EOF panel-body -->
	</div>
	<!-- EOF panel -->
</div>
