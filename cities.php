<?php
$config = require (dirname(__FILE__) . "/config/config.php");
require_once (dirname(__FILE__) . "/libs/functions.php");
require_once (dirname(__FILE__) . "/libs/City.php");

$stateID = '';
if (isset($_GET['stateID'])) {
    $stateID = $_GET['stateID'];
}

$obj = new City($config);
$cities = $obj->getcities($stateID);
// dump($countries);
json($cities);