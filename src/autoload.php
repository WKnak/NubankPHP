<?php

/* 
 * NubankPHP
 *
 * Copyright 2021 William Knak
 * https://github.com/WKnak/NubankPHP
 *
 * Licensed under the Apache License, Version 2.0 (the "License")
 */


spl_autoload_register('AutoLoader');

function AutoLoader($className) {

    $file = str_replace('\\', DIRECTORY_SEPARATOR, $className) . '.php';

    $file = __DIR__ . "\\" . $file;

    require_once $file;
}
