<?php
/**
 * Jingga
 *
 * PHP Version 8.1
 *
 * @package   Modules\Workflow
 * @copyright Dennis Eichhorn
 * @license   OMS License 2.0
 * @version   1.0.0
 * @link      https://jingga.app
 */
declare(strict_types=1);

/**
 * @var \phpOMS\Views\View $this
 */
$instances = $this->data['instances'] ?? [];

echo $this->data['nav']->render(); ?>

<div class="row">
    <div class="col-xs-12">
        <div class="portlet">
            <div class="portlet-head"><?= $this->getHtml('Instances'); ?><i class="lni lni-download download btn end-xs"></i></div>
            <table class="default">
                <thead>
                    <td><?= $this->getHtml('Status'); ?>
                    <td class="wf-100"><?= $this->getHtml('Title'); ?>
                <tbody>
                <?php
                    $c = 0;
                    foreach ($instances as $key => $instance) : ++$c;
                        $url = \phpOMS\Uri\UriFactory::build('{/base}/admin/instance/single?{?}&id=' . $instance->id);
                ?>
                <tr data-href="<?= $url; ?>">
                    <td><a href="<?= $url; ?>"><?= $this->printHtml((string) $instance->getStatus()); ?></a>
                    <td><a href="<?= $url; ?>"><?= $this->printHtml($instance->title); ?></a>
                <?php endforeach; if ($c == 0) : ?>
                <tr><td colspan="6" class="empty"><?= $this->getHtml('Empty', '0', '0'); ?>
                        <?php endif; ?>
            </table>
        </div>
    </div>
</div>