<?php
/*
 * 管理
 * 2009 by Crytus. Y.Kurihara
 */
require_once("../lib/config.php");

require_once("cms2.php");

//require_once("user.php");
//require_once("qa.php");
//require_once("answer.php");
//require_once("genre.php");
//require_once("news.php");

require_once("info.php");
require_once("info_item.php");
require_once("image.php");

require_once("common_func.php");

require_once("smarty/Smarty.class.php");

/*
require_once("mail.php");
require_once("auth.php");
*/
session_start();

/*
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
*/

// magic_quotes_gpc対策
if (get_magic_quotes_gpc()) {
	$_REQUEST = safeStripSlashes($_REQUEST);
	$_GET = safeStripSlashes($_GET);
	$_POST = safeStripSlashes($_POST);
}

// --------------------------------
// 共通前処理

class administrator extends Smarty {

	var $template;
	var $act;
	var $admin_info;

	function administrator()
	{
		global $DB_URI;

		session_start();

		if (get_magic_quotes_gpc()) {
			$_REQUEST = $this->safeStripSlashes($_REQUEST);
			$_GET = $this->safeStripSlashes($_GET);
			$_POST = $this->safeStripSlashes($_POST);
		}
		$this->inst = DBConnection::getConnection($DB_URI);

		$this->set("top", TOP);
		$this->set("sitename", SITENAME);

		if (defined("GMAP_KEY")) {
			$this->set("gmap_key", GMAP_KEY);
		}
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

	// テンプレートの表示
	function view($template) {
		if ($template) {
			$this->display($template);
		} else {
			$this->display($this->template);
		}
	}
}

$html = new administrator();

//$html->debugging = true;

$act = $_REQUEST["act"];

//-----------------------------------
// 認証
if ($act == "logout") {	// ログアウト
	unset($_SESSION["ADMIN_LOGIN"]);
}
if ($act == "login") {	// ログイン
	if ($_REQUEST["id"] && $_REQUEST["passwd"]) {
		$id = htmlspecialchars($_REQUEST["id"]);
		$passwd = htmlspecialchars($_REQUEST["passwd"]);
		//
		$setup = get_info(INFO_SETUP);
		if (($id == $setup["login_id"])&&($passwd == $setup["passwd"])) {
			$_SESSION["ADMIN_LOGIN"] = $setup;
			header("location: ./");
			exit;
		}
		$data["message"] = "ログインできません。IDとパスワードを確認してください。";
	}
	$html->display("login.html");
	exit;
}
// --------------------------------
// ログイン成功後の処理の継続の場合
if ($act == 'logined') {
	$_REQUEST = $_SESSION['REQUEST'];
	unset($_SESSION['REQUEST']);
	$act = htmlspecialchars($_REQUEST['act']);
}
//-----------------------------------
if (isset($_SESSION["ADMIN_LOGIN"])) {
	$data["login"] = 1;
} else {
	$data['logout'] = 1;
}
// 管理画面はログインが必要
//login_check("ログインしてください");

$admin_menu = array(
	array("title" => "登録情報"),

//	array("act" => "user", "title" => "ユーザー"),
//	array("act" => "qa", "title" => "掲示板"),

	array("title" => "管理情報"),
//	array("act" => "genre", "title" => "掲示板カテゴリ"),
//	array("act" => "news", "title" => "お知らせ"),
	array("act" => "mail", "title" => "メール"),
	array("act" => "setup", "title" => "設定"),
	array("act" => "logout", "title" => "ログアウト"),
);

$html->set("menu", $admin_menu);

// --------------------------------
// 写真表示
if ($act == "get_image") {
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
	exit;
}

// --------------------------------
$act_ary = explode("_", $act);
$html->set("act", $act_ary[0]);
// --------------------------------
function user_link($key, $val)
{
	$item = User::getData($val);
	if ($item) {
		if ($item["nickname"]) {
			$name = $item["nickname"];
		} else {
			$name = $item["name"];
		}
		return "<a href=\"?act=user_edit&id={$val}\" target=\"_blank\">" . $name . "</a>";
	}
	return $val;
}

$user_data = array(
	"name" => "user",	// 処理名
	"id" => "user_id",	// IDフィールド
	"title" => "利用者",
	"list" => array(
		"form" => "list.html",
		"items" => array(
/*
			"user_id" => array(
				"title" => "利用者ID",
			),
*/
			"name" => array(
				"title" => "名前・企業名",
			),
			"nickname" => array(
				"title" => "ニックネーム",
			),
			"email" => array(
				"title" => "メールアドレス",
			),
/*
			"passwd" => array(
				"title" => "パスワード",
			),
			"url" => array(
				"title" => "ホームページ",
			),
			"contents" => array(
				"title" => "説明",
			),
			"template" => array(
				"title" => "利用テンプレート",
			),
			"category1" => array(
				"title" => "登録カテゴリ1",
			),
			"category2" => array(
				"title" => "登録カテゴリ2",
			),
			"pref" => array(
				"title" => "都道府県",
			),
			"open" => array(
				"title" => "掲載希望",
			),
			"code" => array(
				"title" => "事業者コード",
			),
*/
			"status" => array(
				"title" => "状態",
				"values" => array(
					"1" => "承認",
					"2" => "未承認",
				),
			),
			"reg_date" => array(
				"title" => "登録日時",
			),
		),
 		"search" => array(	// 検索条件
			"name" => array(
				"title" => "名前・企業名",
				"type" => "text",
				"cond" => "like",
			),
			"nickname" => array(
				"title" => "ニックネーム",
				"type" => "text",
				"cond" => "like",
			),
			"email" => array(
				"title" => "メールアドレス",
				"type" => "text",
				"cond" => "like",
			),
		),
	),
	"input" => array(
		"form" => "contents.html",
		"buttons" => array(
			//
		),
		"items" => array(
/*
			"user_id" => array(
				"title" => "利用者ID",
				"type" => "text",
			),
*/
			"name" => array(
				"title" => "名前・企業名",
				"type" => "text",
			),
			"nickname" => array(
				"title" => "ニックネーム",
				"type" => "text",
			),
			"email" => array(
				"title" => "メールアドレス",
				"type" => "text",
			),
			"passwd" => array(
				"title" => "パスワード",
				"type" => "text",
			),
			"url" => array(
				"title" => "ホームページ",
				"type" => "text",
			),
			"contents" => array(
				"title" => "説明",
				"type" => "textarea",
			),
			"pref" => array(
				"title" => "都道府県",
				"type" => "select",
				"list" => $pref_list,
			),
			"status" => array(
				"title" => "状態",
				"type" => "select",
				"list" => array(
					"1" => "承認",
					"2" => "未承認",
				),
			),
		),
	),
);

if ($act_ary[0] == "user") {
	if ($act_ary[1] == "confirm") {
		$upd["status"] = $_REQUEST["kind"];
		User::updateData($_REQUEST["id"], $upd);
		if ($_REQUEST["kind"] == "1") {	// 承認
			$item = User::getData($_REQUEST["id"]);
			// メール送信
			template_mail($item["email"], "mail2_subject", "mail2_body", array("user" => $item));
		}
		$msg[] = "設定しました";
		$act_ary[1] = "edit";
	}
	if ($act_ary[1] == "edit") {
		$item = User::getData($_REQUEST["id"]);
		if ($item["status"] == "1") {
			$user_data["input"]["buttons"][] = array("name" => "非承認にする", "act" => "?act=user_confirm&kind=2&id=");
		} else {
			$user_data["input"]["buttons"][] = array("name" => "承認する(メール送信)", "act" => "?act=user_confirm&kind=1&id=");
		}
	}
	cms2($user_data, $act_ary[1], new User(), $msg);
}

// --------------------------------
$genre_data = array(
	"name" => "genre",	// 処理名
	"id" => "seq",	// IDフィールド
	"title" => "質問カテゴリ",
	"list" => array(
		"form" => "list.html",
		"order" => array(
			array("field" => "ord"),
		),
		"items" => array(
/*
			"seq" => array(
				"title" => "シーケンス",
			),
*/
			"title" => array(
				"title" => "タイトル",
			),
/*
			"contents" => array(
				"title" => "説明",
			),
*/
			"ord" => array(
				"title" => "順序",
			),
			"status" => array(
				"title" => "状態",
				"values" => array(
					"1" => "公開",
					"2" => "非公開",
				),
			),
			"reg_date" => array(
				"title" => "登録日時",
			),
		),
 		"search" => array(	// 検索条件
		),
	),
	"input" => array(
		"form" => "contents.html",
		"items" => array(
/*
			"seq" => array(
				"title" => "シーケンス",
				"type" => "text",
			),
*/
			"title" => array(
				"title" => "タイトル",
				"type" => "text",
			),
			"contents" => array(
				"title" => "説明",
				"type" => "textarea",
			),
			"ord" => array(
				"title" => "順序",
				"type" => "text",
			),
			"status" => array(
				"title" => "状態",
				"type" => "select",
				"list" => array(
					"1" => "公開",
					"2" => "非公開",
				),
			),
		),
	),
);

if ($act_ary[0] == "genre") {
	cms2($genre_data, $act_ary[1], new Genre());
}

// --------------------------------

$qa_data = array(
	"name" => "qa",	// 処理名
	"id" => "seq",	// IDフィールド
	"title" => "QA集",
	"nonew" => "1",
	"list" => array(
		"form" => "list.html",
		"items" => array(
/*
			"seq" => array(
				"title" => "シーケンス",
			),
*/
			"user_id" => array(
				"title" => "登録者",
/*
				"reference" => array(
					"class" => "user",
					"field" => "nickname",
				),
*/
				"func" => "user_link",
			),
			"category" => array(
				"title" => "カテゴリ",
				"reference" => array(
					"class" => "genre",
					"field" => "title",
				),
			),
			"title" => array(
				"title" => "タイトル",
			),
			"status" => array(
				"title" => "状態",
				"values" => $open_list,
			),
			"reg_date" => array(
				"title" => "登録日時",
			),
		),
 		"search" => array(	// 検索条件
		),
	),
	"input" => array(
		"form" => "contents.html",
		"items" => array(
/*
			"seq" => array(
				"title" => "シーケンス",
				"type" => "text",
			),
*/
			"user_id" => array(
				"title" => "登録者ID",
				"type" => "display",
				"func" => "user_link",
			),
			"name" => array(
				"title" => "氏名",
				"type" => "text",
			),
			"email" => array(
				"title" => "メールアドレス",
				"type" => "text",
			),
			"category" => array(
				"title" => "カテゴリ",
				"type" => "select",
				"reference" => array(
					"class" => "Genre",
					"value" => "seq",
					"name" => "title",
				),
			),
			"title" => array(
				"title" => "タイトル",
				"type" => "text",
			),
			"question" => array(
				"title" => "質問内容",
				"type" => "textarea",
			),
			"status" => array(
				"title" => "状態",
				"type" => "select",
				"list" => $open_list,
			),
		),
	),
);

$answer_data = array(
	"name" => "answer",	// 処理名
	"id" => "seq",	// IDフィールド
	"title" => "回答",
	"list" => array(
		"form" => "list.html",
		"cond" => array(
			array("field" => "qa_id", "value" => "", "cond" => "="),
		),
		"items" => array(
/*
			"seq" => array(
				"title" => "シーケンス",
			),
*/
			"user_id" => array(
				"title" => "回答者",
				"reference" => array(
					"class" => "user",
					"field" => "nickname",
				),
			),
			"answer" => array(
				"title" => "回答",
			),
/*
			"orei" => array(
				"title" => "お礼",
			),
*/
			"reg_date" => array(
				"title" => "登録日時",
			),
		),
 		"search" => array(	// 検索条件
		),
	),
	"input" => array(
		"form" => "contents.html",
		"items" => array(
/*
			"seq" => array(
				"title" => "シーケンス",
				"type" => "text",
			),
*/
			"user_id" => array(
				"title" => "回答者",
				"type" => "text",
			),
			"answer" => array(
				"title" => "回答",
				"type" => "textarea",
			),
			"good" => array(
				"title" => "参考になった",
				"type" => "checkbox",
				"list" => array(
					"1" => "参考になった",
				),
			),
		),
	),
);


if ($act_ary[0] == "answer") {
	//
	cms2($answer_data, $act_ary[1], new Answer(), array(), (($act_ary[1] != "edit")&&($act_ary[1] != "new")));
	//
	unset($data["title"]);
	if ($_REQUEST["seq"]) {
		$_REQUEST["id"] = $_REQUEST["seq"];
	} else {
		$_REQUEST["id"] = $_SESSION["QA_ID"];
	}
	$act_ary[0] = "qa";
	$act_ary[1] = "edit";
}

if ($act_ary[0] == "qa") {
	$_SESSION["QA_ID"] = $_REQUEST["id"];
	if ($act_ary[1] == "edit") {
		$answer_data["list"]["cond"][0]["value"] = $_REQUEST["id"];
		cms2($answer_data, "list", new Answer(), array(), true);
		$html->set("item_list", $data["list"]);
		$html->set("item_pager", $data["pager"]);
		$html->set("item_form", $answer_data);
		unset($data["list"]);
		unset($data["form"]);
	}
	//
	cms2($qa_data, $act_ary[1], new Qa());
}
// --------------------------------

$news_data = array(
	"name" => "news",	// 処理名
	"id" => "seq",	// IDフィールド
	"title" => "お知らせ",
	"list" => array(
		"form" => "list.html",
		"items" => array(
/*
			"seq" => array(
				"title" => "シーケンス",
			),
*/
			"news_date" => array(
				"title" => "お知らせ日付",
			),
			"title" => array(
				"title" => "タイトル",
			),
/*
			"contents" => array(
				"title" => "問合せ内容",
			),
			"reg_date" => array(
				"title" => "登録日時",
			),
*/
		),
 		"search" => array(	// 検索条件
		),
	),
	"input" => array(
		"form" => "contents.html",
		"items" => array(
/*
			"seq" => array(
				"title" => "シーケンス",
				"type" => "text",
			),
*/
			"news_date" => array(
				"title" => "お知らせ日付",
				"type" => "date_sel",
			),
			"title" => array(
				"title" => "タイトル",
				"type" => "text",
			),
/*
			"contents" => array(
				"title" => "お知らせ内容",
				"type" => "textarea",
			),
*/
		),
	),
);

if ($act_ary[0] == "news") {
	cms2($news_data, $act_ary[1], new News());
}

// --------------------------------
// 設定
$setup_data = array(
	"id" => INFO_SETUP,
	"name" => "setup",
	"title" => "基本設定",
/*
	"list" => array(
		"form" => "list.html",
		"items" => array(
			"title" => array(
				"title" => "タイトル",
			),
		),
	),
*/
	"input" => array(
		"form" => "contents.html",
		"items" => array(
			"dummy1" => array(
				"title" => "管理者",
				"type" => "comment",
			),
			"login_id" => array(
				"title" => "ログインID",
				"type" => "text",
			),
			"passwd" => array(
				"title" => "パスワード",
				"type" => "text",
			),
		),
	),
);

if ($act == "setup") {
	unset($cond);
	$cond["kind"] = INFO_SETUP;
	$ret = Info::findData($cond);
	if (!$ret) {
		cms2($setup_data, "new", null, array());
	} else {
		$id = $ret[0]["info_id"];
		$_REQUEST["id"] = $id;
		cms2($setup_data, "edit", null, array());
	}
	$act = "setup";
}
if ($act == "setup_update") {
	cms2($setup_data, "update", null, array());
	$act = "setup";
}
if ($act == "setup") {
	$html->view("top.html");
	exit;
}

// ---------------------------------
$mail_data = array(
	"id" => INFO_MAIL,
	"name" => "mail",
	"title" => "メール設定",
/*
	"list" => array(
		"form" => "list.html",
		"items" => array(
			"title" => array(
				"title" => "タイトル",
			),
		),
	),
*/
	"input" => array(
		"form" => "contents.html",
		"items" => array(
			"dummy1" => array(
				"title" => "管理者情報",
				"type" => "comment",
			),
			"fromname" => array(
				"title" => "メール発信者",
				"type" => "text",
			),
			"email" => array(
				"title" => "送信元メールアドレス",
				"type" => "text",
			),
			"manager" => array(
				"title" => "管理者メールアドレス",
				"type" => "text",
			),
			"sign" => array(
				"title" => "シグニチャー",
				"type" => "textarea",
			),
			"dummy2" => array(
				"title" => "メール内容情報",
				"type" => "comment",
			),
			"mail1_subject" => array(
				"title" => "パスワードの再送依頼件名",
				"type" => "text",
			),
			"mail1_body" => array(
				"title" => "パスワードの再送依頼内容",
				"type" => "textarea",
			),
			"mail2_subject" => array(
				"title" => "会員登録完了件名",
				"type" => "text",
			),
			"mail2_body" => array(
				"title" => "会員登録完了内容",
				"type" => "textarea",
			),
			"mail3_subject" => array(
				"title" => "お問合せ件名",
				"type" => "text",
			),
			"mail3_body" => array(
				"title" => "お問合せ内容",
				"type" => "textarea",
			),
		),
	),
);


if ($act == "mail") {
	unset($cond);
	$cond["kind"] = INFO_MAIL;
	$ret = Info::findData($cond);
	if (!$ret) {
		cms2($mail_data, "new", null, array());
	} else {
		$id = $ret[0]["info_id"];
		$_REQUEST["id"] = $id;
		cms2($mail_data, "edit", null, array());
	}
}

if ($act == "mail_update") {
	cms2($mail_data, "update", null, array());
}

// ---------------------------------
// トップメニュー画面
$html->display("top.html");
exit;

// ---------------------------------
// ログインしていなければ、ログインフォームを表示し、ログイン後もとの処理へ
function login_check($msg)
{
	// ログイン確認
	if (!isset($_SESSION["ADMIN_LOGIN"])) {
		// ログイン後の飛び先（戻る場所）
		$_SESSION['NEXT_URL'] = TOP . './?act=logined';
		// 現在のパラメータを保存
		$_SESSION['REQUEST'] = $_REQUEST;
		$_SESSION['LOGIN_MESSAGE'] = $msg;
		// ログイン処理へ
		header("Location: ./?act=login");
		exit;
	}
}

function safeStripSlashes($var)
{
	if (is_array($var)) {
		return array_map('safeStripSlashes', $var);
	} else {
		if (get_magic_quotes_gpc()) {
			$var = stripslashes($var);
		}
		return $var;
	}
}
/*
// デバッグダンプ
function dump($data) {
	echo "<pre>";
	var_dump($data);
	echo "</pre>";
}
*/
?>
