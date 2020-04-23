<?php

$params = require __DIR__ . '/params.php';
$db = require __DIR__ . '/db.php';

$config = [
    'id' => 'basic',
    'name' => 'Fezez',
    'basePath' => dirname(__DIR__),
    'bootstrap' => ['log'],
    'aliases' => [
        '@bower' => '@vendor/bower-asset',
        '@npm'   => '@vendor/npm-asset',
    ],
    'components' => [
        'request' => [
            // !!! insert a secret key in the following (if it is empty) - this is required by cookie validation
            'cookieValidationKey' => getenv('COOKIEVALIDATIONKEY'),
        ],
        'cache' => [
            'class' => 'yii\caching\FileCache',
        ],
        'user' => [
            'identityClass' => 'app\models\User',
            'enableAutoLogin' => true,
        ],
        'errorHandler' => [
            'errorAction' => 'site/error',
        ],
        'mailer' => [
            'class' => 'yii\swiftmailer\Mailer',
            'transport' => [
                'class' => 'Swift_SmtpTransport',
                'host' => getenv('SMTP_SERVER'),
                'username' => getenv('SMTP_USERNAME'),
                'password' => getenv('SMTP_PASSWORD'),
                'port' => getenv('SMTP_PORT'),
                'encryption' => getenv('SMTP_ENCRYPTION'),
            ],
            'viewPath' => '@app/mail',
        ],
        'log' => [
            'traceLevel' => YII_DEBUG ? 3 : 0,
            'targets' => [
                [
                    'class' => 'yii\log\FileTarget',
                    'levels' => ['error', 'warning'],
                ],
            ],
        ],
        'db' => $db,
		'urlManager' => [
			'class' => 'yii\web\UrlManager',
			// Disable index.php
			'showScriptName' => false,
			// Disable r= routes
            'enablePrettyUrl' => true,
            'baseUrl' => getenv('HOME_URL'),
			'rules' => array(
                    'home' => 'site/home',
                    'invite' => 'site/invite',
                    'signup' => 'site/signup',
                    'login' => 'site/login',
                    'requestpasswordreset' => 'site/request-password-reset',
                    'resetpassword' => 'site/reset-password',
                    'resendverificationemail' => 'site/resend-verification-email',
                    'verifyemail' => 'site/verify-email',
                    'marketplace' => 'site/marketplace',
                    'myrequests' => 'request/myrequests',
                    'myoffers' => 'offer/myoffers',
                    'offer/edit/<id:\d+>' => 'offer/edit',
                    'request/paid/<id:\d+>' => 'request/paid',
					/* '<controller:\w+>/<id:\d+>' => '<controller>/view',
					'<controller:\w+>/<action:\w+>/<id:\d+>' => '<controller>/<action>',
					'<controller:\w+>/<action:\w+>' => '<controller>/<action>', */
			),
        ],
        'i18n' => [
            'translations' => [
                '*' => [
                    'class' => 'yii\i18n\PhpMessageSource',
                    'basePath' => '@app/messages',
                    'sourceLanguage' => 'en-US',
                    'fileMap' => [
                    ],
                ],
            ],
        ],
    ],
    'params' => $params,
    'language' => getenv('SITE_LANG'),
];

if (YII_ENV_DEV) {
    // configuration adjustments for 'dev' environment
    $config['bootstrap'][] = 'debug';
    $config['modules']['debug'] = [
        'class' => 'yii\debug\Module',
        // uncomment the following to add your IP if you are not connecting from localhost.
        //'allowedIPs' => ['127.0.0.1', '::1'],
    ];

    $config['bootstrap'][] = 'gii';
    $config['modules']['gii'] = [
        'class' => 'yii\gii\Module',
        // uncomment the following to add your IP if you are not connecting from localhost.
        //'allowedIPs' => ['127.0.0.1', '::1'],
    ];
}

return $config;
