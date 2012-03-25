<?php
function size($s) {
	$s = strtolower($s);
	if (strstr($s, "g") == "g") {
		$s = intval(substr($s, 0, count($s) - 2)) * 1024 * 1024 * 1024;
	} else
	if (strstr($s, "m") == "m") {
		$s = intval(substr($s, 0, count($s) - 2)) * 1024 * 1024;
	} else
	if (strstr($s, "k") == "k") {
		$s = intval(substr($s, 0, count($s) - 2)) * 1024 * 1024;
	} else {
		$s = intval($s);
	}	
	
	// Must be in megabytes
	return round($s / (1024 * 1024));
}

// Main workhorse - generated kickstart script from var
function gen($var) {
	global $postPlugins, $pluginDir;
	
	extract($var);
	if ($hostnameauto > 0) {
		$hostname = "#KSAUTO#";
	}
	
	
	ob_start();
?>
install
<? if ($source == "url") { ?>
url --url <?echo $urlmanual ? $urlmanual : $url ?>
<? } else { ?>
<? echo $source ?>
<? } ?>

lang <?echo $lang ?><? echo $charset ? ("." . $charset) : "" ?>

keyboard <?echo $keyboard ?>

<? if ($network == "dhcp") { ?>
network --device <?echo $networkdev ?> --bootproto dhcp --hostname <?echo $hostname ?> <? echo in_array("ipv6-off", $plugins) ? "--noipv6" : "" ?>
<? } else { ?>
network --device <?echo $networkdev ?> --bootproto static --hostname <?echo $hostname ?> --ip <? echo $ip ?> --netmask <? echo $netmask ?> --gateway <? echo $gw ?> <? 
$dnss = join(" --nameserver ", explode(",", join(",", explode(" ", $dns))));
if ($dnss != "") {
	echo "--nameserver ";
}
echo $dnss;
?>
<? } ?>

firewall --<? echo $firewall ?> <? echo $networkdev ?><?
$fwp = explode(",", join(",", explode(" ", $firewallports)));
$wellknown = array ("ssh", "telnet", "smtp", "http", "ftp");
for ($i = 0; $i < count($fwp); $i++) {
	if (in_array($fwp[$i], $wellknown)) {
		echo " --" . $fwp[$i];
	} else
	if ($fwp[$i] != "") {
		echo " --port=" . $fwp[$i] . ":tcp";
	}
}
?>

rootpw <? echo $rootpwcrypted ? "--iscrypted" : "" ?> <? echo $rootpw ?>

selinux --<? echo $selinux ?>

timezone <? echo $hwclockutc ? "--utc " : "" ?><? echo $timezone ?>

<?
$hdddev = str_replace("/dev/", "", $hdd);
?>

bootloader --location=mbr --driveorder=<? echo $hdddev ?>

firstboot --disable
reboot

# Partitioning
clearpart --all --initlabel --drives=<?echo $hdddev ?>

<?
$have = array();
$types = array();
$hddlayout = strtolower($hddlayout);

for ($i = 0; $i < strlen($hddlayout); $i++) {
	$part = $hddlayout[$i];
	$type = $hddlayout[$i + 1];
	$i ++;
	$have[$part] = true;
	$types[$part] = $type;
}

// R - /, V - /var, S - swap, B - /boot, H - /home, T - /tmp
// P - primary, S - secondary, R - raid, V - LVM volgroup
// --grow added to home or root (if no home)

/* WARNING!!! NOT REALLY FLEXIBLE HERE! */
?>
part /boot --fstype ext3 --size=<?echo size($bootsize) ?> --ondisk=<?echo $hdddev ?>

part pv.2 --size=0 --grow --ondisk=<?echo $hdddev ?>

volgroup sys --pesize=32768 pv.2
<? if ($have["h"]) { ?>
logvol / --fstype ext3 --name=root --vgname=sys --size=<?echo size($rootsize) ?>

logvol /home --fstype ext3 --name=home --vgname=sys --size=1 --grow
<? } else { ?>
logvol / --fstype ext3 --name=root --vgname=sys --size=1 --grow
<? } ?>
<? if ($have["v"]) { ?>
logvol /var --fstype ext3 --name=home --vgname=sys --size=<?echo size($varsize) ?>

<? } ?>
logvol swap --fstype swap --name=swap --vgname=sys --size=<?echo size($swapsize) ?>


%packages
<?
for ($i = 0; $i < count($packages); $i++) {
	echo $packages[$i];
	echo "\n";
}
?>

%post
<?
/* PLUGINS!!! */
for ($i = 0; $i < count($postPlugins); $i++) {
	$p = $postPlugins[$i];
	$pid = $p["id"];
	
	if (in_array($pid, $plugins)) {
		ob_start();	
		if (file_exists($pluginDir . "/" . $pid . ".sh")) {
			echo join("", file($pluginDir . "/" . $pid . ".sh"));
		} else
		if (file_exists($pluginDir . "/" . $pid)) {
			echo join("", file($pluginDir . "/" . $pid));
		}
		
		if ($p["code"]) {
			echo $p["code"];
		}
		
		if ($p["handler"]) {
			$f = $p["handler"];
			echo $f($var);
		}
		
		$text = ob_get_contents();
		ob_end_clean();
		$text = explode("\n", $text);
		// Remove shell execute first line
		if (count($text) > 0 && strstr($text[0], "#!") == $text[0]) {
			array_splice($text, 0, 1);
		}
		$text = join("\n", $text);
		
		if ($text != "") {
			echo "# [ $pid ]\n";	
			echo trim($text);
			echo "\n\n";
		}
	}
}

if ($post != "") {
	echo $post;
}
?>

<?	
	$s = ob_get_contents();
	ob_end_clean();
	return $s;
}
?>
