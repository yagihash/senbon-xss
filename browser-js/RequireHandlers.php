<?php
/**
 * Created by PhpStorm.
 * User: yagihash
 * Date: 2017/10/11
 * Time: 22:39
 */

$files = glob(__DIR__ . '/BrowserFor*.php');
foreach($files as $path)
    require_once $path;