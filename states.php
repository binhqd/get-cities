<?php
$config = require (dirname(__FILE__) . "/config/config.php");
require_once (dirname(__FILE__) . "/libs/functions.php");
require_once (dirname(__FILE__) . "/libs/State.php");

$countryID = '';
if (isset($_GET['countryID'])) {
    $countryID = $_GET['countryID'];
}

$obj = new State($config);
$states = $obj->getStates($countryID);
// dump($countries);
json($states);