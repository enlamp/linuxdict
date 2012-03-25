<?php
// Disable E_NOTICE in the first place
error_reporting(error_reporting() & ~E_NOTICE);

// Kickstart base URL
// Make sure .htaccess is installed
$ksurl = "http://localhost/learnphp/ks/scripts/";

// Where plugins is located, including default
$pluginDir = "post-plugins";

// Where scripts saved
// TODO: Save to database!
$scriptDir = "scripts";

$postPlugins = array();
include ("post-plugins.php");
include ("ks-gen.php");
include ("ks-save.php");

$action = $_REQUEST["action"];
if ($action == "") {
	$action = isset($_REQUEST["edit"]) ? "edit" : "";
}

//$id = intval($_REQUEST["id"]);
$id = $_REQUEST['hostname'];
	
if ($id > 0 && $action == "") {
	// Show kickstart script
	header("Content-Type: text/plain");
	$ks = load($id);
	$ks = str_replace("#KSAUTO#", "ks" . time(), $ks);
	header("Content-Disposition: inline; filename=ks.cfg");
	echo $ks;
	return;
} else
if ($action == "") {
	$action = "form";	
} else
if ($id > 0 && $action == "edit") {
	// Load script and show form
	$action = "form";
} else
if ($action == "save") {
	// Save as new script
	$var = array();
	$var["packages"] = array();
	$var["plugins"] = array();
	foreach ($_REQUEST as $key => $value) {
		if ($key == "packages") {
			$value = explode(" ", join(" ", explode("\n", $value)));
			array_splice($var["packages"], count($var["packages"]), 0, $value);
		} else
		if (strstr($key, "packages-")) {
			$value = substr($key, 9);
			$var["packages"][] = "@" . $value;
		} else
		if (strstr($key, "plugin-")) {
			$var["plugins"][] = $value;
		} else {
			$var[$key] = $value;
		}
	}
	
	foreach ($var as $key => $value) {
		if (is_array($value)) {
			$value = "[ " . join(", ", $value) . " ]";
		}
		// echo "$key = $value<br>";
	}	
	
	// echo "<hr>";
	$s = gen($var);
	$id = save($s, $var);
	// echo str_replace("\n", "<br>\n", $s);
	// echo "<hr>";
} else {
	$action = "form";
}

function loadSelectOptions($file, $defindex = -1, $defvalue = NULL) {
	$values = file($file);
	for ($i = 0; $i < count($values); $i++) {
	    $v = trim($values[$i]);
		if ($v != "") {
  			echo "<option";
			if ($i == $defindex || $v == $defvalue) {
				echo " selected=\"selected\"";
		 	}
			echo ">" . $v . "</option>";
		}		  
	}
}

