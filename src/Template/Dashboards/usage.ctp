<?php
/**
* Cakebox usage
*/
?>

<div class="index-main col-sm-8 column">
    <div class="widget stacked widget-table action-table">
        <div class="widget-content">
            <div class="panel-body faqs">
                <ol class="faq-list">

                    <!-- Cakebox Console -->
                    <li id="faq-1">
                        <div class="faq-icon">
                            <div class="faq-number">1</div>
                        </div>
                        <div class="faq-text">
                            <h4><?= __("Utilize the cakebox command line") ?></h4>
                            <p>
                                Run <em>cakebox CLI commands</em> inside your virtual machine to create databases, configure virtual hosts and install fully configured applications.
                                For more information run:
                            </p>
                            <ul>
                                <li><i class="fa fa-arrow-right"></i><em>cakebox application --help</em></li>
                                <li><i class="fa fa-arrow-right"></i><em>cakebox site --help</em></li>
                                <li><i class="fa fa-arrow-right"></i><em>cakebox database --help</em></li>
                                <li><i class="fa fa-arrow-right"></i><em>cakebox package --help</em></li>
                            </ul>
                        </div>
                    </li>

                    <!-- Self-Update -->
                    <li id="faq-2">
                        <div class="faq-icon">
                            <div class="faq-number">2</div>
                        </div>
                        <div class="faq-text">
                            <h4><?= __("Cakebox self-update") ?></h4>
                            <p>
                                Easily update your dashboard and cakebox commands to the latest version by running <em>cakebox update</em>.
                            </p>
                        </div>
                    </li>

                    <!-- YAML re-provisioning -->
                    <li id="faq-3">
                        <div class="faq-icon">
                            <div class="faq-number">3</div>
                        </div>
                        <div class="faq-text">
                            <h4><?= __("YAML (re)provisioning") ?></h4>
                            <p>
                                Use the <em>Cakebox.yaml</em> file to personalize your box.This way you will always be able
                                to create/restore an exact fresh copy of your customized box. Currently supports (re)provisioning:
                            </p>
                            <ul>
                                <li><li><i class="fa fa-arrow-right"></i>virtual machine settings (hostname, IP address, CPUs, memory)</li>
                                <li><li><i class="fa fa-arrow-right"></i>databases</li>
                                <li><li><i class="fa fa-arrow-right"></i>virtual hosts</li>
                                <li><li><i class="fa fa-arrow-right"></i>git hosted applications (both public and private repositories)</li>
                                <li><li><i class="fa fa-arrow-right"></i>composer installed applications</li>
                                <li><li><i class="fa fa-arrow-right"></i>secure SSH access setup (protecting your box with your own private key pair)</li>
                                <li><li><i class="fa fa-arrow-right"></i>auto configuring your Git credentials</li>
                                <li><li><i class="fa fa-arrow-right"></i>creating unlimited <?= $this->Html->link('Vagrant Synced Folders', 'https://docs.vagrantup.com/v2/synced-folders') ?></li>
                                <li><li><i class="fa fa-arrow-right"></i>installing additional Ubuntu software</li>
                            </ul>
                        </div>
                    </li>

                    <!-- Vagrant Commands -->
                    <li id="faq-4">
                        <div class="faq-icon">
                            <div class="faq-number">4</div>
                        </div>
                        <div class="faq-text">
                            <h4><?= __("Get familiar with Vagrant") ?></h4>
                            <p>
                                Since the Cakebox lives inside a Vagrant virtual machine it won't hurt to get familiar with
                                the Vagrant command line. Full documentation found <a href="https://docs.vagrantup.com/v2/cli/index.html">here</a> but to get you started:.
                            </p>
                            <ul>
                                <li><i class="fa fa-arrow-right"></i><em>vagrant up</em> : start your box</li>
                                <li><i class="fa fa-arrow-right"></i><em>vagrant ssh</em> : ssh into your box</li>
                                <li><i class="fa fa-arrow-right"></i><em>vagrant reload --provision</em> : restart and reconfigure your box using settings in your Cakebox.yaml</li>
                                <li><i class="fa fa-arrow-right"></i><em>vagrant suspend</em> : pause your box</li>
                                <li><i class="fa fa-arrow-right"></i><em>vagrant resume</em> : continue your box from paused state</li>
                                <li><i class="fa fa-arrow-right"></i><em>vagrant halt</em> : shut down your box</li>
                                <li><i class="fa fa-arrow-right"></i><em>vagrant destroy</em> : delete your box</li>
                            </ul>
                        </div>
                    </li>

                </ol>
            </div> <!-- EOF panel-body -->
        </div>
    </div>
</div>

<div class="col-sm-4 column">
    <div class="widget widget-plain">
        <div class="widget-content">
            <?php
                echo $this->Html->link(__('Read The Docs!'),
                    '#',
                    ['class' => 'todo btn btn-primary btn-usage-docs']
                );
                echo $this->Html->link(__('Report Issues'),
                    'https://github.com/alt3/cakebox-console/issues',
                    ['class' => 'btn btn-default btn-usage-issues']
                );
            ?>
        </div>
    </div>
</div>
