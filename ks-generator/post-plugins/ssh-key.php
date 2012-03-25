<?php 
/* Default plugin */
/* Adds ability to specify ssh keys for root user */

function sshKeyHtml() {
	?>
	<fieldset>
		<label>SSH public keys (for ssh-key plugin)</label>
		<div class="block">
			<textarea name="sshpubkeys" cols="60"></textarea><br/>
		</div>
	</fieldset>
	<?
}

function sshKeyHandler($var) {
?>
mkdir /root/.ssh
chmod u=rwx,g=,o= /root/.ssh
<?
$l = explode("\n", $var["sshpubkeys"]);
for ($i = 0; $i < count($l); $i++) {
	?>echo "<?echo trim($l[$i]) ?>" >> /root/.ssh/authorized_keys
<?
}
?>
chmod u=rw,g=,o= /root/.ssh/authorized_keys	
<?
}

postPlugin(array("id" => "ssh-key", "htmlHandler" => "sshKeyHtml", "handler" => "sshKeyHandler"));

?>
