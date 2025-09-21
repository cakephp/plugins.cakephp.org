<?php
declare(strict_types=1);

/**
 * Custom templates for pagination elements.
 */
return [
    'button' => '<button class="bg-red-800 p-2 text-white rounded-sm"{{attrs}}>{{text}}</button>',
    'checkbox' => '<input class="justify-self-start" type="checkbox" name="{{name}}" value="{{value}}"{{attrs}}>',
    'checkboxWrapper' => '<div class="col-span-2 col-start-2 flex gap-2">{{label}}</div>',
    'error' => '<div class="col-start-2 col-end-4 text-red-700" id="{{id}}">{{content}}</div>',
    'input' => '<input class="col-span-2 border border-slate-500 rounded-sm max-w-md min-w-4 p-1" type="{{type}}" name="{{name}}"{{attrs}}>',
    'inputContainer' => '<div class="group py-3 grid grid-cols-3 gap-2">{{content}}</div>',
    'inputContainerError' => '<div class="group py-3 grid grid-cols-3 gap-2 text-red-700">{{content}}{{error}}</div>',
    'label' => "<label class=\"group-has-[input:required]:after:content-['_*'] group-has-[input:required]:after:text-red-700\" {{attrs}}>{{text}}</label>",
    // Not actually a nested label.
    'nestingLabel' => "{{hidden}}<label class=\"group-has-[input:required]:after:content-['_*'] group-has-[input:required]:after:text-red-700\" {{attrs}}>{{text}}</label>{{input}}",
    'textarea' => '<textarea class="border border-slate-500 col-span-2 rounded-sm max-w-md min-w-4 p-1" name="{{name}}"{{attrs}}>{{value}}</textarea>',
    'select' => '<select class="border border-slate-500 col-span-2 rounded-sm max-w-md min-w-4 p-1" name="{{name}}"{{attrs}}>{{content}}</select>',
    'selectMultiple' => '<select class="border border-slate-500 col-span-2 rounded-sm max-w-md min-w-50 p-1" name="{{name}}[]" multiple="multiple"{{attrs}}>{{content}}</select>',
    'radioWrapper' => '<div class="col-span-2 col-start-2 flex gap-2">{{label}}</div>',
];
