<?php
// Handles saving or loading scripts

// HAAAACK!!!! Must save to DB!!!
function save($ks, $var) {
	global $scriptDir;
	$d = opendir($scriptDir);
	$all = array();
	while (($s = readdir($d))) {
		if ($s != "." && $s != "..") {
			// Scan each file and if same, return last url
			if ($ks == join("", file($scriptDir . "/$s"))) {
				return $s;
			}
			
			$all[] = $s;
		}
	}
	closedir($d);
	
	if (count($all) > 0) {
		sort($all, SORT_NUMERIC);
		$id = $all[count($all) - 1] + 1;
	} else {
		$id = 1;
	}
	
	$fd = fopen($scriptDir . "/$id", "w");
	fwrite($fd, $ks);
	fclose($fd);
	return $id;
}

function load($id) {
    global $scriptDir;
    return join("", file($scriptDir . "/$id"));
}
?>