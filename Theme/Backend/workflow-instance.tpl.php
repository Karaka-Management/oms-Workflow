<?php
/**
 * Jingga
 *
 * PHP Version 8.2
 *
 * @package   Modules\Workflow
 * @copyright Dennis Eichhorn
 * @license   OMS License 2.2
 * @version   1.0.0
 * @link      https://jingga.app
 */
declare(strict_types=1);

/** @var \phpOMS\Views\View $this */
echo $this->data['nav']->render();
?>
<div class="row">
    <div class="col-xs-12 col-md-6">
        <section class="portlet">
            <div class="portlet-head"><?= $this->getHtml('Instance'); ?></div>
            <div class="portlet-body">
                <table class="list wf-100">
                    <tr>
                        <th><?= $this->getHtml('Workflow'); ?>
                        <td class="wf-100"><?= $this->printHtml($this->data['template']->name); ?>
                    <tr>
                        <th><?= $this->getHtml('Title'); ?>
                        <td><?= $this->printHtml($this->data['instance']->title); ?>
                    <tr>
                        <th><?= $this->getHtml('Created'); ?>
                        <td><?= $this->data['instance']->createdAt->format('Y-m-d H:i:s'); ?>
                    <tr>
                        <th><?= $this->getHtml('End'); ?>
                        <td><?= $this->data['instance']->end?->format('Y-m-d H:i:s'); ?>
                    <tr>
                        <th><?= $this->getHtml('Status'); ?>
                        <td><?= $this->getHtml(':istatus-' . $this->data['instance']->status); ?>
                    <tr><th colspan="2"><?= $this->getHtml('Data'); ?>
                    <tr><td colspan="2"><pre><?= $this->printHtml($this->data['instance']->data); ?></pre>
                </table>
            </div>
        </section>
    </div>
</div>
