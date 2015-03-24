<?php

use Cake\Utility\Hash;
use Cake\Utility\Inflector;

// Load Jquery flot plugin to generate the donut
$this->Html->script('cdn-fallback/jquery-plugins/flot/jquery.flot.min', ['block' => 'scriptBottom']);
$this->Html->script('cdn-fallback/jquery-plugins/flot/jquery.flot.pie.min', ['block' => 'scriptBottom']);
$this->Html->script('cdn-fallback/jquery-plugins/flot/jquery.flot.resize.min', ['block' => 'scriptBottom']);

?>

<div class="col-md-6 col-xs-12">

    <!-- Quick stats widget -->
    <div class="widget stacked">

        <div class="widget-header">
            <i class="fa fa-star"></i>
            <h3><?= __('Stats') ?></h3>
        </div> <!-- /widget-header -->

        <div class="widget-content dashboard-stats">

            <div class="row stats">

                <!-- Application counter -->
                <div class="col-xs-4">
                    <span class="stat-value"><?= count($data['apps']) ?></span>
                    <span class="stat-key"><?= __('Applications'); ?></span>
                </div>

                <div class="col-xs-4">
                    <span class="stat-value"><?= $data['counters']['databases'] ?></span>
                    <span class="stat-key"><?= __('Databases'); ?></span>
                </div>

                <div class="col-xs-4">
                    <span class="stat-value"><?= $data['counters']['sites'] ?></span>
                    <span class="stat-key"><?= __('Virtual Hosts'); ?></span>
                </div>

            </div> <!-- /stats -->


            <div id="chart-stats" class="stats">

                <div class="stat stat-chart">
                    <div id="donut-chart" class="chart-holder"></div> <!-- #donut -->
                </div> <!-- /substat -->

                <div class="stat stat-time">
                    <span class="stat-value">
                        <span class="uptime-component">
                            <span><?= $data['vm']['uptime']['days'] ?></span>
                            <span class="uptime-key">d</span>
                        </span>

                        <span class="uptime-component">
                            <span class="uptime-value"><?= $data['vm']['uptime']['hours'] ?></span>
                            <span class="uptime-key">h</span>
                        </span>

                        <span class="uptime-component">
                            <span class="uptime-value"><?= $data['vm']['uptime']['minutes'] ?></span>
                            <span class="uptime-key">m</span>
                        </span>
                    </span>
                    <div class="uptime-title">
                        <?= __('System Uptime') ?>
                    </div>
                </div> <!-- /substat -->

            </div> <!-- /substats -->

        </div> <!-- /widget-content -->

    </div> <!-- /widget -->


    <!-- Applications widget (only use table CSS when user has apps)-->
    <?php
    if (!empty($data['apps'])) :
    ?>
    <div class="widget stacked widget-table action-table">
    <?php
    else :
    ?>
    <div class="widget stacked">
    <?php
    endif;
    ?>

        <div class="widget-header">
            <i class="fa fa-th-list"></i>
            <h3><?= __('Applications') ?></h3>
        </div> <!-- /widget-header -->

        <div class="widget-content">
            <?php
            if (!empty($data['apps'])) :
            ?>
            <table class="table table-striped table-bordered">
                <tbody>

                    <?php
                    foreach ($data['apps'] as $app) :
                    ?>
                        <tr>
                            <td class="app-name">
                                <?= $this->Html->link($app['name'], "http://" . $app['name'], ['class' => 'app-link']) ?>
                            </td>
                            <td class="app-version">
                                <?= $app['framework_human'] ?> <?= $app['framework_version'] ?>
                            </td>
                            <td class="app-actions">
                                <?php
                                    echo $this->Html->link(
                                        '<i class="btn-icon-only fa fa-share"></i>' . '',
                                        ['controller' => 'Applications', 'action' => 'index'],
                                        ['escape' => false, 'class' => 'btn btn-xs btn-primary']
                                    );
                                ?>
                            </td>
                        </tr>
                    <?php
                    endforeach;
                    ?>

                </tbody>
            </table>
            <?php
            else :
            ?>
            <p>
                Follow
                <?php
                    echo $this->Html->link(
                        __('these instructions'),
                        'http://cakebox.readthedocs.org/en/latest/tutorials/creating-your-first-website/'
                    );
                ?>
                to create your first application.
            </p>

            <?php
            endif;
            ?>

        </div> <!-- /widget-content -->

    </div> <!-- /widget -->

</div> <!-- /span6 -->


