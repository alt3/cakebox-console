<?php
/**
* Fork me on Github ribbon
*/
?>
<!-- Usage: Cakebox console -->
<div class="row">
	<div class="col-sm-12">
		<div class="panel panel-default">
			<div class="panel-heading"><?= __("Cakebox console") ?></div>
			<div class="panel-body">
				<p>
					Use the command line to create databases, Nginx virtual hosts and completely pre-configured applications.
					For usage instructions run the following commands inside your box:
				</p>
				<ul>
					<li><em>cakebox application --help</em></li>
					<li><em>cakebox site --help</em></li>
					<li><em>cakebox database --help</em></li>
					<li><em>cakebox package --help</em></li>
				</ul>
			</div>
		</div>
	</div>
</div>

<!-- Usage: Updates -->
<div class="row">
	<div class="col-sm-12">
		<div class="panel panel-default">
			<div class="panel-heading danger"><?= __("Self-update") ?></div>
			<div class="panel-body">
				<p>
					Update your Cakebox console and dashboard to the latest version by running:
				</p>
				<ul class="list-unstyled">
					<li><em>cakebox update</em></li>
				</ul>
			</div>
		</div>
	</div>
</div>

<!-- Usage: YAML provisioning -->
<div class="row">
	<div class="col-sm-12">
		<div class="panel panel-default">
			<div class="panel-heading"><?= __("YAML provisioned virtual machine") ?></div>
			<div class="panel-body">
				<p>Your box is highly customizable and uses your personal settings in the <em>Cakebox.yaml</em> file to:</p>
				<ul>
					<li>change virtual machine settings (hostname, IP address, CPUs, memory)</li>
					<li>secure SSH access using your own key pair</li>
					<li>auto configure your Git credentials</li>
					<li>automatically provision databases, sites and applications</li>
					<li>create unlimited <?= $this->Html->link('Vagrant Synced Folders', 'https://docs.vagrantup.com/v2/synced-folders') ?></li>
					<li>install additional software</li>
				</ul>
			</div>
		</div>
	</div>
</div>

<!-- Usage: YAML provisioning -->
<div class="row">
	<div class="col-sm-12">
		<div class="panel panel-default">
			<div class="panel-heading"><?= __("Vagrant commands") ?></div>
			<div class="panel-body">
				<p>Common `vagrant` commands to be run from your hosts command line:</p>
				<ul>
					<li><em>`vagrant up`</em>: start your box</li>
					<li><em>`vagrant ssh`</em>: ssh into your box</li>
					<li><em>`vagrant reload --provision`</em>: restart and reconfigure your box using settings in your Cakebox.yaml</li>
					<li><em>`vagrant halt`</em>: stop your box</li>
					<li><em>`vagrant destroy`</em>: stop the box an remove from your system</li>
				</ul>
			</div>
		</div>
	</div>
</div>
