<?php
declare(strict_types=1);

/**
 * Shared pagination and sorting links, including Interact's enhanced navigation.
 */
return [
    'nextActive' => '<a class="page-link btn btn-outline" rel="next nofollow" href="{{url}}">{{text}}</a>',
    'nextDisabled' => '<span class="page-link btn btn-outline" aria-disabled="true">{{text}}</span>',
    'prevActive' => '<a class="page-link btn btn-outline" rel="prev nofollow" href="{{url}}">{{text}}</a>',
    'prevDisabled' => '<span class="page-link btn btn-outline" aria-disabled="true">{{text}}</span>',
    'number' => '<a class="page-link btn btn-outline numbered-page" rel="nofollow" href="{{url}}">{{text}}</a>',
    'current' => '<span class="page-link btn btn-outline numbered-page" aria-current="page">{{text}}</span>',
    'ellipsis' => '<span class="numbered-page" aria-hidden="true">&hellip;</span>',
    'first' => '<a class="page-link btn btn-outline" rel="nofollow" href="{{url}}">{{text}}</a>',
    'last' => '<a class="page-link btn btn-outline" rel="nofollow" href="{{url}}">{{text}}</a>',
    'sort' => '<a class="sort-option" rel="nofollow" href="{{url}}">{{text}}</a>',
    'sortAsc' => '<a class="sort-option sort-option-active" rel="nofollow" href="{{url}}">{{text}} ↑</a>',
    'sortDesc' => '<a class="sort-option sort-option-active" rel="nofollow" href="{{url}}">{{text}} ↓</a>',
    'sortAscLocked' => '<span class="sort-option sort-option-active">{{text}} ↑</span>',
    'sortDescLocked' => '<span class="sort-option sort-option-active">{{text}} ↓</span>',
];
