<?php
namespace App\View\Helper;

use BootstrapUI\View\Helper\OptionsAwareTrait;
use Cake\View\Helper;
use Cake\View\StringTemplateTrait;

/**
 * ButtonGroup Helper
 */
class ButtonGroupHelper extends AppHelper
{
    use OptionsAwareTrait;
    use StringTemplateTrait;

    /**
     * Default config for the helper.
     *
     * @var array
     */
    protected $_defaultConfig = [
        'templates' => [
            'wrapper' => '<div role="group"{{attrs}}>{{content}}</div>',
        ],
    ];

    public function render($content, array $attributes = [])
    {
        $attributes = $this->injectClasses('btn-group', (array)$attributes);
        $templater = $this->templater();

        return $this->formatTemplate('wrapper', [
            'content' => $content,
            'attrs' => $templater->formatAttributes($attributes, ['templateVars']),
            'templateVars' => isset($attributes['templateVars']) ? $attributes['templateVars'] : [],
        ]);
    }
}
