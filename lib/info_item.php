<?php
require_once("dbaccess.php");
class InfoItem extends DbAccess {

	static $t_table = array(
		'title' => 'info_item',
		'name' => 'info_item',
		'fields' => array(
			1 => 'seq',
			2 => 'info_id',
			3 => 'kind',
			4 => 'contents',
			5 => 'reg_date',
		),
		'seq' => 'seq'
	);

	function InfoItem($page=0, $limit=0, $order="", $search=array()) {
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
// メッセージ保存
function save_info_item($id, $item)
{
	// 現在の設定を削除
	$cond["info_id"] = $id;
	$ret = InfoItem::findData($cond);
	if ($ret) {
		foreach ($ret as $val) {
			InfoItem::deleteData($val["seq"]);
		}
	}
	// 新しい情報を保存
	foreach ($item as $key => $val) {
		if (is_array($val)) {
			foreach ($val as $val2) {
				unset($item);
				$item["info_id"] = $id;
				$item["kind"] = $key;
				$item["contents"] = $val2;
				InfoItem::addData($item);
			}
		} else {
			unset($item);
			$item["info_id"] = $id;
			$item["kind"] = $key;
			$item["contents"] = $val;
			InfoItem::addData($item);
		}
	}
}
// メッセージ取得
function get_info_item($id)
{
	$item = array();
	$cond["info_id"] = $id;
	$ret = InfoItem::findData($cond);
	if ($ret) {
		foreach ($ret as $val) {
			if (isset($item[$val["kind"]])) {
				$item[$val["kind"]] .= "," . $val["contents"];
			} else {
				$item[$val["kind"]] = $val["contents"];
			}
		}
	}
	return $item;
}
?>