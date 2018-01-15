<?php

main();

function getKLADRArray($filename) { //поулчает массив из csv
	ini_set('auto_detect_line_endings', TRUE);
	return array_map('str_getcsv', file($filename));
}

function fixKLADRArray($filename) { //убирает строки, не имеющие смысла
	$newArray = [];
	$addreses = getKLADRArray($filename);

	for ($i = 1; $i < count($addreses); $i++) {
		$countNewArray = 0;
		for ($j = 5; $j < 8; $j++) {
			if ($addreses[$i][$j] != '') {
				$newArray[$i - 1][$countNewArray] = $addreses[$i][$j];
				$countNewArray++;
			}
		}
	}

	return $newArray;
}

function unique($array) {
	$newArray = [];
	foreach ($array as $row) {
		if (isset($row[0]))
			array_push($newArray, $row[0]);
		else
			array_push($newArray, $row[1]);
	}

	return array_unique($newArray);
}

function search($array, $search) {
	$newArray = [];
	foreach ($array as $row) {
		if ($row[0] == $search) {
			if (isset($row[2])){
				array_push($newArray, array($row[1], $row[2]));
			} elseif (isset($row[1]) && !is_int($row[0])) {
				array_push($newArray, $row[1]);
			}
		}
	}
	return $newArray;
}

function getVillageHouse($array, $village) { //для деревень без улиц
	$newArray = [];
	$house = [];
	foreach ($array as $row) {
		if (($row[0] == $village) && (!isset($row[2]))) {
			array_push($house, $row[1]);
		}
	}
	$newArray[$village] = $house;
	return $newArray;
}

function main() {
	$jsonKLADR = [];
	$getVillageName = isset($_GET['getVillageName'])? $_GET['getVillageName'] : '';
	$getStreetName = isset($_GET['getStreetName'])? $_GET['getStreetName'] : '';
	$addreses = fixKLADRArray('addreses.csv');
	$uniqueVillageName = unique($addreses);
	$lastVillageName = $uniqueVillageName[0];

	foreach ($uniqueVillageName as $village) {
		$streets = search($addreses, $village);
		$house = [];
		foreach ($streets as $street) {
			$house[$street[0]] = search($streets, $street[0]);
			$jsonKLADR[$village] = $house;
		}
	}

	foreach ($jsonKLADR as $village => $street) { //т.к. у некоторых деревень нет улиц, то пришлось дописать небольшой костыль
		$arrayMerge = [];
		foreach (array_keys($street) as $key) {
			if (strlen($key) <= 1|| (!isset($key[1]))) {
				unset($jsonKLADR[$village][$key]);
				$arrayMerge = getVillageHouse($addreses, $village);
			}
		}
		$jsonKLADR[$village] = array_merge($jsonKLADR[$village], $arrayMerge);
	}

	echo getList($getVillageName, $getStreetName, $jsonKLADR); //возвращает список нас. пунктов или улиц
	getJSON($getVillageName, $getStreetName, $jsonKLADR); //возвращает json
}

function getList($getVillageName, $getStreetName, $array) {
	$list = '<option>Все *</option>';
	if ($getVillageName == '') {
		foreach (array_keys($array) as $village)
			$list .= '<option>'.$village.'</option>';
		return $list;
	} elseif ($getStreetName == '') {
		foreach (array_keys($array[$getVillageName]) as $street)
			$list .= '<option>'.$street.'</option>';
		return $list;
	}
}

function getJSON($getVillageName, $getStreetName, $array) {
	if (($getVillageName == '') && ($getStreetName == '')) {
		$array = json_encode($array, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
	}

	if (($getVillageName != '') && ($getStreetName == '')) {
		$array = json_encode($array[$getVillageName], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
	}

	if (($getVillageName != '') && ($getStreetName != '')) {
		$array = json_encode($array[$getVillageName][$getStreetName], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
	}

	file_put_contents('list.json', $array);
}

?>