<?php
function dump($obj, $exit = true) {
	echo "<pre>";
	var_dump($obj);
	if ($exit) exit;
}

function json($obj) {
    header("Content-type: application/json");
    
    echo json_encode($obj);
    exit;
}