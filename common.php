<?php
/*
 * アプリケーション単位の共通処理
 */
require_once("lib/config.php");

require_once("info.php");
require_once("image.php");
require_once("filesave.php");

require_once("common_func.php");
require_once("login.php");

define("PAGE_ITEMS", 10);

// アプリケーションクラス

require_once("smarty/Smarty.class.php");

class application extends Smarty {

	var $template;
	var $act;
	var $admin_info;

	function application()
	{
		global $DB_URI;

		session_start();
/*
		// テンプレートのSJIS対応(SJISの場合有効にする)
		$this->register_prefilter("convert_encoding_to_eucjp");
		$this->register_postfilter("convert_encoding_to_sjis");
*/
		if (get_magic_quotes_gpc()) {
			$_REQUEST = $this->safeStripSlashes($_REQUEST);
			$_GET = $this->safeStripSlashes($_GET);
			$_POST = $this->safeStripSlashes($_POST);
		}
		if ($_REQUEST["act"] == "logined") {
			$_REQUEST = $_SESSION['REQUEST'];
			unset($_SESSION['REQUEST']);
			$this->act = $_REQUEST["act"];
		}

		$this->template = 'index.html';	// デフォルト
		if ($_REQUEST["act"]) {
			$this->act = $_REQUEST["act"];
			// actがあれば、それがデフォルトテンプレート
			$this->template = $this->act . '.html';
		}
/* ログイン、ログアウト
		if ($this->act == "logout") {
			// ログアウト
			unset($_SESSION["LOGIN"]);
		//	$this->act = "login";
		//	$this->template = 'login.html';
			removeLoginCookie(LOGIN_COOKIE);
		} else if (!$_SESSION["LOGIN"]) {
			$ret = isLogin(LOGIN_COOKIE);
			if ($ret) {
				$cond["user_id"] = $ret["user_id"];
				$cond["email"] = $ret["name"];
				$ret = User::findData($cond);
				if ($ret && ($ret[0]["status"] == "1")) {
					$user = $ret[0];
					$_SESSION["LOGIN"] = $user;
				}
			}
		}
		if ($_SESSION["LOGIN"]) {
			// 一般利用者としてログイン中
			$this->set("user_login", $_SESSION["LOGIN"]);
			$this->set("login", $_SESSION["LOGIN"]["user_id"]);
			$this->set("user_name", $_SESSION["LOGIN"]["nickname"]);
		}
*/
		// 全ページ共通データを用意する
		$this->inst = DBConnection::getConnection($DB_URI);
/*
		// ジャンル一覧
		$sql = "select * from genre where status=1 order by ord";
		$ret = $this->inst->search_sql($sql);
		if ($ret["count"]) {
			$this->genre = $ret["data"];
			$this->set("genre", $this->genre);
		}
		// 登録ユーザー数
		$sql = "select count(*) as cnt from user where status=1";
		$ret = $this->inst->search_sql($sql);
		if ($ret["count"]) {
			$this->set("user_count", $ret["data"][0]["cnt"]);
		}
*/
		$this->set("top", TOP);
	}

	// 変数を設定
	function set($list, $value=array()) {
		if (is_array($list)) {
			foreach ($list as $key => $val) {
				$this->assign($key, $val);
			}
		} else {
			$this->assign($list, $value);
		}
	}

	// テンプレートを確定する
	function fix_template() {
		// テンプレートファイルの存在確認
		if (file_exists("templates/" . $this->template)) {
			//
		} else {
			// ファイルが存在しなければデフォルトページ
			$this->template = 'index.html';
		}
	}

	// テンプレートの表示
	function view() {
		$this->display($this->template);
	}

	function safeStripSlashes($var) {
		if (is_array($var)) {
			foreach ($var as $key => $v) {
				$var[$key] = $this->safeStripSlashes($v);
			}
			return $var;
			//return array_map('application::safeStripSlashes', $var);
		} else {
			if (get_magic_quotes_gpc()) {
				$var = stripslashes($var);
			}
			return $var;
		}
	}

}
/*
// Smarty SJIS対応用関数1
function convert_encoding_to_eucjp($template_source) {
    if (function_exists("mb_convert_encoding")) {
        //文字コードを変換する
        return mb_convert_encoding($template_source, "EUC-JP", "SJIS");
    }
    return $template_source;
}

// Smarty SJIS対応用関数2
function convert_encoding_to_sjis($template_source) {
    if (function_exists("mb_convert_encoding")) {
        //文字コードを変換する
        return mb_convert_encoding($template_source, "SJIS", "EUC-JP");
    }
    return $template_source;
}
*/
?>
