<?php
/**
 * CakePHP(tm) : Rapid Development Framework (https://cakephp.org)
 * Copyright (c) Cake Software Foundation, Inc. (https://cakefoundation.org)
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright     Copyright (c) Cake Software Foundation, Inc. (https://cakefoundation.org)
 * @link          https://cakephp.org CakePHP(tm) Project
 * @since         0.10.0
 * @license       https://www.opensource.org/licenses/mit-license.php MIT License
 *
 * @var \App\View\AppView $this
 */
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <?= $this->Html->charset() ?>
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title><?= $this->fetch('title') ?></title>
    <meta charset="utf-8" >

    <link href='https://fonts.googleapis.com/css?family=Source+Sans+Pro:100,300,400,600,700,900,400italic%7CMontserrat:400,700' rel='stylesheet'>
    <?= $this->AssetCompress->css('public') ?>
    <?= $this->AssetCompress->css('fonts', ['raw' => true, 'pathPrefix' => null]) ?>
    <script type="text/javascript" src="https://www.gstatic.com/charts/loader.js"></script>
    <script type="text/javascript" src="https://www.google.com/jsapi"></script>
    <?= $this->fetch('socialMeta') ?>
    <link rel="apple-touch-icon" sizes="180x180" href="/apple-touch-icon.png">
    <link rel="icon" type="image/png" sizes="32x32" href="/favicons/favicon-32x32.png">
    <link rel="icon" type="image/png" sizes="16x16" href="/favicons/favicon-16x16.png">
    <link rel="manifest" href="/favicons/manifest.json">
    <link rel="mask-icon" href="/favicons/safari-pinned-tab.svg" color="#5bbad5">
    <meta name="msapplication-config" content="/favicons/browserconfig.xml" />
    <meta name="apple-mobile-web-app-title" content="CakePHP">
    <meta name="application-name" content="CakePHP">
    <meta name="msapplication-TileColor" content="#da532c">
    <meta name="theme-color" content="#ffffff">

    <?= $this->fetch('meta') ?>
    <?= $this->fetch('css') ?>
    <link rel="icon" type="image/png" href="/favicon.png">
</head>
<body class="<?php echo $_bodyClass; ?>" id="<?php echo $_bodyId; ?>">
<?= $this->element('Layout/default/navbar')?>
<?= $this->Flash->render() ?>
<?= $this->element('site/top_bar') ?>
<section class="clearfix">
    <?= $this->fetch('content') ?>
</section>
<?= $this->element('Layout/default/footer')?>

<?= $this->AssetCompress->script('public'); ?>
<?= $this->AssetCompress->script('not_compiled', ['raw' => true, 'pathPrefix' => null]); ?>
<?= $this->fetch('script') ?>

<?php $address = env('SERVER_ADDR'); if ($address && !in_array($address, ['127.0.0.1', 'localhost'], true)): ?>
    <script type="text/javascript">
        var gaJsHost = (("https:" == document.location.protocol) ? "https://ssl." : "https://www.");
        document.write(unescape("%3Cscript src='" + gaJsHost + "google-analytics.com/ga.js' type='text/javascript'%3E%3C/script%3E"));
    </script>
    <script type="text/javascript">
        if (typeof _gat === Object) {
            var pageTracker = _gat._getTracker("UA-743287-4");
            pageTracker._initData();
            pageTracker._trackPageview();
        }
    </script>
<?php endif; ?>
</body>
</html>
