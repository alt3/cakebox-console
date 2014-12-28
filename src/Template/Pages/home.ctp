<?php
/**
 * Placeholder login view.
 *
 * @todo move once hooked into Auth
 */
?>

<div class="account-container stacked">

	<div class="content clearfix">

		<form action="./index.html" method="post">

			<h1>Sign In</h1>

			<div class="login-fields">

				<p>Unleash your box!</p>

				<div class="field">
					<label for="username">Username:</label>
					<input type="text" id="username" name="username" value="" placeholder="Username" class="form-control input-lg username-field" />
				</div> <!-- /field -->

				<div class="field">
					<label for="password">Password:</label>
					<input type="password" id="password" name="password" value="" placeholder="Password" class="form-control input-lg password-field"/>
				</div> <!-- /password -->

			</div> <!-- /login-fields -->

			<div class="login-actions">

				<span class="login-checkbox">
					<input id="Field" name="Field" type="checkbox" class="field login-checkbox" value="First Choice" tabindex="4" />
					<label class="choice" for="Field">Keep me signed in</label>
				</span>

				<button class="login-action btn btn-primary">Sign In</button>

			</div> <!-- .actions -->

		</form>

	</div> <!-- /content -->

</div> <!-- /account-container -->


<!-- Text Under Box -->
<div class="login-extra">
	Powered by <a href="http://www.cakephp.org">CakePHP 3</a><br/>
</div> <!-- /login-extra -->
