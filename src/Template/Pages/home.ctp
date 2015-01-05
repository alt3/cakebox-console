<?php
/**
 * Placeholder login view.
 *
 * @todo move once hooked into Auth
 */
?>

<div class="account-container stacked">

	<div class="content clearfix">

		<form action="dashboards" method="post">

			<h1><?= __('Sing in') ?></h1>
			<div class="login-fields">
				<p><?= __('Unleash your box') ?></p>
				<div class="field">
					<label for="username"><?= __('Username') ?>:</label>
					<input type="text" id="username" name="username" value="" placeholder="<?= __('Username') ?>" class="form-control input-lg username-field" />
				</div>

				<div class="field">
					<label for="password"><?= __('Password') ?>:</label>
					<input type="password" id="password" name="password" value="" placeholder="<?= __('Password') ?>" class="form-control input-lg password-field"/>
				</div>
			</div>

			<div class="login-actions">
				<span class="login-checkbox">
					<input id="Field" name="Field" type="checkbox" class="field login-checkbox" value="First Choice" tabindex="4" />
					<label class="choice" for="Field"><?= __('Keep me signed in') ?></label>
				</span>
				<a href="/dashboards" class="login-action btn btn-primary"><?= __('Sign in') ?></a>
			</div>

		</form>

	</div> <!-- /content -->

</div> <!-- /account-container -->


<!-- Text Under Box -->
<div class="login-extra">
	Powered by <a href="http://www.cakephp.org">CakePHP 3</a><br/>
</div> <!-- /login-extra -->
