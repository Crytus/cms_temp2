<?php
// -----------------------------------
// 共通関数
// Crytus.co.jp
//
// 更新記録
// 2014/07/07 テンプレートメールの送信元の指定を可能にした
// -----------------------------------

require_once("smarty/Smarty.class.php");
require_once("mail.php");

// カンマ要素をINDEXとした配列にする
function multi_value($val)
{
	$ret = array();
	if (is_array($val)) {
		$ary = $val;
	} else {
		$ary = explode(",", $val);
	}
	if ($ary) {
		foreach ($ary as $v) {
			$ret[$v] = "1";
		}
	}
	return $ret;
}
// 配列の要素に対応する文字列を返す
function multi_string($val, $list, $sep="")
{
	$ret = array();
	if (is_array($val)) {
		$ary = $val;
	} else {
		$ary = explode(",", $val);
	}
	if ($ary) {
		foreach ($ary as $v) {
			$ret[] = $list[$v];
		}
	}
	if ($sep && $ret) {
		return join($sep, $ret);
	}
	return $ret;
}

function get_const_list($kind=1)
{
	$const_list = array();
	$db = new ConstData();
	$db->search[] = array("field" => "kind", "value" => $kind, "cond" => "=");
	$db->order[] = array("field" => "no");
	$list = $db->getList();
	if ($list) {
		foreach ($list as $val) {
			$const_list[$val["no"]] = $val["name"];
		}
	}
//dump($const_list);
	return $const_list;
}
// カテゴリの文字への変換
function disp_category($item, $sep="")
{
	if (is_array($item)) {
		$cate = array();
		foreach ($item as $val) {
			$cate[] = disp_category($val);
		}
	} else {
		// 単一
		$cate = Category::getData($item);
		return $cate["title"];
	}
	if ($sep && $cate) {
		return join($sep, $cate);
	}
	return $cate;
}
function movie_resize($movie, $sx="400", $sy="325")
{
	if (eregi("width=&quot;([0-9]+)&quot;", $movie, $matches)) {
		$x = $matches[1];
		$str_x = $matches[0];
	} else if (eregi("width=\"([0-9]+)\"", $movie, $matches)) {
		$x = $matches[1];
		$str_x = $matches[0];
	}
	if (eregi("height=&quot;([0-9]+)&quot;", $movie, $matches)) {
		$y = $matches[1];
		$str_y = $matches[0];
	} else if (eregi("height=\"([0-9]+)\"", $movie, $matches)) {
		$y = $matches[1];
		$str_y = $matches[0];
	}
	if ($x && $y) {
		if ($x > $sx) {
			$sy = intval($y * $sx / $x);
		} else if ($y > $sy) {
			$sx = intval($x * $sy / $y);
		} else {
			$sx = $x;
			$sy = $y;
		}
	}
	$movie = str_replace($str_x, "width=&quot;" . $sx . "&quot;", $movie);
	$movie = str_replace($str_y, "height=&quot;" . $sy . "&quot;", $movie);
	return $movie;
}
/* SCRIPT_ENCODINGがSJISの場合に有効にする
// Smarty SJIS対応用関数1
function m_convert_encoding_to_eucjp($template_source) {
    if (function_exists("mb_convert_encoding")) {
        //文字コードを変換する
        return mb_convert_encoding($template_source, "EUC-JP", "SJIS");
    }
    return $template_source;
}

// Smarty SJIS対応用関数2
function m_convert_encoding_to_sjis($template_source) {
    if (function_exists("mb_convert_encoding")) {
        //文字コードを変換する
        return mb_convert_encoding($template_source, "SJIS", "EUC-JP");
    }
    return $template_source;
}
*/
// テンプレートを使用してメールを送信する
function template_mail($to, $subject_tpl, $body_tpl, $info, $manager="", $reply="", $reply_name="")
{
	$smarty = new Smarty();
// SCRIPT_ENCODINGがSJISの場合に有効にする
//	$smarty->register_prefilter("m_convert_encoding_to_eucjp");
//	$smarty->register_postfilter("m_convert_encoding_to_sjis");
	// メール用リソース設定
	$smarty->register_resource('mail', 
		array(
			"mail_get_template",
			"mail_get_timestamp",
			"mail_get_secure",
			"mail_get_trusted",
		)
	);
	//
	$smarty->assign("sitename", SITE_NAME);
	$smarty->assign("url", TOP_URL);
	$smarty->assign("loginurl", TOP_URL . "login.html");
	//
	if (is_array($info)) {
		foreach ($info as $key => $val) {
			$smarty->assign($key, $val);
		}
	} else {
		$smarty->assign("info", $info);
	}
	$mail = get_info(INFO_MAIL);
//	$subject = "【" . $mail["fromname"] . "】 " . $mail[$subject_tpl];
	$subject = "【" . $mail["fromname"] . "】 " . $smarty->fetch("mail:" . $subject_tpl);
	$body = $smarty->fetch("mail:" . $body_tpl) . "\n" . $mail["sign"];
	if (!$reply) {
		$reply = $mail["email"];
		$reply_name = $mail["fromname"];
	}
	if ($to) {
		$ret = sendmail($mail["email"], $to, $subject, $body, array(), $reply, $reply_name);
	}
	if ($manager && $mail[$manager]) {
		$ret = sendmail($mail["email"], $mail[$manager], $subject, $body, array(), $reply, $reply_name);
	}
	return $ret;
}

