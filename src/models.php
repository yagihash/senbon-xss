<?php
$models = glob(__DIR__ . '/models/*.php');

foreach($models as $path) {
    require_once($path);
}