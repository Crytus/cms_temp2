<?php

// 2014/08/02
// サイズ指定をパラメータ化
// ドキュメントタイプが不明の場合に、ファイルから検出する（XPERIA対応）

require_once("dbaccess.php");

class Image extends DbAccess {

	static $t_table = array(
		'title' => 'image',
		'name' => 'image',
		'fields' => array(
			1 => 'image_id',
			2 => 'file_name',
			3 => 'title',
			4 => 'image',
			5 => 'type',
			6 => 'size',
			7 => 'reg_date',
		),
		'seq' => 'image_id'
	);

	function Image($page=0, $limit=0, $order="", $search=array()) {
		parent::DbAccess($page, $limit, $order, $search, self::$t_table["name"], self::$t_table["seq"]);
	}

	function getData($id, $fields="", $raw=null) {
		return parent::getData($id, $fields, $raw, self::$t_table["name"], self::$t_table["seq"]);
	}

	function addData($data, $raw=null) {
		return parent::addData($data, $raw, self::$t_table["name"], self::$t_table["seq"]);
	}

	function updateData($id, $data, $raw=null) {
		return parent::updateData($id, $data, $raw, self::$t_table["name"], self::$t_table["seq"]);
	}

	function deleteData($id) {
		return parent::deleteData($id, self::$t_table["name"], self::$t_table["seq"]);
	}

	function findData($cond, $fields=null, $raw=null, $order=null, $page=null) {
		return parent::findData($cond, $fields, $raw, self::$t_table["name"], $order, $page);
	}
}

/* サイズ指定はパラメータに変更
define("IMAGE_MAX_X", 640);
define("IMAGE_MAX_Y", 480);
*/

