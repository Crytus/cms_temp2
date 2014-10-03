<?php
// --------------------------------
// 写真表示

require_once("lib/config.php");
require_once("image.php");

$id = htmlspecialchars($_REQUEST["id"]);
$file = htmlspecialchars($_REQUEST["file"]);
$size = htmlspecialchars($_REQUEST["size"]);	// sizeで指定した矩形の切り取り
$tx = htmlspecialchars($_REQUEST["x"]);	// x,yで指定した矩形の切り取り
$ty = htmlspecialchars($_REQUEST["y"]);	// x,yで指定した矩形の切り取り
$pv = htmlspecialchars($_REQUEST["pv"]);
if ($id || $file) {
	if ($tx || $ty) {
		$sx = 0;
		$sy = 0;
		if ($tx) {
			$sx = $tx;
		}
		if ($ty) {
			$sy = $ty;
		}
	} else if ($size) {
		$sx = $size;
		$sy = $size;
	} else {
		$sx = 0;
		$sy = 0;
	}
	get_image($id, $file, $sx, $sy, $pv);
}
?>
