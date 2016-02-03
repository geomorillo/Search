<?php

/**
 * Sample layout
 */
use Helpers\Assets;
use Helpers\Hooks;

//initialise hooks
$hooks = Hooks::get();
?>
<!DOCTYPE html>
<html lang="<?php echo LANGUAGE_CODE; ?>">
    <head>

        <!-- Site meta -->
        <meta charset="utf-8">
        <?php
        //hook for plugging in meta tags
        $hooks->run('meta');
        ?>
        <title><?php echo $data['title'] . ' - ' . SITETITLE; //SITETITLE defined in app/Core/Config.php  ?></title>

        <!-- CSS -->
        <?php
        $theroute = DIR.'app/Modules/Search/templates/search/';
        Assets::css(array(
            $theroute . 'css/ajaxlivesearch.min.css',
            $theroute . 'css/fontello.css',
            $theroute . 'css/animation.css',
            $theroute . 'font/fontello.woff'
        ));

        //hook for plugging in css
        $hooks->run('css');
        ?>

    </head>
    <body>
        <?php
//hook for running code after body tag
        $hooks->run('afterBody');
        ?>

        <div class="container">
