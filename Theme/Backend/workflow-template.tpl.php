<?php
/**
 * Karaka
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

use phpOMS\Views\View;

/**
 * @var \phpOMS\Views\View                $this
 * @var \Modules\Workflow\Models\Template $template
 */
$template = $this->getData('template');

$actions = \json_decode(\file_get_contents(__DIR__ . '/../../Definitions/actions.json'), true);

function renderWorkflow(array $leaf, array $actions) : void
{
    foreach ($leaf as $e) {
        echo <<<NEWDOC
        <li>
            <span>
                <div class="portlet-head">{$actions[(int) $e['id']]['name']}</div>
                <div class="portlet-body">
                    <div class="form-group">
                        <label for="iId">Module</label>
                        <input type="text" value="{$actions[(int) $e['id']]['module']}" disabled>
                    </div>

                    <div class="form-group">
                        <label for="iId">Type</label>
                        <input type="text" value="{$actions[(int) $e['id']]['function_type']}" disabled>
                    </div>

                    <div class="form-group">
                        <label for="iId">Internal Function</label>
                        <input type="text" value="{$actions[(int) $e['id']]['function']}" disabled>
                    </div>
        NEWDOC;

        foreach ($actions[(int) $e['id']]['settings'] ?? [] as $key => $setting) {
            echo '<div class="form-group">'
                , '<label for="iId">' , $setting['title']['en'] , '</label>'
                , '<input name="' , $key , '" type="text" value="' , ($e['settings'][$key] ?? '') , '">'
                , '</div>';
        }

        echo '</div><div class="portlet-foot">'
            , '<input name="save" type="Submit" value="Save">'
            , '<input name="delete" class="cancel" type="Submit" value="Delete">'
            , '</div></span>';

        if (!empty($e['children'])) {
            echo '<ul>';
            \renderWorkflow($e['children'], $actions);
            echo '</ul>';
        }

        echo '</li>';
    }
}

function renderElements(array $leaf, array $actions) : void
{
    foreach ($leaf as $e) {
        $name = View::html($actions[(int) $e['id']]['name']);

        echo <<<NEWDOC
        <section class="portlet">
            <div class="portlet-head">{$name}</div>
            <div class="portlet-body"></div>
        </section>
        NEWDOC;

        \renderElements($e['children'], $actions);
    }
}

echo $this->getData('nav')->render(); ?>

<?php
if (!empty($template->schema)) :
    $level = $template->schema;
?>
    <div class="row">
        <div class="col-md-9 col-xs-12">
            <section class="portlet sticky">
                <div class="portlet-head"><?= $this->getHtml('Workflow'); ?></div>
                <div class="portlet-body">
                    <ul class="tree center">
                    <?php
                        \renderWorkflow($level, $actions);
                    ?>
                    </ul>
                </div>
            </section>
        </div>

        <div class="col-md-3 col-xs-12">
            <section class="portlet">
                <div class="portlet-body"><?= $this->getHtml('Available'); ?></div>
            </section>

            <?php foreach ($actions as $action) : ?>
                <section class="portlet">
                    <div class="portlet-body"><?= $this->printHtml($action['name']); ?></div>
                    <div class="portlet-body"><?= $this->printHtml($action['description'][$this->request->header->l11n->language]); ?></div>
                </section>
            <?php endforeach; ?>
        </div>
    </div>
<?php endif; ?>