<div class="col-md-6">

    <div class="widget stacked">

        <div class="widget-header">
            <i class="fa fa-bookmark"></i>
            <h3><?= __('Shortcuts') ?></h3>
        </div> <!-- /widget-header -->

        <div class="widget-content">

            <div class="shortcuts">
                <?php
                    // Applications
                     echo $this->Html->link(
                         '<i class="shortcut-icon fa fa-code-fork"></i><span class="shortcut-label"></span>' . __('Applications'),
                         ['controller' => 'Applications', 'action' => 'index'],
                         ['class' => 'shortcut', 'escape' => false, 'title' => 'Not implemented yet']
                     );

                    // Databases
                     echo $this->Html->link(
                         '<i class="shortcut-icon fa fa-database"></i><span class="shortcut-label"></span>' . __('Databases'),
                         ['controller' => 'Databases', 'action' => 'index'],
                         ['class' => 'shortcut', 'escape' => false, 'title' => 'Not implemented yet']
                     );

                    // Site files
                     echo $this->Html->link(
                         '<i class="shortcut-icon fa fa-file-text-o"></i><span class="shortcut-label"></span>' . __('Virtual Hosts'),
                         ['controller' => 'Vhosts', 'action' => 'index'],
                         ['class' => 'shortcut', 'escape' => false]
                     );

                    // Pro Tips
                     echo $this->Html->link(
                         '<i class="shortcut-icon fa fa-lightbulb-o"></i><span class="shortcut-label"></span>' . __('Pro Tips!'),
                         ['controller' => 'Dashboards', 'action' => 'usage'],
                         ['class' => 'shortcut', 'escape' => false]
                     );

                    // Virtual Machine
                     echo $this->Html->link(
                         '<i class="shortcut-icon fa fa-cube"></i><span class="shortcut-label"></span>' . __('Virtual Machine'),
                         ['controller' => 'Dashboards', 'action' => 'vm'],
                         ['class' => 'shortcut', 'escape' => false]
                     );

                    // Kibana: dirty https tp http hack until figured out how to do otherwise
                     $kibanaLink = $this->Html->link(
                         '<i class="shortcut-icon fa fa-bar-chart"></i><span class="shortcut-label"></span>' . __('Kibana'),
                         [
                            '_port' => '5601',
                            'controller' => false,
                            'action' => false
                         ],
                         ['class' => 'shortcut', 'escape' => false]
                     );
                     echo str_replace('https', 'http', $kibanaLink);

                    // Elasticsearch: dirty https tp http hack until figured out how to do otherwise
                     $elasticSearchLink = $this->Html->link(
                         '<i class="shortcut-icon fa fa-search"></i><span class="shortcut-label"></span>' . __('Elasticsearch'),
                         [
                            '_port' => '9200',
                            'controller' => false,
                            'action' => false
                         ],
                         ['class' => 'shortcut', 'escape' => false]
                     );
                     echo str_replace('https', 'http', $elasticSearchLink);

                    // Credits
                     echo $this->Html->link(
                         '<i class="shortcut-icon fa fa-graduation-cap"></i><span class="shortcut-label"></span>' . __('Wall of Fame'),
                         '#',
                         ['class' => 'shortcut todo', 'escape' => false]
                     );
                ?>
            </div> <!-- /shortcuts -->

        </div> <!-- /widget-content -->

    </div> <!-- /widget -->



    <!-- Sponsors widget -->
    <div class="widget sponsors stacked">

        <div class="widget-header">
            <button type="button" class="close" id="close-sponsors" data-dismiss="widget" aria-label="Close"><span aria-hidden="true">&times;</span></button>
            <i class="fa fa-bullhorn"></i>
            <h3><?= __('Your Sponsors') ?></h3>
        </div> <!-- /widget-header -->

        <div class="widget-content">
            <p>Theme sponsored by the kind folks at <?= $this->Html->link('Your Name Here', 'http://google.com') ?></p>
            <p>Box image CDN provided by <?= $this->Html->link('Your Name Here', 'http://google.com') ?></p>
        </div> <!-- /widget-content -->

    </div> <!-- /widget -->


    <!-- Recent Contributions widget -->
    <div class="widget widget-nopad stacked">

        <div class="widget-header">
            <i class="fa fa-heart"></i>
            <h3><?= __('Recent Contributions') ?></h3>
        </div> <!-- /widget-header -->

        <div class="widget-content">

            <ul class="pull-requests list-unstyled">
                <?php
                if (empty($data['contributions'])) :
                ?>
                    <li class="pull-request api-failure">
                        <p class="text-danger"><?= __('Looks the Github API is having an off day') ?><i class="fa fa-exclamation-circle"></i></p>
                    </li>
                <?php
                else :
                ?>
                    <?php
                    foreach ($data['contributions'] as $pullRequest) :
                    ?>
                        <li class="pull-request">
                            <div class="row row-list">
                                <div class="col-xs-1 avatar">
                                    <?php
                                    if (empty($pullRequest['user']['avatar_data'])) {
                                        echo $this->Html->image(
                                            $pullRequest['user']['avatar_url'] . '&amp;size=40',
                                            ['alt' => $pullRequest['user']['login']]
                                        );
                                    } else {
                                        $blobImage = '<img src="data:image/jpeg;base64,' . $pullRequest['user']['avatar_data'] . '" alt="' . $pullRequest['user']['login'] . '">';
                                        echo $blobImage;
                                    }
                                    ?>
                                </div>
                                <div class="col-xs-9 details">
                                    <?= $this->Html->link($pullRequest['user']['login'], $pullRequest['user']['html_url']) ?>

                                    <span class="message">
                                        <?= $pullRequest['title'] ?>
                                        <?php
                                            echo $this->Html->link(
                                                '<i class="fa fa-share"></i>' . '',
                                                $pullRequest['html_url'],
                                                ['escape' => false, 'class' => 'link']
                                            );
                                        ?>
                                    </span>
                                </div>
                                <div class="col-xs-2 date pull-right">
                                    <span class="day"><?= (new DateTime($pullRequest['merged_at']))->format("d") ?></span>
                                    <span class="month"><?= (new DateTime($pullRequest['merged_at']))->format("M") ?></span>
                                </div>
                            </div>
                        </li>
                    <?php
                    endforeach;
                    ?>
                <?php
                endif;
                ?>
            </ul>

        </div> <!-- /widget-content -->

    </div> <!-- /widget -->


