<?php
use App\Form\SiteFileForm;

$this->assign('title', 'Virtual Hosts');
?>

<div class="index-main col-sm-10 column">

    <!-- Ajax success message -->
    <div class="alert alert-success alert-dismissible collapse" role="alert">
        <button type="button" class="close" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        <span class="message">
            ajax-loaded message
        </span>
    </div>

    <!-- Sitefiles widget -->
    <div class="widget stacked widget-table action-table">

        <div class="widget-header">
            <i class="fa fa-file-text-o"></i>
            <h3><?= __('Nginx Virtual Hosts') ?></h3>
        </div>

        <div class="widget-content">
            <div class="panel-body">
                <table class="table collection">
                    <caption>
                        <?= __('As found in ') . $data['directories']['sites-available'] ?>
                    </caption>
                    <thead>
                        <tr>
                            <th>#</th>
                            <th><?= __("Site File") ?></th>
                            <th><?= __("Enabled") ?></th>
                            <th><?= __("Last Modified") ?></th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        foreach ($data['sitefiles'] as $key => $file) :
                        ?>
                            <tr>
                                <td class="index"><?= $key + 1 ?></td>
                                <td class="filename"><?= $file['name'] ?></td>
                                <td><?= $file['enabled'] ? __('Yes') : __('No') ?></td>
                                <td><?= $this->Time->format($file['modified'], 'YYYY-MM-dd'); ?></td>
                                <td class="actions">
                                    <div class="btn-group pull-right">
                                        <button type="button" class="btn btn-default btn-sm" data-toggle="modal" data-target="#fileModal">
                                            <?= __('View') ?>
                                        </button>
                                        <button type="button" class="confirm delete btn btn-danger btn-sm" rel="sitefiles/ajaxDelete">
                                            <?= __('Delete') ?>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        <?php
                        endforeach;
                        ?>
                    </tbody>
                </table>
            </div>

        </div> <!-- /widget-content -->

    </div> <!-- /widget -->

</div> <!-- col-sm-10 -->

<!-- Stats -->
<div class="col-sm-2 column">
    <div class="widget stacked widget-table action-table">
        <div class="widget-header">
            <i class="fa fa-star"></i>
            <h3><?= __('Stats') ?></h3>
        </div>

        <div class="widget-content">
            <div class="panel-body">
        </div>
    </div>
</div>

<!-- View Modal -->
<div class="modal fade" id="fileModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
                <h4 class="modal-title">ajax-loaded-title</h4>
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
