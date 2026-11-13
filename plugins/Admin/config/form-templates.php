<?php
declare(strict_types=1);

/**
 * DaisyUI form markup shared by the admin islands.
 */
return [
    'button' => '<button{{attrs}}>{{text}}</button>',
    'checkbox' => '<input type="checkbox" name="{{name}}" value="{{value}}"{{attrs}}>',
    'checkboxWrapper' => '<div>{{label}}</div>',
    'error' => '<div class="text-sm text-error" id="{{id}}">{{content}}</div>',
    'input' => '<input type="{{type}}" name="{{name}}"{{attrs}}>',
    'inputContainer' => '<div class="fieldset gap-2">{{content}}</div>',
    'inputContainerError' => '<div class="fieldset gap-2">{{content}}{{error}}</div>',
    'label' => '<label class="label"{{attrs}}>{{text}}</label>',
    'nestingLabel' => '{{hidden}}<label{{attrs}}>{{input}}{{text}}</label>',
    'select' => '<select name="{{name}}"{{attrs}}>{{content}}</select>',
    'textarea' => '<textarea name="{{name}}"{{attrs}}>{{value}}</textarea>',
];