</div> <!-- /span6 -->

</div> <!-- closes row from layout.ctp -->

</div> <!-- / closes .content in layout.ctp -->

</div> <!-- /closes .container in layout.ctp -->


<!-- Extra bottom panel/footer (dashboard index only) -->
<div class="extra">

    <div class="container">

        <div class="row">

            <!-- About -->
            <div class="col-md-3">
                <h4>About</h4>
                <ul>
                    <li><a href="#"></a></li>
                    <li><a href="#"></a></li>
                    <li><a href="#"></a></li>
                    <li><a href="#"></a></li>
                </ul>
            </div> <!-- /span3 -->

            <!-- Support -->
            <div class="col-md-3">
                <h4>Support</h4>
                <ul>
                    <li><a href="https://cakebox.readthedocs.org" class="todo">Documentation</a></li>
                    <li><a href="#"></a></li>
                    <li><a href="#"></a></li>
                    <li><a href="#"></a></li>
                </ul>
            </div>

            <!-- Legal -->
            <div class="col-md-3">
                <h4>Legal</h4>
                <ul>
                    <li>
                        <?php
                            echo $this->Html->link(
                                __('License'),
                                ['controller' => 'Dashboards', 'action' => 'license.json'],
                                ['class' => 'ajax-file-modal', 'id' => 'license']
                            );
                        ?>
                    </li>
                    <li><a href="#"></a></li>
                    <li><a href="#"></a></li>
                    <li><a href="#"></a></li>
                </ul>
            </div>

            <!-- Links -->
            <div class="col-md-3">
                <h4>Links</h4>
                <ul>
                    <li>
                        <?php
                            echo $this->Html->link(
                                '<i class="extra-icon fa fa-github"></i>' . 'Github',
                                'https://github.com/alt3/cakebox-console',
                                ['escape' => false, 'escapeTitle' => false, 'title' => 'View our sourcecode on Github']
                            );
                        ?>
                    </li>

                    <li>
                        <?php
                            echo $this->Html->link(
                                $this->Html->image("frameworks/cakephp.icon.png", ['class' => 'extra-icon']) . 'CakePHP',
                                "http://cakephp.org",
                                ['escape' => false, 'escapeTitle' => false, 'title' => 'CakePHP: the rapid development php framework']
                            );
                        ?>
                    </li>

                    <li>
                        <?php
                            echo $this->Html->link('Jumpstart Themes', 'https://jumpstartthemes.com', ['title' => 'Jumpstart Themes: Effortless Twitter Bootstrap Themes']);
                        ?>
                    </li>
                    <li><a href="#"></a></li>
                </ul>
            </div>

<!-- Closed by layout.ctp divs -->

<!-- Ajax loaded modal -->
<div id="ajaxModal" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="ajaxModal" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
                <h4 class="modal-title" id="myModalLabel">ajax-loaded-title</h4>
            </div>
            <div class="modal-body">
                ajax-loaded-content
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<?php

$flotData = [];
// Count the number of app per unique framework to feed the donut
$frameworks = array_values(array_unique(Hash::extract($data['apps'], '{n}.framework_human')));
foreach ($frameworks as $framework) {
    $frameworkCount = count(Hash::extract($data['apps'], "{n}[framework_human=/$framework/].name"));
    $flotData[] = [
        'label' => $framework,
        'data' => $frameworkCount
    ];
}

// Create inline Javascript variable "donutData"
echo $this->Html->scriptBlock(
    "var donutData = " . json_encode($flotData),
    ['inline' => false]
);
