<?php
declare(strict_types=1);

namespace App\View;

use Interact\View\Helper\PaginatorHelper;
use Interact\View\TemplateView;

/**
 * Shares application presentation templates with isolated island renders.
 */
class IslandTemplateView extends TemplateView
{
    /**
     * Preserve Interact's paginator and CSP-compatible form integration.
     */
    public function initialize(): void
    {
        parent::initialize();
        $this->addHelper('Form', ['templates' => 'form-templates', 'autoSetCustomValidity' => false]);
        $this->addHelper('Html', ['templates' => 'html-templates']);
        $this->addHelper('Paginator', ['className' => PaginatorHelper::class, 'templates' => 'paginator-templates']);
    }
}
