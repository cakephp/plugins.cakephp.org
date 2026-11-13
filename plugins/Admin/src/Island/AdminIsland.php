<?php
declare(strict_types=1);

namespace Admin\Island;

use Interact\Authorization\AuthorizationTrait;
use Interact\Island;
use Interact\IslandContext;

/**
 * Snapshots never substitute for current account access.
 */
abstract class AdminIsland extends Island
{
    use AuthorizationTrait;

    /**
     * Authorize the current request identity on every mount and update.
     */
    public function authorize(IslandContext $context): void
    {
        $this->Authorization->authorize($context->request->withParam('plugin', 'Admin'), 'access');
    }
}
