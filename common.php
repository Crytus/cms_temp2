<?php
/*
 * �A�v���P�[�V�����P�ʂ̋��ʏ���
 */
require_once("lib/config.php");

require_once("info.php");
require_once("image.php");
require_once("filesave.php");

require_once("common_func.php");
require_once("login.php");

define("PAGE_ITEMS", 10);

// �A�v���P�[�V�����N���X

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
		// �e���v���[�g��SJIS�Ή�(SJIS�̏ꍇ�L���ɂ���)
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

		$this->template = 'index.html';	// �f�t�H���g
		if ($_REQUEST["act"]) {
			$this->act = $_REQUEST["act"];
			// act������΁A���ꂪ�f�t�H���g�e���v���[�g
			$this->template = $this->act . '.html';
		}
/* ���O�C���A���O�A�E�g
		if ($this->act == "logout") {
			// ���O�A�E�g
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
			// ��ʗ��p�҂Ƃ��ă��O�C����
			$this->set("user_login", $_SESSION["LOGIN"]);
			$this->set("login", $_SESSION["LOGIN"]["user_id"]);
			$this->set("user_name", $_SESSION["LOGIN"]["nickname"]);
		}
*/
		// �S�y�[�W���ʃf�[�^��p�ӂ���
		$this->inst = DBConnection::getConnection($DB_URI);
/*
		// �W�������ꗗ
		$sql = "select * from genre where status=1 order by ord";
		$ret = $this->inst->search_sql($sql);
		if ($ret["count"]) {
			$this->genre = $ret["data"];
			$this->set("genre", $this->genre);
		}
		// �o�^���[�U�[��
		$sql = "select count(*) as cnt from user where status=1";
		$ret = $this->inst->search_sql($sql);
		if ($ret["count"]) {
			$this->set("user_count", $ret["data"][0]["cnt"]);
		}
*/
		$this->set("top", TOP);
	}

	// �ϐ���ݒ�
	function set($list, $value=array()) {
		if (is_array($list)) {
			foreach ($list as $key => $val) {
				$this->assign($key, $val);
			}
		} else {
			$this->assign($list, $value);
		}
	}

	// �e���v���[�g���m�肷��
	function fix_template() {
		// �e���v���[�g�t�@�C���̑��݊m�F
		if (file_exists("templates/" . $this->template)) {
			//
		} else {
			// �t�@�C�������݂��Ȃ���΃f�t�H���g�y�[�W
			$this->template = 'index.html';
		}
	}

	// �e���v���[�g�̕\��
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
// Smarty SJIS�Ή��p�֐�1
function convert_encoding_to_eucjp($template_source) {
    if (function_exists("mb_convert_encoding")) {
        //�����R�[�h��ϊ�����
        return mb_convert_encoding($template_source, "EUC-JP", "SJIS");
    }
    return $template_source;
}

// Smarty SJIS�Ή��p�֐�2
function convert_encoding_to_sjis($template_source) {
    if (function_exists("mb_convert_encoding")) {
        //�����R�[�h��ϊ�����
        return mb_convert_encoding($template_source, "SJIS", "EUC-JP");
    }
    return $template_source;
}
*/
?>