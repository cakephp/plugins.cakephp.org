<?php
declare(strict_types=1);

namespace App\Island;

use Interact\Attribute\Action;
use Interact\Island;

/**
 * Changes the saved preference through CakePHP's response cookie middleware.
 */
class ThemeToggleIsland extends Island
{
    /**
     * Read the current cookie so desktop and mobile toggles share one preference.
     */
    #[Action]
    public function toggle(): void
    {
        $theme = $this->getRequest()->getCookie('theme') === 'cakephp-dark' ? 'cakephp' : 'cakephp-dark';
        $this->getRequest()->getSession()->write('Theme.pending', $theme);
        $this->dispatch('theme-changed', theme: $theme);
    }
}
