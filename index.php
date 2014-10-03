<?php

// クラス
require_once("common.php");
require_once("formcheck.php");

// クラスの生成
$ap = new Application();

// -----------------------------------
// ログイン
if ($ap->act == "login") {
	if ($_REQUEST["mode"] == "form") {
		if ($_REQUEST["email"] && $_REQUEST["passwd"]) {
			$cond["email"] = $_REQUEST["email"];
			$cond["passwd"] = $_REQUEST["passwd"];
			$cond["status"] = "1";	// 公開
			$ret = User::findData($cond);
			if ($ret) {
				$_SESSION["LOGIN"] = $ret[0];
				if ($_REQUEST["autologin"]) {
					// ログイン情報のCookieへの保存
					setLoginCookie($_SESSION["LOGIN"]["email"], $_SESSION["LOGIN"]["user_id"], LOGIN_COOKIE);
				}
				if ($_SESSION['NEXT_URL']) {
					header("location: " . $_SESSION['NEXT_URL']);
					unset($_SESSION['NEXT_URL']);
				} else {
					header("location: ./edit.html");
				}
				exit;
			}
			$msg[] = "「メールアドレス」または「パスワード」が違います！";
		} else {
			if (!$_REQUEST["email"]) {
				$msg[] = "「メールアドレス」欄が未入力になっています。 ";
			}
			if (!$_REQUEST["passwd"]) {
				$msg[] = "「パスワード」欄が未入力になっています。 ";
			}
		}
		$form["email"] = $_REQUEST["email"];
		$form["passwd"] = $_REQUEST["passwd"];
		$ap->set("msg", $msg);
		$ap->set("form", $form);
	}
	unset($_SESSION["LOGIN"]);
}
if ($ap->act == "password") {
	if ($_REQUEST["mode"]) {
		if ($_REQUEST["email"]) {
			$cond["email"] = $_REQUEST["email"];
			$ret = User::findData($cond);
			if ($ret) {
				// メール送信
				template_mail($_REQUEST["email"], "mail1_subject", "mail1_body", array("passwd" => $ret[0]["passwd"], "name" => $ret[0]["name"]));
				$msg[] = "パスワードをメールで送信しました。";
				$ap->template = "login.html";
			} else {
				$msg[] = "該当するメールアドレスがありません。 ";
			}
		} else {
			$msg[] = "「メールアドレス」欄が未入力になっています。 ";
		}
	}
	$ap->set("msg", $msg);
	$ap->set("form", $form);
}
// -----------------------------------
// 利用者登録処理
if ($ap->act == "regist") {

$user_check = array(
	// フォーム項目名 文字種 最大文字数 必須 表示名称
	array("mode", "*", "", false, "モード"),
	array("name", "*", "40", true, "お名前"),
	array("nickname", "*", "20", true, "ニックネーム"),
	array("email", "MAIL", "80", true, "メールアドレス"),
	array("passwd", "*", "20", true, "パスワード"),
	array("url", "*", "100", false, "サイトURL"),
	array("contents", "*", "100", false, "サイト説明文"),
	array("category1", "*", "", false, "登録カテゴリ1"),
	array("category2", "*", "", false, "カテゴリ2"),
	array("template", "*", "", false, "利用テンプレート"),
	array("pref", "*", "", false, "住所"),
	array("open", "*", "", true, "公開"),
	array("code", "*", "", false, "事業者コード"),
);

	if ($_REQUEST["mode"]) {
		$form = FormCheck::get_form($user_check, $_REQUEST);
		$msg = FormCheck::check($form, $user_check);
		$img = form_image("image", $msg, $img_type, IMAGE_MAX);
		if ($img) {
			$form["image"] = $img;
		} else {
		//	$msg["image"] = "画像が指定されていません。";
		}
		if ($form["email"]) {
			$cond["email"] = $form["email"];
			$ret = User::findData($cond);
			if ($ret) {
				foreach ($ret as $val) {
					if ($_SESSION["LOGIN"]) {
						if ($val["user_id"] != $_SESSION["LOGIN"]["user_id"]) {
							$msg["email"] = "このメールアドレスは利用できません。";
						}
					} else {
						$msg["email"] = "このメールアドレスは利用できません。";
					}
				}
			}
		}
		if ($form["nickname"]) {
			unset($cond);
			$cond["nickname"] = $form["nickname"];
			$ret = User::findData($cond);
			if ($ret) {
				foreach ($ret as $val) {
					if ($_SESSION["LOGIN"]) {
						if ($val["user_id"] != $_SESSION["LOGIN"]["user_id"]) {
							$msg["nickname"] = "このニックネームは利用できません。";
						}
					} else {
						$msg["nickname"] = "このニックネームは利用できません。";
					}
				}
			}
		}
		if ($msg) {
			foreach ($msg as $key => $v) {
				$data["error"][$key] = $v;
			}
			$ap->set("msg", $msg);
		} else {
			$_SESSION["form"] = $form;
			$ap->template = "form-confirm.html";
		}
	} else {
		unset($_SESSION["form"]);
		if ($_SESSION["LOGIN"]) {
			$form = $_SESSION["LOGIN"];
		} else {
			// デフォルト
			$form["url"] = "http://";
		}
	}
	$ap->set("form", $form);
}
if ($ap->act == "form-reinput") {
	// 再入力
	$ap->template = "form.html";
	$form = $_SESSION["form"];
	unset($_SESSION["form"]);
	$ap->set("form", $form);
}
if ($ap->act == "form-thanks") {
	if ($_REQUEST["reinput_x"]) {
		$form =	$_SESSION["form"];
		unset($_SESSION["form"]);
		$ap->set("form", $form);
		$ap->template = "contact.html";
	} else if ($_SESSION["form"]) {
	// 保存
		$form = $_SESSION["form"];
		unset($_SESSION["form"]);
		// 保存
		$item['name'] = $form["name"];
		$item["nickname"] = $form["nickname"];
		$item["email"] = $form["email"];
		$item["passwd"] = $form["passwd"];
		if ($form["url"] != "http://") {
			$item["url"] = $form["url"];
		} else {
			$item["url"] = array();
		}
		$item["contents"] = $form["contents"];
		$item["category1"] = $form["category1"];
		$item["category2"] = $form["category2"];
		$item["template"] = $form["template"];
		$item["pref"] = $form["pref"];
		$item["code"] = $form["code"];
		$item["open"] = $form["open"];
		$item['up_date'] = "now()";
		if ($_SESSION["LOGIN"]) {
			$id = User::updateData($_SESSION["LOGIN"]["user_id"], $item);
			$user = User::getData($_SESSION["LOGIN"]["user_id"]);
			$_SESSION["LOGIN"] = $user;
		} else {
			$item['status'] = "2";	// 非公開
			$id = User::addData($item);
		}
		// メール送信
		if ($form["pref"]) {
			$form["pref"] = $pref_list[$form["pref"]];
		}
//		template_mail($form["email"], "mail2_subject", "mail2_body", array("form" => $form, "name" => $form["name"]), "manager");
	}
}
// -----------------------------------
// 検索
if ($ap->act == "search") {
	$page = $_REQUEST["page"];
	$keyword = $_REQUEST["keyword"];
	//
/*
	$db = new Qa();
	if ($keyword) {
		$where_keyword[] = array("field" => "title", "value" => "%{$keyword}%", "cond" => "like", "relation" => "or");
		$where_keyword[] = array("field" => "question", "value" => "%{$keyword}%", "cond" => "like", "relation" => "or");
		$db->search[] = array("nest" => $where_keyword);
		$form["keyword"] = $keyword;
	}
	$db->order[] = array("field" => "reg_date", "desc" => "1");
	$db->page = $page;
	$db->limit = PAGE_ITEMS;
	$count = $db->getCount();
	$pages = intval(($count + $db->limit - 1) / $db->limit);
	if ($pages > 1) {
		$ap->set("pager", page_index($page, $pages));
	}
	$list = $db->getList();
	$ap->set(array("total" => $count, "start" => $page * $db->limit + 1, "end" => $page * $db->limit + count($list)));
	if ($list) {
		$ap->set("list", $list);
	}
	$ap->set("id", $id);
*/
	$ap->set("form", $form);
}
// -----------------------------------
// お知らせ一覧
if ($ap->act == "news") {
	$page = $_REQUEST["page"];
	//
	$db = new News();
	$db->order[] = array("field" => "news_date", "desc" => "1");
	$db->page = $page;
	$db->limit = PAGE_ITEMS;
	$count = $db->getCount();
	$pages = intval(($count + $db->limit - 1) / $db->limit);
	if ($pages > 1) {
		$ap->set("pager", page_index($page, $pages));
	}
	$list = $db->getList();
	$ap->set(array("total" => $count, "start" => $page * $db->limit + 1, "end" => $page * $db->limit + count($list)));
	if ($list) {

	}
	//
	$ap->set("list", $list);
}
// お知らせ詳細
if ($ap->act == "news-detail") {
	// トップページの情報を得る
	$news = News::getData($_REQUEST["id"]);
	$ap->set("item", $news);
}
// -----------------------------------
// 問合せ
if ($ap->act == "contact") {

$contact_check = array(
	// フォーム項目名 文字種 最大文字数 必須 表示名称
	array("mode", "*", "", false, "モード"),
	array("message", "*", "", true, "メッセージ欄"),
);
	if ($_REQUEST["mode"]) {
		$form = FormCheck::get_form($contact_check, $_REQUEST);
		$msg = FormCheck::check($form, $contact_check);
		if ($msg) {
			foreach ($msg as $key => $v) {
				$data["error"][$key] = $v;
			}
			$ap->set("msg", $msg);
		} else {
			$_SESSION["form"] = $form;
			$ap->template = "contact-confirm.html";
		}
	} else {
		//
	}
	$ap->set("form", $form);
}
if ($ap->act == "contact-reinput") {
	$form =	$_SESSION["form"];
	unset($_SESSION["form"]);
	$ap->set("form", $form);
	$ap->template = "contact.html";
}
if ($ap->act == "contact-thanks") {
	if ($_REQUEST["reinput_x"]) {
		$form =	$_SESSION["form"];
		unset($_SESSION["form"]);
		$ap->set("form", $form);
		$ap->template = "contact.html";
	} else {
		$form =	$_SESSION["form"];
		if ($_SESSION) {
			unset($_SESSION["form"]);
			$mail = get_info(INFO_MAIL);
			// メール送信
			template_mail($mail["email"], "mail4_subject", "mail4_body", array("top" => TOP_URL, "form" => $form, "user" => $_SESSION["LOGIN"]));
		}
	}
}
// -----------------------------------
// トップページ・スタティックページ
$ap->fix_template();

if ($ap->template == 'index.html') {
/*
	// 新着お知らせ5件
	$db = new News();
	$db->order[] = array("field" => "reg_date", "desc" => "1");
	$db->page = 0;
	$db->limit = 10;
	$list = $db->getList();
	$ap->set("news", $list);
	// カテゴリ別質問一覧5件
	foreach ($ap->genre as $key => $val) {
		$db = new Qa();
		$db->search[] = array("field" => "category", "value" => $val["seq"], "cond" => "=");
		$db->order[] = array("field" => "reg_date", "desc" => "1");
		$db->page = 0;
		$db->limit = 5;
		$list = $db->getList();
		$ap->genre[$key]["qa"] = $list;
	}
	$ap->set("qa", $ap->genre);
*/
}
// 画面出力
$ap->view();

// -----------------------------------
function login_check($msg="")
{
	if ($_SESSION["LOGIN"]) {
		return;
	}
	// ログイン後の飛び先（戻る場所）
	$_SESSION['NEXT_URL'] = TOP . '?act=logined';
	// 現在のパラメータを保存
	$_SESSION['REQUEST'] = $_REQUEST;
	//
	$_SESSION["login_message"] = $msg;
	header("location: login.html");
	exit;
}
?>