// メール用テンプレート取得処理(データベースより)
function mail_get_template($name, &$source, $smarty)
{
	$mail = get_info(INFO_MAIL);
	if ($mail[$name]) {
		$source = $mail[$name];
	} else {
		$source = "該当リソースなし";
	}
	return true;
}

function mail_get_timestamp($name, &$timestamp, $smarty)
{
	$timestamp = time();
	return true;
}

function mail_get_secure($name, $smarty)
{
	return true;
}

function mail_get_trusted($name, $smarty)
{
	//
}

/*
 * ページインデックス作成
 * $cur : 現在のページ（0始まり）
 * $pages : 全ページ数
 */
function page_index($cur, $pages)
{
	$page = array();
	$no = 0;
	if ($cur > 0) {
		$page['prev'] = array('no' => $cur - 1, 'name' => '前へ', 'link' => 1);
	}
	for ($i = 0; $i < $pages; $i++) {
		if ($i == $cur) {
			$page['list'][$no] = array('no' => $i, 'name' => $i + 1);
		} else {
			$page['list'][$no] = array('no' => $i, 'name' => $i + 1, 'link' => 1);
		}
		$no++;
	}
	if ($cur < ($pages - 1)) {
		$page['next'] = array('no' => $cur + 1, 'name' => '次へ', 'link' => 1);
	}
	return $page;
}
/*
 * ページインデックス作成(ページ切り替えを10までに制限)
 * $cur : 現在のページ（0始まり）
 * $pages : 全ページ数
 * $maxpage ： ページリストの最大数
 */
function page_index2($cur, $pages, $maxpage=10)
{
	$page = array();
	$no = 0;
	if ($cur > 0) {
		$page['prev'] = array('no' => $cur - 1, 'name' => '前へ', 'link' => 1);
	}
	$start = 0;
	$end = $pages;
	if ($pages > $maxpage) {
		if ($cur > intval($maxpage / 2)) {
			$start = $cur - intval($maxpage / 2);
		}
		$end = $start + $maxpage;
		if ($end > $pages) {
			$end = $pages;
			$start = $end - $maxpage;
		}
		if ($start > 1) {
			$page['prev_skip'] = "1";
			$page['top'] = array('no' => 0, 'name' => '1', 'link' => 1);
		}
		if ($end < $pages) {
			$page['next_skip'] = "1";
			$page['last'] = array('no' => $pages - 1, 'name' => $pages, 'link' => 1);
		}
	}
	for ($i = $start; $i < $end; $i++) {
		if ($i == $cur) {
			$page['list'][$no] = array('no' => $i, 'name' => $i + 1);
		} else {
			$page['list'][$no] = array('no' => $i, 'name' => $i + 1, 'link' => 1);
		}
		$no++;
	}
	if ($cur < ($pages - 1)) {
		$page['next'] = array('no' => $cur + 1, 'name' => '次へ', 'link' => 1);
	}
	return $page;
}

// デバッグダンプ
function dump($data) {
	echo "<pre>";
	var_dump($data);
	echo "</pre>";
}
?>