if ($action == "form") {
?>
<html>
	<head>
		<title>Kickstart generator</title>
		<script src="prototype.js"></script>
		<script src="javacrypt.js"></script>
		<script language="Javascript" src="ks.js"></script>
		<link rel="stylesheet" type="text/css" href="ks.css" />
	</head>
<body>

<h1>Kickstart generator</h1>

<form action="" name="FormBlock" method="POST">	
	<input type="hidden" name="action" value="save"/>
	
	<fieldset>
		<label>Source of install</label>
		<select name="source">
			<option selected="true">cdrom</option>
			<option >url</option>
		</select>
	</fieldset>		
	
	<fieldset>
		<label>Installation url</label>
		<select name="url">
			<?
			loadSelectOptions("urls", 0);
			?>
		</select>
		or enter your url <input type="text" name="urlmanual" size="50"/>
	</fieldset>
	
	<fieldset>
		<label>Language</label>
		<select name="lang">
			<?
            loadSelectOptions("langs", -1, "en_US");
            ?>			
		</select>
		
		Charset 
		<select name="charset">
			<option></option>
			<option>ISO-8859-1</option>
			<option selected="true">UTF-8</option>
			<option>Windows-1251</option>
			<option>KOi8-R</option>
		</select>

		Keyboard
		<select name="keyboard">
			<?
            loadSelectOptions("keyboards", -1, "us");
            ?>			
		</select>
	</fieldset>
	
	<fieldset>
		<label>Hostname</label>
		<input type="text" name="hostname" value="ks<?php echo time() ?>" disabled="true" />
		<input type="checkbox" name="hostnameauto" value="1" checked="checked"/>
		<label for="hostnameauto">Automatically generated (will be new on every request)</label>		
	</fieldset>	
	
	<fieldset>
		<label>Network</label>
		<select name="network">
			<option>dhcp</option>
			<option>static</option>
		</select>
		device
		<input type="text" name="networkdev" value="eth0" />
	</fieldset>

	<fieldset>
		<label>Static params</label>
		
		IP
		<input type="text" name="ip" value="192.168.1.101" />
		
		Netmask
		<input type="text" name="netmask" value="255.255.255.0" />
		
		Gateway
		<input type="text" name="gw" value="192.168.1.1" />
		
		DNS
		<input type="text" name="dns" value="192.168.1.1" />
	</fieldset>

	<fieldset>
		<label>Root password</label>
		<input type="text" name="rootpw" value="123456" />
		<input name="rootpwcrypt" type="button" value="Encrypt" /> (encryption done at client-side)
		<input type="checkbox" name="rootpwcrypted" value="1" />
		<label for="rootpwcrypted">Encrypted</label>
	</fieldset>

	<fieldset>
		<label>Firewall</label>
		<select name="firewall">
			<option selected="true">enabled</option>
			<option >disabled</option>
		</select>
	</fieldset>
		
	<fieldset>
		<label>Firewall allow ports</label>
		<input type="text" name="firewallports" value="ssh,http,https"> (ports or names, e.g. ssh, comma separated)
	</fieldset>
	
	<fieldset>
		<label>SELinux</label>
		<select name="selinux">
			<option>enforcing</option>
			<option>permissive</option>
			<option selected="true">disabled</option>
		</select>
	</fieldset>	

	<fieldset>
		<label>Timezone</label>
		<select name="timezone">
			<?
			$deftz = "Asia/Shanghai";
			$tz = file("tz");
			for ($i = 0; $i < count($tz); $i++) {
				$n = trim($tz[$i]);
				echo "<option" . ($n == $deftz ? " selected=\"selected\"" : "") . ">$n</option>\n";
			}
			?>
		</select>
		<input type="checkbox" name="hwclockutc" value="1" />
		<label for="hwclockutc">Hardware clock in UTC</label>
	</fieldset>

	
	<fieldset>
		<label>Root drive</label>
		<select name="hdd">
			<option>/dev/hda</option>
			<option>/dev/hdb</option>
			<option>/dev/hdc</option>
			<option>/dev/hdd</option>
			<option selected="true">/dev/sda</option>
			<option>/dev/sdb</option>
			<option>/dev/sdc</option>
			<option>/dev/sdd</option>
			<option>/dev/xvda</option>
			<option>/dev/xvdb</option>
		</select>
	</fieldset>

	<fieldset>
		<label>Partition model</label>
		<select name="hddlayout">
			<option value="BPRV">/boot on root drive, / and swap in volgroup</option>
			<option value="BPRVHV">/boot on root drive, /, /home and swap in volgroup</option>
			<option value="BPRVVVHV">/boot on root drive, /, /var, /home and swap in volgroup</option>
		</select>
	</fieldset>
	
	<fieldset>
		<label>Boot size</label>
		<input type="text" name="bootsize" value="200m">
	</fieldset>

	<fieldset>
		<label>Root size</label>
		<input type="text" name="rootsize" value="5g">
	</fieldset>

	<fieldset>
		<label>Var size</label>
		<input type="text" name="varsize" value="3g">
	</fieldset>
	
	<fieldset>
		<label>Swap size</label>
		<input type="text" name="swapsize" value="2g">
	</fieldset>
	
	<fieldset>
		<label>Package groups</label>
		<div class="block">
			<input type="checkbox" name="packages-core" checked="checked"> <label for="packages-core">Core (unchecking this is dangerous)</label><br/> 
			<input type="checkbox" name="packages-server"> <label for="packages-server">Server</label<br/>
		</div>
	</fieldset>

	<fieldset>
		<label>Additional packages</label>
		<div class="block">
			<textarea name="packages" cols="25" rows="3">yum</textarea>
		</div>
	</fieldset>
	
	<fieldset>
		<label>Post-install script (%post)</label>
		<div class="block">
			Plugins<br>
			<?
			for ($i = 0; $i < count($postPlugins); $i++) {
				$p = $postPlugins[$i];
				$pid = $p["id"];
				$pname = $p["name"];
				if (!$pname) {
					$pname = $pid;
				}


				$shscript = null;
				if (file_exists($pluginDir . "/" . $pid . ".sh")) {
					$shscript = $pluginDir . "/" . $pid . ".sh";
				}
				if (file_exists($pluginDir . "/" . $pid)) {
					$shscript = $pluginDir . "/" . $pid;
				}

				if ($shscript ||
					file_exists($pluginDir . "/" . $pid . ".php") ||
					$p["code"] != "" || 
					$p["handler"] != "") {
					$pidurl = $shscript ? ("<a href=\"$shscript\" target=\"_blank\">$pid</a>") : $pid;
					?>
					<input type="checkbox" name="plugin-<?echo $i + 1 ?>-<?echo $pid ?>" value="<?echo $pid ?>"/>
					[<?echo $pidurl ?>] <label for="plugin-<?echo $i + 1 ?>-<?echo $pid ?>"><?echo $pname ?></label><br>
					<?
				}
			}
			?>
			<p/>
			Custom code (<a href="#" id="posthide">hide</a>)<br>
			<textarea name="post" cols="60" rows="10" style="display: none"></textarea>
		</div>
	</fieldset>
	
	<?
	// Display postPlugins HTML
	for ($i = 0; $i < count($postPlugins); $i++) {
		$p = $postPlugins[$i];
		$pid = $p["id"];
		
		if ($p["htmlHandler"]) {
			$f = $p["htmlHandler"];
			$f();
		}
	}
	?>	
	
	<fieldset>		
		<? if ($id > 0) { ?>
		<input type="submit" value="Save as new"/>
		<? } else { ?>
		<input type="submit" value="Create"/>	
		<? } ?>
	</fieldset>
</form>
</body>
</html>
<?
} /* $action == "form" */

