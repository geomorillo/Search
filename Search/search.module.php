<?php
use Helpers\Hooks;
//use Modules\Search\Controllers\Config as config;
Hooks::addHook('routes', 'Modules\Search\Controllers\Main@routes');
