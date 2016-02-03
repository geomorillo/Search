<div style="clear: both">
    <input type="text" class='mySearch' id="ls_query">
</div>
<?php

use Helpers\Hooks;

//initialise hooks
$hooks = Hooks::get();

$hooks::addHook('js', 'Modules\Search\Controllers\Main@js');

