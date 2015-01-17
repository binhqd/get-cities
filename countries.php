<?php
$config = require (dirname(__FILE__) . "/config/config.php");
require_once (dirname(__FILE__) . "/libs/functions.php");
require_once (dirname(__FILE__) . "/libs/Country.php");


$obj = new Country($config);
$countries = $obj->countries;
// dump($countries);
json($countries);