<?php
if (isset($_GET['q'])) {
	require_once('ZoneGeography.php');
	$zone = new ZoneGeography();
	$data = $zone->query($_GET['q'], true);
	echo json_encode($data);
}