// 画像の保存
function save_image($img, $user_id=0, $title="", $save_path="", $max_x=0, $max_y=0)
{
	$fp = fopen($img["tmp_name"], "rb");
	if ($fp) {
		// 画像タイプの判定
		if (($img["type"] == "image/pjpeg")||($img["type"] == "image/jpeg")||
			($img["type"] == "image/gif")||($img["type"] == "image/bmp")||
			($img["type"] == "image/png")||($img["type"] == "image/x-png")) {
			// 正しい画像タイプ
		} else {
			// 不明の場合はファイルから得る
			$file_type = getimagesize($img["tmp_name"]);
			$img["type"] = $file_type["mime"];
		}
		$image = fread($fp, filesize($img["tmp_name"]));
		fclose($fp);
		$im_inp = ImageCreateFromString($image);
		if (function_exists("exif_read_data")) {
			$exif = exif_read_data($img["tmp_name"]);
			if ($exif["Orientation"]) {
				$ort = $exif["Orientation"];
			} else {
				$ort = $exif['IFD0']['Orientation'];
			}
			switch ($ort) {
			case 3: // 180 rotate left
				$im_inp = imagerotate($im_inp, 180, 0);
				break;
			case 6: // 90 rotate right
				$im_inp = imagerotate($im_inp, -90, 0);
				break;
			case 8:    // 90 rotate left
				$im_inp = imagerotate($im_inp, 90, 0);
				break;
			}
		}
		//
		$item["x"] = imagesx($im_inp);
		$item["y"] = imagesy($im_inp);
		$item["file_name"] = $img['name'];
		$item["type"] = $img["type"];
		$item["size"] = $img["size"];
		if ($max_x || $max_y) {
			// サイズ調整が必要な場合
			$px = $item["x"];	// オリジナルのサイズ
			$py = $item["y"];
			$sx = 0;
			$sy = 0;
			if ($max_x) {
				if ($px > $max_x) {
					$sx = $max_x;
				} else {
					$sx = $px;
				}
			}
			if ($max_y) {
				if ($py > $max_y) {
					$sy = $max_y;
				} else {
					$sy = $py;
				}
			}
			if (!$sx) {		// 横指定なし
				$sx = intval($px / $py * $sy);
			} else if (!$sy) {	// 縦指定なし
				$sy = intval($py / $px * $sx);
			}
			//
			$rb = floatval($px) / floatval($py);
			$ra = floatval($sx) / floatval($sy);
			if ($ra == $rb) {
				$x = 0;	// 切り取り始点
				$y = 0;
				$wx = $px;	// 切り取りサイズ
				$wy = $py;
			} else if ($ra > $rb) {
				$wy = intval($px / $ra);
				$wx = $px;
				$x = 0;	// 切り取り始点
				$y = intval(($py - $wy) / 2);
			} else {
				$wx = intval($py * $ra);
				$wy = $py;
				$x = intval(($px - $wx) / 2);	// 切り取り始点
				$y = 0;
			}
			// サイズ変更後の画像データを生成
			$im_out = ImageCreateTrueColor($sx, $sy);
			// 切り抜き
			ob_start();
			if (ImageCopyResized($im_out, $im_inp, 0, 0, $x, $y, $sx, $sy, $wx, $wy)) {
				if (($img["type"] == "image/pjpeg")||($img["type"] == "image/jpeg")) {
					imagejpeg($im_out);
				} else if ($img["type"] == "image/gif") {
					imagegif($im_out);
				} else if ($img["type"] == "image/bmp") {
					imagebmp($im_out);
				} else if (($img["type"] == "image/png")||($img["type"] == "image/x-png")) {
					imagepng($im_out);
				}
			}
			$newimg = ob_get_contents();
			ob_end_clean();
			ImageDestroy($im_out);
			//
			$image = $newimg;
		}
		ImageDestroy($im_inp);
		//
		if (!$save_path) {
			$item["image"] = $image;
		}
		$item["user_id"] = $user_id;
		$id = Image::addData($item, true);
		if ($id && $title) {
			$upd["title"] = $title;
			Image::updateData($id, $upd);	// 漢字コード変換対応
		}
		// 実ファイルの保存
		if ($save_path) {
			$ext = "dat";
			if (($img["type"] == "image/pjpeg")||($img["type"] == "image/jpeg")) {
				$ext = "jpg";
			} else if ($img["type"] == "image/gif") {
				$ext = "gif";
			} else if ($img["type"] == "image/bmp") {
				$ext = "bmp";
			} else if (($img["type"] == "image/png")||($img["type"] == "image/x-png")) {
				$ext = "png";
			}
			$save_name = sprintf("%08d.%s", $id, $ext);
			$full_name = $save_path . $save_name;
			//copy($img["tmp_name"], $full_name);	// オリジナルを保存
			$fp = fopen($full_name, "wb");	// 縮小した物を保存
			fwrite($fp, $image);
			fclose($fp);
			//
			unset($upd);
			$upd["save_name"] = $save_name;
			Image::updateData($id, $upd);
		}
		return $id;
	} else {
		echo "File open error.";
	}
	return false;
}
// 指定のイメージが自分のものなら削除
function delete_image($id, $user_id = 0)
{
	$img = Image::getData($id, array("user_id", "type"));
	if ($img["user_id"] == $user_id) {
		if (defined("IMAGE_DIR")) {
			$file = "";
			$dir = IMAGE_DIR;
			if (($img["type"] == "image/pjpeg")||($img["type"] == "image/jpeg")) {
				$ext = "jpg";
				$file = $dir . sprintf("%08d.%s", $id, $ext);
			} else if ($img["type"] == "image/gif") {
				$ext = "gif";
				$file = $dir . sprintf("%08d.%s", $id, $ext);
			} else if ($img["type"] == "image/bmp") {
				$ext = "bmp";
				$file = $dir . sprintf("%08d.%s", $id, $ext);
			} else if (($img["type"] == "image/png")||($img["type"] == "image/x-png")) {
				$ext = "png";
				$file = $dir . sprintf("%08d.%s", $id, $ext);
			}
			if ($file) {
				unlink($file);
			}
		}
		return Image::deleteData($id);
	}
	return false;
}
// 画像の取得
function get_image($id, $file="", $sx=0, $sy=0, $pv=0, $mx=0, $my=0)
{
	if ($id) {	// DBから
		// キャッシュのチェック
		if (defined("IMAGE_CACHE")) {
			$img_file = sprintf(IMAGE_CACHE . "%08d_%03d_%03d.jpg", $id, $sx, $sy);
			if (file_exists($img_file)) {
				$fp = fopen($img_file, "rb");
				$img = fread($fp, filesize($img_file));
				fclose($fp);
				header("Content-type: image/jpeg");
				echo $img;
				return;
			}
		}
		$img = Image::getData($id, null, true);
		if ($img) {
			if ($img["image"]) {
				// 読み込んだデータを内部画像へ
				$im_inp = ImageCreateFromString($img["image"]);
				if ($pv) {	// 参照回数カウント
					$sql = "update image set page_view=page_view+1 where image_id=" . $id;
					$inst = DBConnection::getConnection($DB_URI);
					$inst->db_exec($sql);
				}
			} else {
				$dir = "";
				if (defined("IMAGE_DIR")) {
					$dir = IMAGE_DIR;
				}
				if (($img["type"] == "image/pjpeg")||($img["type"] == "image/jpeg")) {
					$ext = "jpg";
					$file = $dir . sprintf("%08d.%s", $id, $ext);
					$im_inp = ImageCreateFromJPEG($file);
				} else if ($img["type"] == "image/gif") {
					$ext = "gif";
					$file = $dir . sprintf("%08d.%s", $id, $ext);
					$im_inp = ImageCreateFromGIF($file);
				} else if ($img["type"] == "image/bmp") {
					$ext = "bmp";
					$file = $dir . sprintf("%08d.%s", $id, $ext);
					$im_inp = ImageCreateFromwBMP($file);
				} else if (($img["type"] == "image/png")||($img["type"] == "image/x-png")) {
					$ext = "png";
					$file = $dir . sprintf("%08d.%s", $id, $ext);
					$im_inp = ImageCreateFromPNG($file);
				}
			}
		}
	} else if ($file) {		// ファイルから
		$file_type = getimagesize($file);
		if ($file_type["mime"] == "image/gif") {
			$im_inp = ImageCreateFromGIF($file);
		}
		if ($file_type["mime"] == "image/jpg") {
			$im_inp = ImageCreateFromJPEG($file);
		}
		if ($file_type["mime"] == "image/jpeg") {
			$im_inp = ImageCreateFromJPEG($file);
		}
		if ($file_type["mime"] == "image/png") {
			$im_inp = ImageCreateFromPNG($file);
		}
	}
	if ($im_inp) {
		$px = imagesx($im_inp);
		$py = imagesy($im_inp);
		if ($mx || $my) {	// 最大サイズ指定
			if ($mx) {
				if ($px > $mx) {
					$sx = $mx;
				} else {
					$sx = $px;
				}
			}
			if ($my) {
				if ($py > $my) {
					$sy = $my;
				} else {
					$sy = $py;
				}
			}
		}
		if ($sx || $sy) {	// サイズ指定有り
			if (!$sx) {		// 横指定なし
				$sx = intval($px / $py * $sy);
			} else if (!$sy) {	// 縦指定なし
				$sy = intval($py / $px * $sx);
			}
			//
			$rb = floatval($px) / floatval($py);
			$ra = floatval($sx) / floatval($sy);
			if ($ra == $rb) {
				$x = 0;	// 切り取り始点
				$y = 0;
				$wx = $px;	// 切り取りサイズ
				$wy = $py;
			} else if ($ra > $rb) {
				$wy = intval($px / $ra);
				$wx = $px;
				$x = 0;	// 切り取り始点
				$y = intval(($py - $wy) / 2);
			} else {
				$wx = intval($py * $ra);
				$wy = $py;
				$x = intval(($px - $wx) / 2);	// 切り取り始点
				$y = 0;
			}
			// サイズ変更後の画像データを生成
			$im_out = ImageCreateTrueColor($sx, $sy);
			// 切り抜き
			ob_start();
			if (ImageCopyResized($im_out, $im_inp, 0, 0, $x, $y, $sx, $sy, $wx, $wy)) {
				imagejpeg($im_out);
			} else {
				imagejpeg($im_inp);
			}
			$img = ob_get_contents();
			ob_end_clean();
			//
			if ($img_file && defined("IMAGE_CACHE")) {
				// キャッシュファイル作成
				$fp = fopen($img_file, "wb");
				fwrite($fp, $img);
				fclose($fp);
			}
			//
			header("Content-type: image/jpeg");
			echo $img;	// 画像出力
			//
			ImageDestroy($im_out);
			ImageDestroy($im_inp);
		} else {	// オリジナルのサイズのまま
			ob_start();
			imagejpeg($im_inp);
			$img = ob_get_contents();
			ob_end_clean();
			ImageDestroy($im_inp);
			if ($img_file && defined("IMAGE_CACHE")) {
				// キャッシュファイル作成
				$fp = fopen($img_file, "wb");
				fwrite($fp, $img);
				fclose($fp);
			}
			//
			header("Content-type: image/jpeg");
			echo $img;	// 画像出力
		}
	} else {
		// エラー
		header("HTTP/1.1 404 Not Found");	// ファイルが見つからない
	}
}
// フォームからの画像登録
function form_image($key, &$msg, $type=array(), $size=0, $max_x=0, $max_y=0)
{
	$img_id = '';
	if ($_FILES[$key]["name"]) {
		// 以前の画像があれば
		if ($_REQUEST[$key . "_old"]) {
			// ここでは削除できない
			//delete_image($_REQUEST[$key . "_old"], $id);
		}
		$error = "";
		if ($type) {
			if ($type[$_FILES[$key]["type"]]) {
				// ok
			} else {
				$error = "ファイルの種類が正しくありません";
			}
		}
		if ($size) {
			if ($_FILES[$key]["size"] > $size) {
				$error = "ファイルのサイズが大きすぎます";
			}
		}
		if ($error == "") {
			if (defined("IMAGE_DIR")) {
				$img_id = save_image($_FILES[$key], 0, "", IMAGE_DIR, $max_x, $max_y);
			} else {
				$img_id = save_image($_FILES[$key], 0, "", "", $max_x, $max_y);
			}
			if ($img_id) {
				//
			} else {
				$msg[$key] = "保存できませんでした。";
			}
		}
		if ($error) {
			$msg[$key] = $error;
		}
	} else if ($_REQUEST[$key . "_old"]) {
		if ($_REQUEST[$key . "_del"]) {
			// ここでは削除できない
			//delete_image($_REQUEST[$key . "_old"], $id);	// 画像は削除
			$img_id = array();
		} else {
			$img_id = $_REQUEST[$key . "_old"];	// 前回の画像のまま
		}
	}
	return $img_id;
}
function image_copy($image_id)
{
	$dir = "";
	if (defined("IMAGE_DIR")) {
		$dir = IMAGE_DIR;
	}
	$img = Image::getData($image_id);
	$org_file = $img["save_name"];
	unset($img["image_id"]);
	unset($img["save_name"]);
	$id = Image::addData($img);
	if (($img["type"] == "image/pjpeg")||($img["type"] == "image/jpeg")) {
		$ext = "jpg";
		$img["save_name"] = sprintf("%08d.%s", $id, $ext);
	} else if ($img["type"] == "image/gif") {
		$ext = "gif";
		$img["save_name"] = sprintf("%08d.%s", $id, $ext);
	} else if ($img["type"] == "image/bmp") {
		$ext = "bmp";
		$img["save_name"] = sprintf("%08d.%s", $id, $ext);
	} else if (($img["type"] == "image/png")||($img["type"] == "image/x-png")) {
		$ext = "png";
		$img["save_name"] = sprintf("%08d.%s", $id, $ext);
	}
	copy($dir . $org_file, $dir . $img["save_name"]);
	Image::updateData($id, $img);
	return $id;
}
?>