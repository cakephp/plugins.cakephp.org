<?php
declare(strict_types=1);

namespace App\Model\Filter;

use Cake\Database\Expression\QueryExpression;
use Search\Model\Filter\Like;
use function Cake\Database\Expression\like;

/**
 * Extend the established wildcard search without joining keyword memberships.
 */
class KeywordSearch extends Like
{
    /**
     * Match any text field or associated keyword, keeping pagination unique.
     */
    public function process(): bool
    {
        $this->setEscaper();
        $pattern = $this->wildcards($this->value());
        if ($pattern === false) {
            return false;
        }
        /** @var \App\Model\Table\PackagesTable $packages */
        $packages = $this->manager()->getRepository();
        $query = $this->getQuery();
        $membership = $packages->keywordMembership()->where(['SearchKeyword.name' => like($pattern)]);
        $query->where(static fn(QueryExpression $exp): QueryExpression => $exp->or([
            'Packages.package' => like($pattern),
            'Packages.description' => like($pattern),
            $exp->exists($membership),
        ]));

        return true;
    }
}
