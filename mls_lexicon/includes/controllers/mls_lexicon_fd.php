<?php 
require('mls_lexicon_functions.php');
if(isset($_GET['zip'])) {
$file = $_GET['zip'];
$filename = basename($file);
$dir = dirname($file);
header('Content-type:  application/zip');
header('Content-Length: ' . filesize($file));
header("Content-Disposition: attachment; filename=$filename"); //TODO
readfile($file);
mls_lexicon_delete_dir($dir);
ignore_user_abort(true);

if (connection_aborted()) {
	unlink($file);

}
}
?>