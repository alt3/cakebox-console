<?php
/**
* Fork me on Github ribbon
*/
?>
<!-- Usage: Cakebox console -->
<div class="row">
	<div class="col-sm-12">
		<div class="panel panel-primary">
			<div class="panel-heading">
				<h3 class="panel-title"><?= __("Cakebox console") ?></div></h3>
			<div class="panel-body">
				<p>
					Use the <em><strong>cakebox commands</strong></em> to create databases, Nginx virtual hosts and fully pre-configured applications.
				</p>
				<p>For usage instructions see:<p>
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

<!-- Usage: Self-update -->
<div class="row">
	<div class="col-sm-12">
		<div class="panel panel-primary">
			<div class="panel-heading">
				<h3 class="panel-title"><?= __("Self-update") ?></h3>
			</div>
			<div class="panel-body">
				<p>
					Update this dashboard and your console commands to the latest version by running <em><strong>cakebox update</strong></em>.
				</p>
			</div>
		</div>
	</div>
</div>

<!-- Usage: YAML provisioning -->
<div class="row">
	<div class="col-sm-12">
		<div class="panel panel-primary">
			<div class="panel-heading">
				<h3 class="panel-title"><?= __("YAML provisioned virtual machine") ?></div></h3>
			<div class="panel-body">
				<p>Configure personal box customizations in the <em>Cakebox.yaml</em> file to:</p>
				<ul>
					<li>change virtual machine settings (hostname, IP address, CPUs, memory)</li>
					<li>provision databases, sites and applications</li>
					<li>provision your personal repository applications (both public and private)</li>
					<li>secure SSH access to your box using a private key pair</li>
					<li>auto configure your Git credentials</li>
					<li>create unlimited <?= $this->Html->link('Vagrant Synced Folders', 'https://docs.vagrantup.com/v2/synced-folders') ?></li>
					<li>provision additional software</li>
				</ul>
			</div>
		</div>
	</div>
</div>

<!-- Usage: YAML provisioning -->
<div class="row">
	<div class="col-sm-12">
		<div class="panel panel-primary">
			<div class="panel-heading">
				<h3 class="panel-title"><?= __("Vagrant commands") ?></div></h3>
			<div class="panel-body">
				<ul>
					<li><em>vagrant up</em> : start your box</li>
					<li><em>vagrant ssh</em> : ssh into your box</li>
					<li><em>vagrant reload --provision</em> : restart and reconfigure your box using settings in your Cakebox.yaml</li>
					<li><em>vagrant suspend</em> : pause your box</li>
					<li><em>vagrant resume</em> : continue your box from paused state</li>
					<li><em>vagrant halt</em> : shut down your box</li>
					<li><em>vagrant destroy</em> : delete your box</li>
				</ul>
			</div>
		</div>
	</div>
</div>
