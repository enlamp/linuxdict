<?php
function postPlugin($a) {
	global $postPlugins;

	// echo("<p>New plugins: { " . $a . ", " . $a["id"] . " }");
	// print_r($postPlugins);
	
	for ($i = 0; $i < count($postPlugins); $i++) {
		if ($postPlugins[$i]["id"] == $a["id"]) {
			
			// echo "found: " . $a["id"] . ".";
	
			if ($a["name"]) {
				$postPlugins[$i]["name"] = $a["name"];
			}
			
			if ($a["code"]) {
				$postPlugins[$i]["code"] = $a["code"];
			}

			if ($a["handler"]) {
				$postPlugins[$i]["handler"] = $a["handler"];
			}

			if ($a["htmlHandler"]) {
				$postPlugins[$i]["htmlHandler"] = $a["htmlHandler"];
			}
			
			return;
		}
	}
	
	$postPlugins[] = $a;
}

postPlugin(array("id" => "svc-off", "name" => "Disable non-server services" ));
postPlugin(array("id" => "ipv6-off", "name" => "Disable IPv6" ));
postPlugin(array("id" => "pcspkr-off", "name" => "Disable PC Speaker" ));
postPlugin(array("id" => "autofsck", "name" => "Make fsck at boot automatic (no prompt)" ));
// postPlugin(array("id" => "noatime", "name" => "Add <code>noatime,nodiratime</code> to every hdd in /etc/fstab " ));
postPlugin(array("id" => "ssh-key", "name" => "Add ssh public keys to <code>authorized_keys</code> (see below)" ));
postPlugin(array("id" => "ssh-enable-root", "name" => "Enable remote ssh root login" ));
postPlugin(array("id" => "ssh-enable-root-nopw", "name" => "Enable remote ssh root login (without password, pubkey only)" ));
postPlugin(array("id" => "yum-update", "name" => "Run <code>yum -y update</code> after install (looong)" ));


$d = opendir($pluginDir);
if ($d) {
	while (($s = readdir($d))) {
		if ($s != "." && $s != "..") {
			if (strstr($s, ".php") == ".php") {
				include($pluginDir . "/" . $s);
			} else {
				if (strstr($s, ".sh") == ".sh") {
					// Strip .sh
					$s = substr($s, 0, count($s) - 4);
				}
				
				postPlugin(array("id" => $s));
			}
		}
	}
}
?>
