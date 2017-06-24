<?php
return [
    'Users' => [
        // User model.
        'userModel' => 'Users.Users',
        // Enable users the ability to upload avatars
        'enableAvatarUploads' => true,
        // Enable the password-reset flow
        'enablePasswordReset' => true,
        // Require that a user's email be authenticated
        'requireEmailAuthentication' => true,
        // Make all users active immediately
        'setActiveOnCreation' => true,
        // Track last login timestamp in the database and session
        'trackLoginActivity' => true,
        // Track last activity timestamp in the database and session
        'trackLastActivity' => true,
        // Fields to use for authentication
        'fields' => [
            'username' => 'email',
            'password' => 'password',
        ],
        // A route to the controller action that handles logins
        'loginAction' => [
            'plugin' => 'Users',
            'prefix' => false,
            'controller' => 'Users',
            'action' => 'login'
        ],
        'loginRedirect' => null,
        'logoutRedirect' => null,
        // Social configuration
        'social' => [
        ],
    ],
];
