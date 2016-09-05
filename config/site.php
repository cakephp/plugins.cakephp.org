<?php

return [
    'Site' => [
        'contact' => [
            'email' => 'contact@cakedc.com'
        ],
        'menu' => [
            'items' => [
                'community' => [
                    'team' => [
                        'url' => [
                            'plugin' => false,
                            'controller' => 'Pages',
                            'action' => 'display',
                            'team',
                            'prefix' => false,
                        ],
                        'title' => __('Team'),
                    ],
                    'issues' => [
                        'url' => 'https://github.com/cakephp/cakephp/issues',
                        'options' => ['target' => '_blank'],
                        'title' => __('Issues (Github)'),
                    ],
                    'youtube' => [
                        'url' => 'https://www.youtube.com/user/CakePHP',
                        'options' => ['target' => '_blank'],
                        'title' => __('Youtube Channel'),
                    ],
                    'getInvolved' => [
                        'url' => [
                            'plugin' => false,
                            'controller' => 'Pages',
                            'action' => 'display',
                            'get-involved',
                            'prefix' => false,
                        ],
                        'title' => __('Get Involved'),
                    ],
                    'blog' => [
                        'url' => 'http://bakery.cakephp.org/',
                        'title' => __('Bakery'),
                    ],
                    'awesomeList' => [
                        'url' => 'https://github.com/FriendsOfCake/awesome-cakephp',
                        'title' => __('Featured Resources'),
                    ],
                    'newsletter' => [
                        'url' => [
                            'plugin' => false,
                            'controller' => 'Pages',
                            'action' => 'display',
                            'newsletter',
                            'prefix' => false,
                        ],
                        'title' => __('Newsletter'),
                    ],
                    'certification' => [
                        'url' => 'http://certification.cakephp.org/',
                        'title' => __('Certification'),
                    ],
                    'myCakephp' => [
                        'url' => 'http://my.cakephp.org/login',
                        'title' => __('My CakePHP'),
                    ],
                    'cakefest' => [
                        'url' => 'http://cakefest.org',
                        'options' => ['target' => '_blank'],
                        'title' => __('CakeFest'),
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
                        'title' => __('Paid Support'),
                    ],
                    'googleplus' => [
                        'url' => 'https://plus.google.com/communities/108328920558088369819',
                        'options' => ['target' => '_blank'],
                        'title' => __('Google+'),
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
                        'url' => 'http://book.cakephp.org/',
                        'title' => __('Book'),
                    ],
                    'api' => [
                        'url' => 'http://api.cakephp.org/',
                        'title' => __('API'),
                    ],
                    'videos' => [
                        'url' => [
                            'plugin' => false,
                            'controller' => 'documentation',
                            'action' => 'videos',
                            'prefix' => false,
                        ],
                        'title' => __('Videos'),
                    ],
                    'privacy' => [
                        'url' => [
                            'plugin' => false,
                            'controller' => 'Pages',
                            'action' => 'display',
                            'privacy',
                            'prefix' => false,
                        ],
                        'title' => __('Privacy Policy'),
                    ],
                    'logos' => [
                        'url' => [
                            'plugin' => false,
                            'controller' => 'Pages',
                            'action' => 'display',
                            'trademark',
                            'prefix' => false,
                        ],
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
