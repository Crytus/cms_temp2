<?php
// --------------------------------
// ファイルダウンロード

require_once("lib/config.php");
require_once("filesave.php");

// 添付ファイルダウンロード
$id = htmlspecialchars($_REQUEST["id"]);
$file = File::getData($id);
if ($file) {
	$filename = $file["file_name"];
	$ua = $_SERVER['HTTP_USER_AGENT'];
	if (strstr($ua, 'MSIE') && !strstr($ua, 'Opera')) {
	    $filename = mb_convert_encoding($filename, "SJIS", "UTF-8");
	}
	if ($file["image"]) {
		$image = $file["image"];
	} else {
		$file = FILE_DIR . $file["save_name"];
		$fp = fopen($file, "rb");
		if ($fp) {
			$image = fread($fp, filesize($file));
			fclose($fp);
		}
	}
	//$filename = urlencode($filename);
	header("Content-disposition: attachment; filename=" . $filename);
	header("Content-type: " . $file["type"]);
	if (strstr($ua, 'MSIE')) {
		header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
		header("Pragma]: public");
	} else {
		header("Pragma]: no-cache");
	}
	echo $image;
}
exit;
?>
