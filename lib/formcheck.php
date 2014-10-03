<?php
require_once("image.php");
// 入力データ汎用チェック

// 文字種コード
// A 半角英字
// N 半角数字
// K 半角カナ
// G 半角記号
// J 漢字
// * 全文字種

Class FormCheck {
	// 配列データから入力定義に登録された項目を得る
	function get_form($table, $input)
	{
		global $image_type;

		$data = array();
		foreach ($table as $item) {
			$name = $item[0];
			if ($item[1] == "FILE") {
				$data[$name] = $_FILES[$name];
				continue;
			}
			if ($item[1] == "IMAGE") {
				$img = form_image($name, $msg, $image_type, IMAGE_MAX);
				if ($img) {
					$data[$name] = $img;
				}
				continue;
			}
		//	if ($item[5]) {		// <>の使用可不可
		//		$tag = 1;
		//	} else {
				$tag = 0;
		//	}
			if (isset($input[$name])) {
				if (is_array($input[$name])) {
					$cnt = 0;
					foreach ($input[$name] as $val) {
						if ($tag) {
							$data[$name][$cnt] = $val;
						} else {
							$data[$name][$cnt] = htmlspecialchars($val);
						}
						$cnt++;
					}
				} else {
					if ($tag) {
						$data[$name] = $input[$name];
					} else {
						$data[$name] = htmlspecialchars($input[$name]);
					}
				}
			}
			//print "$key -> $val\n";
		}
		return $data;
	}
	// 文字種チェック
	function moji($data, $moji) {
		if ($moji != "*") {
			for ($i = 0; $i < mb_strlen($data, SCRIPT_ENCODING); $i++) {
				$ch = mb_substr($data, $i, 1, SCRIPT_ENCODING);
			//	if (mb_strwidth($ch, SCRIPT_ENCODING) == 2) {	// 全角
				if (strlen($ch) == 2) {	// 全角
					if ($moji == 'JK') {
						if (mb_ereg("[^ァ-ンー－―‐]", $ch)) {
							return true;
						}
					}
					if (ereg('J', $moji)) {
						//
					} else {
						return true;
					}
//				} else {	// 半角
				} else if ($ch >= ' ') {
					if (!preg_match("/[ANKG]+/", $moji, $m1)) {
						return true;
					}
					if ((($ch >= '0')&&($ch <= '9'))||($ch == '-')||($ch == '.')) {
						if (!preg_match("/[N]+/", $moji, $m1)) {
							return true;
						}
					} else if ((($ch >= "A")&&($ch <= 'Z'))||(($ch >= "a")&&($ch <= 'z'))||($ch == '_')) {
						if (!preg_match("/[A]+/", $moji, $m1)) {
							return true;
						}
					} else {
						if (!preg_match("/[KG]+/", $moji, $m1)) {
							return true;
						}
					}
				} else {	// 特殊文字（改行など）
					if (!ereg('S', $moji)) {
						return true;
					}
				}
			}
		}
		return false;	// OK
	}
	// メールアドレスチェック
	function mail($input) {
		if (FormCheck::moji($input, "ANG")) {	// 半角英数チェック
			return true;
		}
		$ary = explode("@", $input);
		if (count($ary) != 2) {
			return true;
		}
		return false;	// OK
	}
	// 電話番号チェック
	function tel($data) {
		// 数字のみ
		if (preg_match("/^(\d+)$/", $data, $m1)) {
			if ($data == $m1[0]) {
				return false;	// OK
			}
		}
		// 数字と-
		if (preg_match("/^(\d+)\-(\d+)\-(\d+)$/", $data, $m1)) {
			return false;	// OK
		}
		return true;	// NG
	}
	// 〒番号チェック
	function zip($data) {
		// 数字のみ
		if (preg_match("/^(\d+)$/", $data, $m1)) {
			if ($data == $m1[0]) {
				return false;	// OK
			}
		}
		// 数字と-
		if (preg_match("/^(\d+)\-(\d+)$/", $data, $m1)) {
			if ((strlen($m1[1]) == 3) && (strlen($m1[2]) == 4)) {
				return false;	// OK
			}
		}
		return true;	// NG
	}
	function url($data) {
		if (ereg("(http|https)://([a-zA-Z0-9\._\-]+)", $data)) {
			return false;
		}
		return true;	// NG
	}
	// チェック内容（必須、長さ、文字種）
	function check($input, $table) {
		$msg = array();
		foreach ($table as $item) {
			$name = $item[0];
			if (($item[1] == "FILE")||($item[1] == "IMAGE")) {
				continue;
			}
			if (is_array($input[$name])) {
				// 配列の場合
			} else {
				$len = 0;
				$val = "";
				if (isset($input[$name])) {
//					$val = mb_convert_encoding($input[$name], mb_internal_encoding(), auto);
					$val = $input[$name];
					$len = mb_strlen($val, SCRIPT_ENCODING);
				}
				if ($item[3] && ($len == 0)) {	// 必須チェック
				//	array_push($msg, $item[4] . "が入力されていません。");
					$msg[$name] = $item[4] . "が入力されていません。";
				}
				if ($len && $item[2]) {	// 桁数チェック
					if ($len > $item[2]) {
					//	array_push($msg, $item[4] . "の文字数が制限を越えています。");
						$msg[$name] =  $item[4] . "の文字数が制限を越えています。";
					}
				}
				if ($len) {
					if ($item[1] == "TEL") {
						if (FormCheck::tel($val)) {
						//	array_push($msg, $item[4] . "の書式が正しくありません。");
							$msg[$name] = $item[4] . "の書式が正しくありません。";
						}
					} else if ($item[1] == "ZIP") {
						if (FormCheck::zip($val)) {
						//	array_push($msg, $item[4] . "の書式が正しくありません。");
							$msg[$name] = $item[4] . "の書式が正しくありません。";
						}
					} else if ($item[1] == "MAIL") {
						if (FormCheck::mail($val)) {
						//	array_push($msg, $item[4] . "の書式が正しくありません。");
							$msg[$name] = $item[4] . "の書式が正しくありません。";
						}
					} else if ($item[1] == "URL") {
						if (FormCheck::url($val)) {
						//	array_push($msg, $item[4] . "の書式が正しくありません。");
							$msg[$name] = $item[4] . "の書式が正しくありません。";
						}
					} else if ($item[1]) {
						if (FormCheck::moji($val, $item[1])) {
						//	array_push($msg, $item[4] . "に使用できない文字種が含まれています。");
							$msg[$name] = $item[4] . "に使用できない文字種が含まれています。";
						}
					}
				}
			}
		}
		return $msg;
	}
}

// データチェック用テーブル
//$chk_table = array (
	// フォーム項目名 文字種 最大文字数 必須 表示名称
//	array("name", "K", 20, true, "名前"),
//	array("address1", "ANK", 10, true, "住所１"),
//	array("tel", "TEL", 15, true, "電話"),
//);

// テスト用データ
//$form = array (
//	"name" => "栗原　好昭",
//	"address1" => "東京都あきる野市草花1092-12");

// チェック実行
//$msg = FormCheck::check($form, $chk_table);
//
//print implode("<br>\n", $msg) . "\n";

?>
