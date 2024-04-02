<?php
/**
 * Jingga
 *
 * PHP Version 8.2
 *
 * @package   Modules\Workflow
 * @copyright Dennis Eichhorn
 * @license   OMS License 2.0
 * @version   1.0.0
 * @link      https://jingga.app
 */
declare(strict_types=1);

use phpOMS\Uri\UriFactory;

/**
 * @var \phpOMS\Views\View                  $this
 * @var \Modules\Workflow\Models\Template[] $templates
 */
$templates = $this->data['reports'];

/** @var \Modules\Admin\Models\Account $account */
$account = $this->data['account'];

$accountDir = $account->id . ' ' . $account->login;

/** @var \Modules\Media\Models\Collection[] */
$collections = $this->data['collections'];
$mediaPath   = \urldecode($this->getData('path') ?? '/');

$previous = empty($templates) ? 'workflow/template/list' : '{/base}/workflow/template/list?{?}&offset=' . \reset($templates)->id . '&ptype=p';
$next     = empty($templates) ? 'workflow/template/list' : '{/base}/workflow/template/list?{?}&offset=' . \end($templates)->id . '&ptype=n';

echo $this->data['nav']->render(); ?>
<div class="row">
    <div class="col-xs-12">
        <div class="portlet">
            <div class="portlet-head"><?= $this->getHtml('Workflows'); ?><i class="g-icon download btn end-xs">download</i></div>
            <div class="slider">
            <table id="workflowTemplateList" class="default sticky">
                <thead>
                <tr>
                    <td><label class="checkbox" for="workflowTemplateList-0">
                            <input type="checkbox" id="workflowTemplateList-0" name="templateselect">
                            <span class="checkmark"></span>
                        </label>
                    <td>
                    <td><?= $this->getHtml('ID', '0', '0'); ?>
                    <td class="wf-100"><?= $this->getHtml('Name'); ?>
                        <label for="workflowTemplateList-sort-1">
                            <input type="radio" name="workflowTemplateList-sort" id="workflowTemplateList-sort-1">
                            <i class="sort-asc g-icon">expand_less</i>
                        </label>
                        <label for="workflowTemplateList-sort-2">
                            <input type="radio" name="workflowTemplateList-sort" id="workflowTemplateList-sort-2">
                            <i class="sort-desc g-icon">expand_more</i>
                        </label>
                        <label>
                            <i class="filter g-icon">filter_alt</i>
                        </label>
                    <td><?= $this->getHtml('Creator'); ?>
                        <label for="workflowTemplateList-sort-5">
                            <input type="radio" name="workflowTemplateList-sort" id="workflowTemplateList-sort-5">
                            <i class="sort-asc g-icon">expand_less</i>
                        </label>
                        <label for="workflowTemplateList-sort-6">
                            <input type="radio" name="workflowTemplateList-sort" id="workflowTemplateList-sort-6">
                            <i class="sort-desc g-icon">expand_more</i>
                        </label>
                        <label>
                            <i class="filter g-icon">filter_alt</i>
                        </label>
                    <td><?= $this->getHtml('Created'); ?>
                        <label for="workflowTemplateList-sort-7">
                            <input type="radio" name="workflowTemplateList-sort" id="workflowTemplateList-sort-7">
                            <i class="sort-asc g-icon">expand_less</i>
                        </label>
                        <label for="workflowTemplateList-sort-8">
                            <input type="radio" name="workflowTemplateList-sort" id="workflowTemplateList-sort-8">
                            <i class="sort-desc g-icon">expand_more</i>
                        </label>
                        <label>
                            <i class="filter g-icon">filter_alt</i>
                        </label>
                <tbody>
                <?php $count = 0; foreach ($templates as $key => $template) : ++$count;
                        $url = UriFactory::build('{/base}/workflow/template/view?{?}&id=' . $template->id); ?>
                <tr tabindex="0" data-href="<?= $url; ?>">
                    <td><label class="checkbox" for="workflowTemplateList-<?= $key; ?>">
                                    <input type="checkbox" id="workflowTemplateList-<?= $key; ?>" name="templateselect">
                                    <span class="checkmark"></span>
                                </label>
                    <td>
                    <td data-label="<?= $this->getHtml('ID', '0', '0'); ?>"><a href="<?= $url; ?>"><?= $template->id; ?></a>
                    <td data-label="<?= $this->getHtml('Name'); ?>"><a href="<?= $url; ?>"><?= $this->printHtml($template->name); ?></a>
                    <td data-label="<?= $this->getHtml('Creator'); ?>"><a class="content" href="<?= UriFactory::build('{/base}/profile/view?{?}&for=' . $template->createdBy->id); ?>"><?= $this->printHtml($this->renderUserName('%3$s %2$s %1$s', [$template->createdBy->name1, $template->createdBy->name2, $template->createdBy->name3, $template->createdBy->login ?? ''])); ?></a>
                    <td data-label="<?= $this->getHtml('Updated'); ?>"><a href="<?= $url; ?>"><?= $this->printHtml($template->createdAt->format('Y-m-d')); ?></a>
                        <?php endforeach; ?>
                        <?php if ($count === 0) : ?>
                <tr tabindex="0" class="empty">
                    <td colspan="4"><?= $this->getHtml('Empty', '0', '0'); ?>
                        <?php endif; ?>
            </table>
            </div>
            <!--
            <div class="portlet-foot">
                <a tabindex="0" class="button" href="<?= UriFactory::build($previous); ?>"><?= $this->getHtml('Previous', '0', '0'); ?></a>
                <a tabindex="0" class="button" href="<?= UriFactory::build($next); ?>"><?= $this->getHtml('Next', '0', '0'); ?></a>
            </div>
            -->
        </div>
    </div>
</div>
