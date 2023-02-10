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
 * @link      https://jingga.app
 */
declare(strict_types=1);

use phpOMS\Uri\UriFactory;
use phpOMS\Views\View;

/**
 * @var \phpOMS\Views\View                  $this
 * @var \Modules\Workflow\Models\Template $template
 */
$template = $this->getData('template');

$actions = \json_decode(\file_get_contents(__DIR__ . '/../../Definitions/actions.json'), true);

function renderLeaf(array $leaf, string $parent = null)
{
    $boxes = [
        'first' => ['((', '))'],
        'standard' => ['[', ']'],
        'if' => ['{', '}']
    ];

    foreach ($leaf as $key => $e) {
        $type = $e['id'] === '1005500001'
            ? 'if'
            : 'standard';

        if ($parent !== null) {
            echo '    ' . $parent . '-->' . $parent . ':' . $key . ':' . $e['id'] .  $boxes[$type][0] . $e['id'] .  $boxes[$type][1] . ";\n";
        } else {
            echo '    ' . $key . ':' . $e['id'] . $boxes['first'][0] . $e['id'] . $boxes['first'][1] . ";\n";
        }

        renderLeaf($e['children'], ($parent === null ? '' : $parent . ':') . $key . ':' . $e['id']);
    }
}

function renderElements(array $leaf, array $actions)
{
    foreach ($leaf as $e) {
        $name = View::html($actions[(int) $e['id']]['name']);

        echo <<<NEWDOC
        <section class="portlet">
            <div class="portlet-body">{$name}</div>
            <div class="portlet-body"></div>
        </section>
        NEWDOC;

        renderElements($e['children'], $actions);
    }
}

echo $this->getData('nav')->render(); ?>

<?php
if (!empty($template->schema)) :
    $level = $template->schema;
?>
    <div class="row">
        <div class="col-md-6 col-xs-12">
            <section class="portlet sticky">
                <div class="portlet-head"><?= $this->getHtml('Workflow'); ?></div>
                <div class="portlet-body">
                    <div class="mermaid">
                    graph TD;
                    <?php renderLeaf($level, null); ?>
                    </div>
                </div>
            </section>
        </div>

        <div class="col-md-3 col-xs-12">
            <section class="portlet">
                <div class="portlet-head"><?= $this->getHtml('Active'); ?></div>
            </section>

            <?php renderElements($level, $actions); ?>
        </div>

        <div class="col-md-3 col-xs-12">
            <section class="portlet">
                <div class="portlet-head"><?= $this->getHtml('Available'); ?></div>
            </section>

            <?php foreach ($actions as $action) : ?>
                <section class="portlet">
                    <div class="portlet-body"><?= $this->printHtml($action['name']); ?></div>
                    <div class="portlet-body"><?= $this->printHtml($action['description'][$this->request->getLanguage()]); ?></div>
                </section>
            <?php endforeach; ?>
        </div>
    </div>
<?php endif; ?>