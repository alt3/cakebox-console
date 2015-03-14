<?php
/**
* Cakebox usage
*/
$this->assign('title', 'Pro Tips!');
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
                            <h4><?= __("Cakebox Commands") ?></h4>
                            <p>
                                Run
                                <?php
                                    echo $this->Html->link('Cakebox Commands', 'http://cakebox.readthedocs.org/en/latest/usage/cakebox-commands/')
                                ?>
                                inside your virtual machine to create databases, virtual hosts and applications.
                            </p>
                            <ul>
                                <li><i class="fa fa-arrow-right"></i><em>cakebox --help</em></li>
                                <li><i class="fa fa-arrow-right"></i><em>cakebox site --help</em></li>
                                <li><i class="fa fa-arrow-right"></i><em>cakebox database --help</em></li>
                                <li><i class="fa fa-arrow-right"></i><em>cakebox application --help</em></li>
                            </ul>
                        </div>
                    </li>

                    <!-- Updates -->
                    <li id="faq-2">
                        <div class="faq-icon">
                            <div class="faq-number">2</div>
                        </div>
                        <div class="faq-text">
                            <h4><?= __("Updates") ?></h4>
                            <p>
                                Self-update your Cakebox Dashboard and Cakebox Commands by running
                                <?php
                                    echo $this->Html->link('cakebox update self', 'http://cakebox.readthedocs.org/en/latest/tutorials/updating-your-box/')
                                ?>
                                inside your box.
                            </p>
                        </div>
                    </li>

                    <!-- Backups -->
                    <li id="faq-3">
                        <div class="faq-icon">
                            <div class="faq-number">3</div>
                        </div>
                        <div class="faq-text">
                            <h4><?= __("Backups") ?></h4>
                            <p>
                                Create hot backups of your database server by running
                                <?php
                                    echo $this->Html->link('cakebox backup database', 'http://cakebox.readthedocs.org/en/latest/tutorials/updating-your-box/')
                                ?>
                                inside your box.
                            </p>
                        </div>
                    </li>

                    <!-- Provisioning -->
                    <li id="faq-4">
                        <div class="faq-icon">
                            <div class="faq-number">4</div>
                        </div>
                        <div class="faq-text">
                            <h4><?= __("Provisioning") ?></h4>
                            <p>
                                You are strongly advised to use your
                                <?php
                                    echo $this->Html->link('Cakebox.yaml', 'http://cakebox.readthedocs.org/en/latest/usage/cakebox-yaml/')
                                ?>
                                file to provision your box. This way you will
                                always be able to (re)create exact copies of
                                your box without losing your:
                            </p>
                            <ul>
                                <li><li><i class="fa fa-arrow-right"></i>virtual machine settings (hostname, IP address, CPUs, memory)</li>
                                <li><li><i class="fa fa-arrow-right"></i>databases</li>
                                <li><li><i class="fa fa-arrow-right"></i>virtual hosts</li>
                                <li><li><i class="fa fa-arrow-right"></i>Git credentials</li>
                                <li><li><i class="fa fa-arrow-right"></i>Git installed applications (public and private)</li>
                                <li><li><i class="fa fa-arrow-right"></i>Composer installed applications</li>
                                <li><li><i class="fa fa-arrow-right"></i>hardened SSH Authentication</li>
                                <li><li><i class="fa fa-arrow-right"></i>Vagrant Synced Folders</li>
                                <li><li><i class="fa fa-arrow-right"></i>additionally installed Ubuntu software</li>
                            </ul>
                        </div>
                    </li>

                    <!-- Vagrant CLI -->
                    <li id="faq-5">
                        <div class="faq-icon">
                            <div class="faq-number">5</div>
                        </div>
                        <div class="faq-text">
                            <h4><?= __("Vagrant Commands") ?></h4>
                            <p>
                                Your box lives is a Vagrant virtual machine so get familiar with the
                                <?php
                                    echo $this->Html->link('Vagrant CLI', 'https://docs.vagrantup.com/v2/cli/index.html')
                                ?>
                                .
                            </p>
                            <ul>
                                <li><i class="fa fa-arrow-right"></i><em>vagrant up</em> : start your box</li>
                                <li><i class="fa fa-arrow-right"></i><em>vagrant ssh</em> : log in to your box using SSH</li>
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
                    'https://cakebox.readthedocs.org',
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
