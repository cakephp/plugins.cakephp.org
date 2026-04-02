<?php
declare(strict_types=1);

/**
 * Custom templates for pagination elements.
 */
return [
    'nextActive' => '<li><a class="inline-flex min-h-10 items-center rounded-full border border-slate-200 bg-slate-50 px-4 text-sm font-medium text-slate-700 transition hover:border-cake-red hover:bg-white hover:text-cake-red" rel="next" href="{{url}}">{{text}}</a></li>',
    'nextDisabled' => '<li><span class="inline-flex min-h-10 items-center rounded-full border border-slate-200 bg-slate-50 px-4 text-sm font-medium text-slate-400">{{text}}</span></li>',
    'prevActive' => '<li><a class="inline-flex min-h-10 items-center rounded-full border border-slate-200 bg-slate-50 px-4 text-sm font-medium text-slate-700 transition hover:border-cake-red hover:bg-white hover:text-cake-red" rel="prev" href="{{url}}">{{text}}</a></li>',
    'prevDisabled' => '<li><span class="inline-flex min-h-10 items-center rounded-full border border-slate-200 bg-slate-50 px-4 text-sm font-medium text-slate-400">{{text}}</span></li>',
    'counterRange' => '{{start}} - {{end}} of {{count}}',
    'counterPages' => '{{page}} of {{pages}}',
    'first' => '<li><a class="inline-flex min-h-10 items-center rounded-full border border-slate-200 bg-slate-50 px-4 text-sm font-medium text-slate-700 transition hover:border-cake-red hover:bg-white hover:text-cake-red" href="{{url}}">{{text}}</a></li>',
    'last' => '<li><a class="inline-flex min-h-10 items-center rounded-full border border-slate-200 bg-slate-50 px-4 text-sm font-medium text-slate-700 transition hover:border-cake-red hover:bg-white hover:text-cake-red" href="{{url}}">{{text}}</a></li>',
    'number' => '<li><a class="inline-flex size-10 items-center justify-center rounded-full border border-slate-200 bg-white text-sm font-medium text-slate-700 transition hover:border-cake-red hover:text-cake-red" href="{{url}}">{{text}}</a></li>',
    'current' => '<li><span class="inline-flex size-10 items-center justify-center rounded-full border border-cake-red bg-cake-red text-sm font-semibold text-white">{{text}}</span></li>',
    'ellipsis' => '<li><span class="inline-flex size-10 items-center justify-center text-sm text-slate-400">&hellip;</span></li>',
    'sort' => '<a class="font-medium text-slate-600 transition hover:text-cake-red" href="{{url}}">{{text}}</a>',
    'sortAsc' => "<a class=\"whitespace-nowrap font-medium text-cake-red after:text-cake-red after:content-['_↑']\" href=\"{{url}}\">{{text}}</a>",
    'sortDesc' => "<a class=\"whitespace-nowrap font-medium text-cake-red after:text-cake-red after:content-['_↓']\" href=\"{{url}}\">{{text}}</a>",
    'sortAscLocked' => '<a class="asc locked" href="{{url}}">{{text}}</a>',
    'sortDescLocked' => '<a class="desc locked" href="{{url}}">{{text}}</a>',
];