if ($action == "save") { 
    // rename the file. sorry can't save to the hostname ;)
    $NewKs=$_REQUEST['hostname'];
	rename("./$scriptDir/$id","./$scriptDir/$NewKs");
?>
<html>
	<head>
		<title>[<?echo $_REQUEST['hostname'] ?>] Kickstart script created</title>
	</head>
<body>
<h1>Kickstart script created</h1>

Your kickstart url is <a href="<?echo $ksurl ?><?echo $_REQUEST['hostname'] ?>"><?echo $ksurl ?><?echo $_REQUEST['hostname'] ?></a>
<p>
<h2>Kickstart from network</h2>
<p>
Enter at boot prompt to fully automatic install:
<p>
<code style="padding: 8px; background: black; color: white; display: block;">
	#> text ks=<?echo $ksurl ?><?echo $_REQUEST['hostname'] ?>
</code>
<p>
<? if (false) { ?>
<p>
Edit kickstart script: <a href="<?echo $ksurl ?><?echo $_REQUEST['hostname'] ?>?edit"><?echo $ksurl ?><?echo $_REQUEST['hostname'] ?>?edit</a>
<? } ?>
<p>
Create new kickstart script: <a href="<?echo $ksurl ?>"><?echo $ksurl ?></a>
<p>
extra = "text ks=<?echo $ksurl ?><?echo $_REQUEST['hostname'] ?>"<br/>
</code>
<?
} /* action == "save" */
?>
