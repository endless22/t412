<?php
require_once __DIR__ . '/' . 'utils.class.php';
$t411 = new Utils;

$t411->id = !empty($_GET['id']) ? $_GET['id'] : $t411->home();
$t411->getDetails();
$t411->getBase64Torrent();

$torrentfile = tempnam(sys_get_temp_dir(), $t411->id);
$handle = fopen($torrentfile, 'w');
fwrite($handle, base64_decode($t411->base64));
fclose($handle);

header('Content-Description: File Transfer');
header('Content-Type: application/x-bittorent');
header('Content-disposition: attachment; filename="' . $t411->details->name . '.torrent"');
header('Expires: 0');
header('Cache-Control: must-revalidate');
header('Pragma: public');
readfile($torrentfile);

unlink($torrentfile);
?>
