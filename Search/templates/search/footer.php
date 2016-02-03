<?php

/**
 * Sample layout
 */
use Helpers\Assets;

use Helpers\Hooks;

//initialise hooks
$hooks = Hooks::get();
?>

</div>

<!-- JS -->
<?php
$theroute = DIR.'app/Modules/Search/templates/search/';
Assets::js(array(
    $theroute . 'js/jquery-1.11.1.min.js',
    $theroute. 'js/ajaxlivesearch.js'
));

//hook for plugging in javascript
$hooks->run('js');

//hook for plugging in code into the footer
$hooks->run('footer');
?>

</body>
</html>
