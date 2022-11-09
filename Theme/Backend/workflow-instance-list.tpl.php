<?php
/**
 * Karaka
 *
 * PHP Version 8.1
 *
 * @package   Modules\Workflow
 * @copyright Dennis Eichhorn
 * @license   OMS License 1.0
 * @version   1.0.0
 * @link      https://karaka.app
 */
declare(strict_types=1);

/**
 * @var \phpOMS\Views\View $this
 */
$instances = $this->getData('instances') ?? [];

echo $this->getData('nav')->render(); ?>

<div class="row">
    <div class="col-xs-12">
        <div class="portlet">
            <div class="portlet-head"><?= $this->getHtml('instance'); ?><i class="fa fa-download floatRight download btn"></i></div>
            <table class="default">
                <thead>
                    <td><?= $this->getHtml('Status'); ?>
                    <td class="wf-100"><?= $this->getHtml('Title'); ?>
                <tbody>
                <?php
                    $c = 0;
                    foreach ($instances as $key => $instance) : ++$c;
                        $url = \phpOMS\Uri\UriFactory::build('workflow/instance/profile?{?}&id=' . $instance->getId());
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