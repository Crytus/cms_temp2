<?php
require_once("dbaccess.php");
require_once("info_item.php");

class Info extends DbAccess {

	static $t_table = array(
		'title' => 'info',
		'name' => 'info',
		'fields' => array(
			1 => 'info_id',
			2 => 'kind',
			3 => 'title',
			4 => 'contents',
			5 => 'ord',
			6 => 'reg_date',
		),
		'seq' => 'info_id'
	);

	function Info($page=0, $limit=0, $order="", $search=array()) {
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
function get_info($id = INFO_SETUP)
{
	$data = array();
	$cond["kind"] = $id;
	$ret = Info::findData($cond);
	if ($ret) {
		$data = get_info_item($ret[0]["info_id"]);
	}
	return $data;
}
?>
