<?php
require_once("dbaccess.php");
class File extends DbAccess {

	static $t_table = array(
		'title' => 'file',
		'name' => 'file',
		'fields' => array(
			1 => 'file_id',
			2 => 'user_id',
			3 => 'file_name',
			4 => 'save_name',
			5 => 'title',
			6 => 'type',
			7 => 'size',
			8 => 'reg_date',
		),
		'seq' => 'file_id'
	);

	function File($page=0, $limit=0, $order="", $search=array()) {
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
/*
CREATE TABLE `file` (
  `file_id` int(11) NOT NULL auto_increment COMMENT 'ファイルID',
  `user_id` int(11) default NULL COMMENT '登録者',
  `file_name` tinytext collate utf8_unicode_ci COMMENT 'ファイル名',
  `save_name` tinytext collate utf8_unicode_ci COMMENT '保存ファイル名',
  `title` tinytext collate utf8_unicode_ci COMMENT 'タイトル',
  `type` tinytext collate utf8_unicode_ci COMMENT 'ファイルタイプ',
  `size` int(11) default NULL COMMENT 'サイズ',
  `reg_date` timestamp NOT NULL default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP COMMENT '登録日',
  PRIMARY KEY  (`file_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;
*/
function save_file($file, $user_id=0, $title="", $save_path="")
{
	$fp = fopen($file["tmp_name"], "rb");
	if ($fp) {
		$item["user_id"] = $user_id;
		$id = File::addData($item, true);
		if ($id) {
			if ($title) {
				$upd["title"] = $title;
			}
			$upd["type"] = $file["type"];
			$upd["size"] = $file["size"];
			$upd["file_name"] = $file["name"];
			File::updateData($id, $upd);	// 漢字コード変換対応
		}
		// 実ファイルの保存
		if ($save_path) {
			$ary = explode(".", $file["name"]);
			if (count($ary) > 1) {
				$ext = $ary[count($ary) - 1];
			} else {
				$ext = "dat";
			}
			$save_name = sprintf("%08d.%s", $id, $ext);
			$full_name = $save_path . $save_name;
			copy($file["tmp_name"], $full_name);	// オリジナルを保存
			//
			unset($upd);
			$upd["save_name"] = $save_name;
			File::updateData($id, $upd);
		}
		return $id;
	} else {
		echo "File open error.";
	}
	return false;
}
// 指定のイメージが自分のものなら削除
function delete_file($id, $user_id = 0)
{
	$file = File::getData($id, array("user_id", "type"));
	if ($file["user_id"] == $user_id) {
		if (defined("FILE_DIR")) {
			$filename = FILE_DIR . $file["save_file"];
			unlink($filename);
		}
		return File::deleteData($id);
	}
	return false;
}
// フォームからの画像登録
function form_file($key, &$msg, $type=array(), $size=0)
{
	$file_id = '';
	if ($_FILES[$key]["name"]) {
		// 以前の画像があれば
		if ($_REQUEST[$key . "_old"]) {
			// ここでは削除できない
			//delete_file($_REQUEST[$key . "_old"], $id);
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
			if ($_FILES[$key]["type"] > $size) {
				$error = "ファイルのサイズが大きすぎます";
			}
		}
		if ($error == "") {
			if (defined("FILE_DIR")) {
				$file_id = save_file($_FILES[$key], 0, "", FILE_DIR);
			} else {
				$file_id = save_file($_FILES[$key], 0, "");
			}
			if ($file_id) {
				//
			} else {
				$error = "保存できませんでした。";
			}
		}
		if ($error) {
			$msg[$key] = $error;
		}
	} else if ($_REQUEST[$key . "_old"]) {
		if ($_REQUEST[$key . "_del"]) {
			// ここでは削除できない
			//delete_file($_REQUEST[$key . "_old"], $id);	// 画像は削除
			$file_id = array();
		} else {
			$file_id = $_REQUEST[$key . "_old"];	// 前回の画像のまま
		}
	}
	return $file_id;
}
?>