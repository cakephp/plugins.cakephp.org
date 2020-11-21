<?php

return [
    'Site' => [
        'contact' => [
            'email' => 'contact@cakedc.com'
        ],
        'menu' => [
            'items' => [
                'community' => [
                    'getInvolved' => [
                        'url' => 'https://cakephp.org/pages/get-involved',
                        'options' => ['target' => '_blank'],
                        'title' => __('Get Involved'),
                    ],
                    'issues' => [
                        'url' => 'https://github.com/cakephp/cakephp/issues',
                        'options' => ['target' => '_blank'],
                        'title' => __('Issues (Github)'),
                    ],
                    'blog' => [
                        'url' => 'http://bakery.cakephp.org/',
                        'options' => ['target' => '_blank'],
                        'title' => __('Bakery'),
                    ],
                    'awesomeList' => [
                        'url' => 'https://github.com/FriendsOfCake/awesome-cakephp',
                        'options' => ['target' => '_blank'],
                        'title' => __('Featured Resources'),
                    ],
                    'training' => [
                        'url' => 'http://training.cakephp.org/',
                        'options' => ['target' => '_blank'],
                        'title' => __('Training'),
                    ],
                    'meetups' => [
                        'url' => 'https://cakephp.org/pages/meetups',
                        'options' => ['target' => '_blank'],
                        'title' => __('Meetups'),
                    ],
                    'myCakephp' => [
                        'url' => 'http://my.cakephp.org/login',
                        'options' => ['target' => '_blank'],
                        'title' => __('My CakePHP'),
                    ],
                    'cakefest' => [
                        'url' => 'http://cakefest.org',
                        'options' => ['target' => '_blank'],
                        'title' => __('CakeFest'),
                    ],
                    'newsletter' => [
                        'url' => 'https://cakephp.org/pages/newsletter',
                        'options' => ['target' => '_blank'],
                        'title' => __('Newsletter'),
                    ],
                    'linkedin' => [
                        'url' => 'https://www.linkedin.com/groups/4623165',
                        'options' => ['target' => '_blank'],
                        'title' => __('Linkedin'),
                    ],
                    'youtube' => [
                        'url' => 'https://www.youtube.com/user/CakePHP',
                        'options' => ['target' => '_blank'],
                        'title' => __('Youtube'),
                    ],
                    'facebook' => [
                        'url' => 'https://www.facebook.com/CakePHP/',
                        'options' => ['target' => '_blank'],
                        'title' => 'Facebook',
                    ],
                    'twitter' => [
                        'url' => 'https://twitter.com/cakephp',
                        'options' => ['target' => '_blank'],
                        'title' => 'Twitter',
                    ],
                ],
                'help' => [
                    'discourse' => [
                        'url' => 'http://discourse.cakephp.org',
                        'title' => __('Forum'),
                        'options' => ['target' => '_blank'],
                    ],
                    'stackOverflow' => [
                        'url' => 'http://stackoverflow.com/tags/cakephp',
                        'options' => ['target' => '_blank'],
                        'title' => __('Stack Overflow')
                    ],
                    'irc' => [
                        'url' => 'https://kiwiirc.com/client/irc.freenode.net#cakephp',
                        'options' => ['target' => '_blank'],
                        'title' => 'IRC',
                    ],
                    'slack' => [
                        'url' => 'http://cakesf.herokuapp.com/',
                        'options' => ['target' => '_blank'],
                        'title' => 'Slack',
                    ],
                    'commercial' => [
                        'url' => 'http://www.cakedc.com/',
                        'options' => ['target' => '_blank'],
                        'title' => __('Paid Support'),
                    ],
                ],
                'jobs' => [
                    'cakeJobs' => [
                        'url' => 'http://cakephpjobs.com/',
                        'options' => ['target' => '_blank'],
                        'title' => __('Cake Jobs'),
                    ],
                    'linkedin' => [
                        'url' => 'https://www.linkedin.com/groups/4623165',
                        'options' => ['target' => '_blank'],
                        'title' => 'LinkedIn',
                    ],
                    'freelancer' => [
                        'url' => 'https://www.freelancer.com/find/CakePHP',
                        'options' => ['target' => '_blank'],
                        'title' => 'Freelancer',
                    ],
                    'odesk' => [
                        'url' => 'https://www.upwork.com/o/jobs/browse/skill/cakephp/',
                        'options' => ['target' => '_blank'],
                        'title' => 'oDesk',
                    ],
                    'cakexperts' => [
                        'url' => 'http://cakexperts.com/',
                        'options' => ['target' => '_blank'],
                        'title' => 'CakeXperts',
                    ],
                ],
                'documentation' => [
                    'book' => [
                        'url' => 'https://book.cakephp.org/',
                        'options' => ['target' => '_blank'],
                        'title' => __('Book'),
                    ],
                    'api' => [
                        'url' => 'https://api.cakephp.org/',
                        'options' => ['target' => '_blank'],
                        'title' => __('API'),
                    ],
                    'videos' => [
                        'url' => 'https://cakephp.org/documentation/videos',
                        'options' => ['target' => '_blank'],
                        'title' => __('Videos'),
                    ],
                    'security' => [
                        'url' => 'https://github.com/cakephp/cakephp#security',
                        'options' => ['target' => '_blank'],
                        'title' => __('Reporting Security Issues'),
                    ],
                    'privacy' => [
                        'url' => 'https://cakephp.org/pages/privacy',
                        'options' => ['target' => '_blank'],
                        'title' => __('Privacy Policy'),
                    ],
                    'logos' => [
                        'url' => 'https://cakephp.org/pages/trademark',
                        'options' => ['target' => '_blank'],
                        'title' => __('Logos & Trademarks'),
                    ],
                ],
                'serviceProvider' => [
                    'cakedc' => [
                        'url' => 'http://www.cakedc.com/',
                        'title' => 'CakeDC',
                        'options' => ['class' => 'hide'],
                    ],
                    'phpstorm' => [
                        'url' => 'https://www.jetbrains.com/phpstorm/',
                        'options' => ['target' => '_blank', 'class' => 'hide'],
                        'title' => 'PhpStorm',
                    ],
                    'rackspace' => [
                        'url' => 'https://www.rackspace.com/',
                        'options' => ['target' => '_blank', 'class' => 'hide'],
                        'title' => 'Rackspace',
                    ]
                ],
                'calendar' => [
                    'meetups' => [
                        'url' => '#',
                        'title' => __('Meetups'),
                    ],
                    'events' => [
                        'url' => '#',
                        'title' => __('Events'),
                    ],
                ]
            ]
        ],
        'cakefest' => [
            'start_date' => '2016-05-26',
            'end_date' => '2016-05-29',
            'location' => __('Amsterdam'),
            'title' => 'Cakefest 2016',
        ],
    ],
];
