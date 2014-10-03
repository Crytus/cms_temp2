<?php
/*
 * データベースアクセス、関連処理
 */
require_once("my_db.php");

static $inst = null;

class DbAccess {
	var $page;		// 取得ページ
	var $limit;		// 取得件数
	var $order;		// 取得順序
//	var $inst;		// データベースインスタンス
	var $search;	// 検索条件
	var $table_name;
	var $table_key;
	var $join;
    /**
     * @desc コンストラクタ
     */
    function DbAccess($page, $limit, $order, $search, $name, $key) {
		global $DB_URI;
		// 初期条件
		$this->page = $page;
		$this->limit = $limit;
		$this->order = $order;
		$this->search = $search;
	//	$this->inst = DBConnection::getConnection($DB_URI);
		$this->table_name = $name;
		$this->table_key = $key;
		$this->join = array();
		if (!$inst) {
			$inst = DBConnection::getConnection($DB_URI);
		}
    }

	/**
	 * @name getCount
	 * @desc 条件に合致するデータ件数を得る
	 * @return 件数
	 **/
	function getCount() {
		global $DB_URI;
		if (!$inst) {
			$inst = DBConnection::getConnection($DB_URI);
		}
		if ($inst) {
			if ($this->join) {
				array_unshift($this->join, array("table" => $this->table_name));
				$ret = $inst->count_data($this->join, $this->search);
			} else {
				$ret = $inst->count_data($this->table_name, $this->search);
			}
			if ($ret["error"] != 1) {
				return $ret["count"];
			}
		}
		return 0;
	}
	/**
	 * @name getList
	 * @desc 条件に合致するデータを得る
	 * @return データの配列
	 **/
	function getList($fields=null, $raw=false) {
		global $DB_URI;
		if (!$inst) {
			$inst = DBConnection::getConnection($DB_URI);
		}
		if ($inst) {
			// 出力位置、件数
			if ($this->limit) {
				$page = array("offset" => $this->page * $this->limit, "limit" => $this->limit);
			}
			// 出力順序
			if ($this->order) {
				$order = $this->order;
			}
			// 結合
			if ($this->join) {
				array_unshift($this->join, array("table" => $this->table_name));
				$ret = $inst->search_data($this->join,  $this->search, $order, $page, $fields, $raw);
			} else {
				// 検索実行
				$ret = $inst->search_data($this->table_name,  $this->search, $order, $page, $fields, $raw);
			}
			if ($ret["count"]) {
				return $ret["data"];
			}
		}
		return array();
	}

	/**
	 * @name getData
	 * @desc 該当データを取得する。
	 * @param $id 取得するid
	 * @return 実行結果（配列に件数と、データが入る）
	 **/
	function getData($id, $fields, $raw, $table_name, $table_key) {
		global $DB_URI;
		if (!$inst) {
			$inst = DBConnection::getConnection($DB_URI);
		}
		if ($inst) {
			$where[] = array("field" => $table_key, "value" => $id);
			$ret = $inst->search_data($table_name, $where, null, null, $fields, $raw);
			if ($ret["count"] > 0) {
			//	$inst->db_close();
				return $ret["data"][0];
			}
		}
		return false;
	}

	/**
	 * @name addData
	 * @desc データを追加する。
	 * @param $data 保存するデータ
	 * @return 実行結果
	 **/
	function addData($data, $raw, $table_name, $table_key) {
 		global $DB_URI;
		if (!$inst) {
			$inst = DBConnection::getConnection($DB_URI);
		}
		if ($inst) {
			$ret = $inst->insert_data($table_name, $data, $raw);
			if ($ret) {
				$sql = "select last_insert_id() as id";
				$ret = $inst->search_sql($sql);
				if ($ret["count"]) {
				//	$inst->db_close();
					return $ret["data"][0]["id"];	// 挿入データのID
				}
			//	$inst->db_close();
				return false;	// 失敗
			}
		//	$inst->db_close();
			return $ret;
		}
		return false;
	}

	/**
	 * @name updateData
	 * @desc データを更新する。
	 * @param $id 更新するデータID
	 * @param $data 更新するデータ
	 * @return 実行結果
	 **/
	function updateData($id, $data, $raw, $table_name, $table_key) {
		global $DB_URI;
		if (!$inst) {
			$inst = DBConnection::getConnection($DB_URI);
		}
		if ($inst) {
			$where[] = array("field" => $table_key, "value" => $id, "cond" => "=");
			$ret = $inst->update_data($table_name, $data, $where, $raw);
		//	$inst->db_close();
			return $ret;
		}
		return false;
	}

	/**
	 * @name deleteData
	 * @desc データを削除する。
	 * @param $id 削除するデータIDまたはIDの配列
	 * @return 実行結果
	 **/
	function deleteData($id, $table_name, $table_key) {
		global $DB_URI;
		if (!$inst) {
			$inst = DBConnection::getConnection($DB_URI);
		}
		if ($inst) {
			if (is_array($id)) {
				$where[] = array("field" => $table_key, "value" => $id, "cond" => "in");
			} else {
				$where[] = array("field" => $table_key, "value" => $id, "cond" => "=");
			}
			$ret = $inst->delete_data($table_name, $where);
		//	$inst->db_close();
			return $ret;
		}
		return false;
	}
	/**
	 * @name findData
	 * @desc 特定のデータを得る
	 * @param $cond 取得するデータ条件の配列
	 * @return データが無ければ空の配列、存在すればデータの配列
	 **/
	function findData($cond, $fields, $raw, $table_name, $order=null, $page=null) {
		global $DB_URI;
		if (!$inst) {
			$inst = DBConnection::getConnection($DB_URI);
		}
		if ($inst) {
			foreach ($cond as $field => $val) {
				$where[] = array("field" => $field, "value" => $val, "cond" => "=");
			}
			$ret = $inst->search_data($table_name, $where, $order, $page, $fields, $raw);
			if ($ret["count"] > 0) {
			//	$inst->db_close();
				return $ret["data"];
			}
		//	$inst->db_close();
		}
		return false;
	}
}
?>
