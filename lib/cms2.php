<?php
/*
 * 汎用CMS
 *
 * Copyright (C) 2009 by Crytus corporation all rights reserved.
 * 2014/06/17 一覧の順序指定
 */

require_once("formcheck.php");

if (!defined("ADMIN_MAX_PAGE")) {
	// 一覧の1ページ表示数
	if (defined("MAX_PAGE")) {
		define("ADMIN_MAX_PAGE", MAX_PAGE);
	} else {
		define("ADMIN_MAX_PAGE", 20);
	}
}

if (!defined("YEARS_SEL")) {
	define("YEARS_SEL", 5);
}

if (!defined("MAX_FILE_SIZE")) {
	define("MAX_FILE_SIZE", (500 * 1024));
}

// --------------------------------
// 専用のデータ構造を持つデータの処理
function cms2($form, $act="list", $class=null, $msg=array(), $pass=false)
{
	global $html;
	global $data;

//	$msg = array();
	if (!$act) {
		$act = "list";
	}
	if ($act == "delete") {
		$id = $_REQUEST["id"];
		if ($id) {
			if ($class) {
				$item = $class->getData($id);
				if ($item) {
					if ($form["pre_delete"]) {		// 削除前チェック
						// 削除できない場合は、メッセージを返す
						$ret = call_user_func($form["pre_delete"], $id, $item);
						if ($ret) {
							$msg[] = $ret;
						}
					}
				} else {
					// 存在しない
					$msg[] = "無効なデータです";
				}
				if (!$msg) {
					// 画像を削除
					foreach ($form["input"]["items"] as $key => $val) {
						if ($val["type"] == "image") {
							if ($item[$key]) {
								delete_image($item[$key], $id);
							}
						}
					}
					$class->deleteData($id);
				}
			} else {
				$info = Info::getData($id);
				$msg = "";
				if ($info) {
					if ($form["pre_delete"]) {		// 削除前チェック
						$msg = call_user_func($form["pre_delete"], $id);	// 削除できない場合は、メッセージを返す
					}
				} else {
					$msg = "無効なデータです";
				}
				if ($msg == "") {
					// 画像を削除
					$item = get_info_item($id);
					foreach ($form["input"]["items"] as $key => $val) {
						if ($val["type"] == "image") {
							if ($item[$key]) {
								delete_image($item[$key], $id);
							}
						}
					}
					// データを削除
					save_info_item($id, array());
					// 情報を削除
					Info::deleteData($id);
				}
			}
			//
			if ($form["post_delete"]) {		// 関連データの削除
				$val = call_user_func($form["post_delete"], $id, $item);
			}
			if (!$msg) {
				$msg[] = "削除しました";
			}
		} else {
			$msg[] = "無効なデータです";
		}
		if ($form["list"])  {
			$act = "list";
		}
	}
	if ($act == "new") {
		foreach ($form["input"]["items"] as $key => $val) {
			unset($item);
			$item = $val;
			$item["name"] = $key;
			$item[$val["type"]] = "1";
			if ($val["value"]) {
				$item["value"] = $val["value"];	// 初期値（設定）
			}
			if ($_REQUEST[$key]) {
				$item["value"] = $_REQUEST[$key];	// 初期値（外部）
			}
			// 他テーブル参照表示
			if ($val["type"] == "reference") {
				$item["reference_value"] = reference($item["value"], $val["reference"]);
			}
			if ($val["type"] == "checkbox") {
				if ($val["list"]) {		// 複数
					unset($sel);
					foreach ($val["list"] as $key2 => $val2) {
						$key_name = $key . "_" . $key2;
						unset($opt);
						$opt["key"] = $key_name;
						$opt["value"] = $key2;
						$opt["name"] = $val2;
						$sel[] = $opt;
					}
					$item["list"] = $sel;
				} else {	// 単一
					if ($val["value"]) {
						$item["checked"] = "checked";
					}
					$item["value"] = $val["value"];
				}
			} else if (($val["type"] == "date_sel")||($val["type"] == "yearmonth_sel")||($val["type"] == "datetime_sel")) {
				if ($_REQUEST[$key]) {
					$year = substr($_REQUEST[$key], 0, 4);
					$item["month" . substr($_REQUEST[$key], 5, 2)] = "selected";
					if ($val["type"] == "date_sel") {
						$item["day" . substr($_REQUEST[$key], 8, 2)] = "selected";
					}
				}
				// 年の選択範囲の設定
				if ($val["year_s"] || $val["year_e"]) {
					if (!$val["year_s"]) {
						$val["year_s"] = date('Y');
					}
					if (!$val["year_e"]) {
						$val["year_e"] = date('Y');
					}
					for ($y = $val["year_s"]; $y <= $val["year_e"]; $y++) {
						if ($y == $year) {
							$item["years"][] = array("value" => $y, "sel" => "selected");
						} else {
							$item["years"][] = array("value" => $y);
						}
					}
				} else {
					for ($y = date('Y'); $y <= date('Y') + YEARS_SEL; $y++) {
						if ($y == $year) {
							$item["years"][] = array("value" => $y, "sel" => "selected");
						} else {
							$item["years"][] = array("value" => $y);
						}
					}
				}
			} else if ($val["list"]) {
				// 選択肢
				$sel = array();
				foreach ($val["list"] as $key2 => $val2) {
					unset($opt);
					$opt["value"] = $key2;
					$opt["name"] = $val2;
					if ($key2 == $val["value"]) {	// 初期値
						if ($val["type"] == "select") {
							$opt["sel"] = "selected";
						} else {
							$opt["sel"] = "checked";
						}
					}
					$sel[] = $opt;
				}
				$item["list"] = $sel;
			}
			// 選択・他テーブル参照
			if (($val["type"] == "select")&& $val["reference"]) {
				$item["list"] = reference_list($item["value"], $val["reference"]);
			}
			if (($val["type"] == "checkbox")&& $val["reference"]) {
				$item["list"] = reference_list($item["value"], $val["reference"]);
			}
			$list[] = $item;
		}
		$html->set("list", $list);
		$html->set("page_title", $form["title"] . "追加");
		$html->set("form", $form);
		$html->set("mode", "new");
		if ($form["list"])  {
			$html->set("list_menu", "1");
		}
		if ($form["input"]["script"]) {
			$html->set("script", $form["input"]["script"]);
		}
		if (!$pass) {
			$html->view($form["input"]["form"]);
			exit;
		}
	}
	if ($act == "update") {
		$mode = $_REQUEST["mode"];
		$id = $_REQUEST["id"];
		// コンテンツを保存
		unset($item);
		// エラーチェック
		foreach ($form["input"]["items"] as $key => $val) {
			if (($val["type"] == "date")||($val["type"] == "date_sel")) {
				$y = $_REQUEST[$key . "_year"];
				$m = $_REQUEST[$key . "_month"];
				$d = $_REQUEST[$key . "_day"];
				if ($y || $m || $d) {
					if ($y && $m && $d) {
						if ((!num_check($y))||(!num_check($m, 1, 12))||(!num_check($d, 1, 31))) {
							$msg[$key] = "日付が間違っています";
						} else {
							$_REQUEST[$key] = sprintf("%04d-%02d-%02d", $y, $m, $d);
						}
					} else {
						$msg[$key] = "年月日全て入力してください";
					}
				} else {
					$_REQUEST[$key] = array();
				}
			} else if (($val["type"] == "yearmonth")||($val["type"] == "yearmonth_sel")) {
				$y = $_REQUEST[$key . "_year"];
				$m = $_REQUEST[$key . "_month"];
				if ($y || $m) {
					if ($y && $m) {
						if ((!num_check($y))||(!num_check($m, 1, 12))) {
							$msg[$key] = "年月が間違っています";
						} else {
							$_REQUEST[$key] = sprintf("%04d-%02d-01", $y, $m);
						}
					} else {
						$msg[$key] = "年月を入力してください";
					}
				} else {
					$_REQUEST[$key] = array();
				}
			} else if (($val["type"] == "time")||($val["type"] == "time_sel")) {
				$h = $_REQUEST[$key . "_hour"];
				$m = $_REQUEST[$key . "_min"];
				if ($h || $m) {
					if ($h && $m) {
						if ((!num_check($h, 0, 23))||(!num_check($m, 0, 59))) {
							$msg[$key] = "時間が間違っています";
						} else {
							$_REQUEST[$key] = sprintf("%02d:%02d", $h, $m);
						}
					} else {
						$msg[$key] = "時間は全て入力してください";
					}
				} else {
					$_REQUEST[$key] = array();
				}
			} else if (($val["type"] == "datetime")||($val["type"] == "datetime_sel")) {
				$y = $_REQUEST[$key . "_year"];
				$m = $_REQUEST[$key . "_month"];
				$d = $_REQUEST[$key . "_day"];
				$h = $_REQUEST[$key . "_hour"];
				$f = $_REQUEST[$key . "_min"];
				if ($y || $m || $d || $h || $f) {
					if ($y && $m && $d && $h && $f) {
						if ((!num_check($y))||(!num_check($m, 1, 12))||(!num_check($d, 1, 31))||(!num_check($h, 0, 23))||(!num_check($f, 0, 59))) {
							$msg[$key] = "日付時間が間違っています";
						} else {
							$_REQUEST[$key] = sprintf("%04d-%02d-%02d %02d:%02d", $y, $m, $d, $h, $f);
						}
					} else {
						$msg[$key] = "年月日時分全て入力してください";
					}
				} else {
					$_REQUEST[$key] = array();
				}
			} else if ($val["type"] == "gmap") {	// GoogleMap
				if ($_REQUEST[$key . "_lat"] && $_REQUEST[$key . "_lon"] && $_REQUEST[$key . "_zoom"]) {
					$_REQUEST[$key] = $_REQUEST[$key . "_lat"] . "," . $_REQUEST[$key . "_lon"] . "," . $_REQUEST[$key . "_zoom"];
				}
			}
			if (($val["type"] == "checkbox")&&($val["list"] || $val["reference"])) {		// 複数
				if ($val["reference"]) {
					$vlist = reference_list($item["value"], $val["reference"]);
					if ($vlist) {
						$val["list"] = array();
						foreach ($vlist as $k => $v) {
							$val["list"][$v["value"]] = $v["name"];
						}
					}
				}
				unset($v);
				foreach ($val["list"] as $key2 => $val2) {
					$key_name = $key . "_" . $key2;
/*
					if ($_REQUEST[$key_name]) {
						$info[$key_name] = $_REQUEST[$key_name];
					} else {
						$info[$key_name] = array();
					}
*/
					if ($_REQUEST[$key_name]) {
						$v[] = $_REQUEST[$key_name];
					}
				}
				if ($v) {
					$info[$key] = join(",", $v);
				} else {
					$info[$key] = "";
				}
				$_REQUEST[$key] = $v;
			} else if (($val["type"] == "category")||($val["type"] == "category2")) {		// カテゴリ
				// 通常の配列保存に変更（mysql.php側機能）
				//	$info[$key] = join(",", $_REQUEST[$key]);
				$info[$key] = $_REQUEST[$key];
			} else {
				$info[$key] = $_REQUEST[$key];
			}
			if ($val["require"]) {	// 必須
				if ($_REQUEST[$key] === "") {	// 空
					$msg[$key] = "必須項目です";
				}
			}
			if ((!$msg[$key]) && $val["check"]) {
				// array("N", "10"),	// 字種、桁数
				$check = $val["check"];
				$value = $_REQUEST[$key];
				$len = mb_strlen($value, SCRIPT_ENCODING);
				if ($len && $check[1]) {	// 桁数チェック
					if ($len > $check[1]) {
						$msg[$key] = "文字数が制限を越えています。";
					}
				}
				if ($len) {
					if ($check[0] == "TEL") {
						if (FormCheck::tel($value)) {
							$msg[$key] = "書式がただしくありません。";
						}
					} else if ($check[0] == "ZIP") {
						if (!preg_match("/^([0-9]{3})(-[0-9]{4})?$/i", $value)) {
							$msg[$key] = "書式がただしくありません。";
						}
					} else if ($check[0] == "MAIL") {
						if (FormCheck::mail($value)) {
							$msg[$key] = "メールアドレスとしてただしくありません。";
						}
					} else if ($check[0] == "URL") {
						if (!preg_match('/^(http|https|ftp):\/\/([A-Z0-9][A-Z0-9_-]*(?:\.[A-Z0-9][A-Z0-9_-]*)+):?(\d+)?\/?/i', $value)) {
							$msg[$key] = "書式がただしくありません。";
						}
					} else if ($check[0]) {
						if (FormCheck::moji($value, $check[0])) {
							$msg[$key] = "使用できない文字種が含まれています。";
						}
					}
				}
			}
		}
		if (!$msg) {
			// 追加、更新処理
			$file_temp = array();
			foreach ($form["input"]["items"] as $key => $val) {
				if ($val["type"] == "image") {
					if ($_FILES[$key]["name"]) {
						// 以前の画像があれば
						if ($_REQUEST[$key . "_old"]) {
							delete_image($_REQUEST[$key . "_old"], $id);
						}
						$max_x = 0;
						if ($val["max_x"]) {
							$max_x = $val["max_x"];
						}
						$max_y = 0;
						if ($val["max_y"]) {
							$max_y = $val["max_y"];
						}
						if (defined("IMAGE_DIR")) {
							$img_id = save_image($_FILES[$key], $id, $form["title"], IMAGE_DIR, $max_x, $max_y);
						} else {
							$img_id = save_image($_FILES[$key], $id, $form["title"], "", $max_x, $max_y);
						}
						if ($img_id) {
							$info[$key] = $img_id;
						} else {
							$msg[] = $_FILES[$key]["name"] . "は保存できませんでした。画像は1Mバイト以内にしてください。";
						}
					} else if ($_REQUEST[$key . "_old"]) {
						if ($_REQUEST[$key . "_del"]) {
							delete_image($_REQUEST[$key . "_old"], $id);	// 画像は削除
							$info[$key] = array();
						} else {
							$info[$key] = $_REQUEST[$key . "_old"];	// 前回の画像のまま
						}
					} else {
						$info[$key] = array();
					}
				} else if ($val["type"] == "file") {
					// 添付ファイル
					if ($_FILES[$key]["name"]) {
						// 以前のファイルがあれば
						if ($_REQUEST[$key . "_old"]) {
							//
						}
						if ($val["filesize"]) {
							$img = form_file($key, $msg, $doc_type, $val["filesize"]);
						} else {
							$img = form_file($key, $msg, $doc_type, MAX_FILE_SIZE);
						}
						if ($img) {
							$info[$key] = $img;
						} else {
							$msg[] = $_FILES[$key]["name"] . "は保存できませんでした。";
						}
					} else if ($_REQUEST[$key . "_old"]) {
						if ($_REQUEST[$key . "_del"]) {
							//
							$info[$key] = array();
						} else {
							$info[$key] = $_REQUEST[$key . "_old"];	// 前回の画像のまま
						}
					} else {
						$info[$key] = array();
					}
				} else if (($val["type"] == "link")||($val["type"] == "reference")) {
					// 保存しない
					unset($info[$key]);
				} else if (($val["type"] == "checkbox")&&($val["reference"])) {		// 複数
					//
				} else if (($val["type"] == "checkbox")&&($val["list"])) {		// 複数
					//
				} else if (($val["type"] == "category")||($val["type"] == "category2")) {		// カテゴリ
					//
				} else if ($val["type"] == "gmap") {
					if ($_REQUEST[$key]) {
						$info[$key] = $_REQUEST[$key];
						$info["lat"] = $_REQUEST[$key . "_lat"];
						$info["lon"] = $_REQUEST[$key . "_lon"];
						$info["zoom"] = $_REQUEST[$key . "_zoom"];
					}
				} else {
					if ($val["nosave"]) {
						unset($info[$key]);
					} else if (isset($_REQUEST[$key])) {
						if ($_REQUEST[$key] === "") {
							$info[$key] = array();
						} else {
							$info[$key] = $_REQUEST[$key];
						}
					} else {
						unset($info[$key]);
					}
				}
			}
			if (($mode == "edit") && $id) {		// 更新
				if ($class) {
					$class->updateData($id, $info);
					if ($form["post_update"]) {		// 更新後処理
						$val = call_user_func($form["post_update"], $id, $info);
					}
					$ret = $id;
				} else {
					unset($item);
					// 更新の場合
					if ($_REQUEST["title"]) {
						$item["title"] = $_REQUEST["title"];
					} else if ($_REQUEST["name"]) {
						$item["title"] = $_REQUEST["name"];
					}
					if ($_REQUEST["ord"]) {
						$item["ord"] = $_REQUEST["ord"];
					}
					if ($_REQUEST["open"]) {
						$item["open"] = $_REQUEST["open"];
					}
					if ($item) {
						$item["kind"] = $form["id"];
						Info::updateData($id, $item);
					}
					save_info_item($id, $info);
					$ret = id;
				}
			} else {	// 追加
				if ($class) {
					$ret = $class->addData($info);
					if ($ret) {
						$id = $ret;
					}
					if ($form["post_update"]) {		// 更新後処理(新規の場合も行なう2013/9/17)
						$val = call_user_func($form["post_update"], $id, $info);
					}
				} else {
					$item["kind"] = $form["id"];
					if ($_REQUEST["title"]) {
						$item["title"] = $_REQUEST["title"];
					} else if ($_REQUEST["name"]) {
						$item["title"] = $_REQUEST["name"];
					}
					if ($_REQUEST["ord"]) {
						$item["ord"] = $_REQUEST["ord"];
					}
					if ($_REQUEST["open"]) {
						$item["open"] = $_REQUEST["open"];
					}
					$id = Info::addData($item);
					save_info_item($id, $info);
					$ret = id;
				}
			}
			if ($file_temp) {
				foreach ($file_temp as $key => $val) {
					if ($info[$key] && file_exists($val)) {
						$file_name = sprintf("../userdata/%04d_%d.pdf", $_REQUEST["user_id"], $id);
						if (file_exists($file_name)) {
							unlink($file_name);
						}
						rename($val, $file_name);
					}
				}
			}
			if ($ret) {
				$msg[] = "保存しました。";
			} else {
				$msg[] = "保存に失敗しました。";
			}
			/* 検索条件に該当する物をクリア */
			if ($form["list"]["search"]) {
				foreach ($form["list"]["search"] as $key => $val) {
					unset($_REQUEST[$key]);
				}
			}
			if ($form["list"])  {
				$act = "list";
			} else {
				$act = "";
			}
		}
			if ($msg) {
				$html->set("message", join("<br>", $msg));
			}
	}
	if (($act == "edit")||($act == "update")) {
		if (($act == "edit") && $_REQUEST["page"]) {
			$html->set("_page_", $_REQUEST["page"]);
		}
		$id = $_REQUEST["id"];
		if ($id && ($act == "edit")) {
			if ($class) {
				$info = $class->getData($id);
			} else {
				$item = Info::getData($id);
				if ($item) {
					$info = get_info_item($id);
				}
			}
		}
		if ($info) {
			foreach ($form["input"]["items"] as $key => $val) {
				unset($year);
				unset($item);
				$item = $val;
				$item["name"] = $key;
				$item[$val["type"]] = "1";
				if (isset($info[$key])) {
					if ($val["type"] == "fckeditor") {
						$item["value"] = mb_ereg_replace("[\n\r]", "", $info[$key]);
						$item["value"] = mb_ereg_replace("'", "\\'", $item["value"]);
					} else if ($val["type"] == "text") {
						$item["value"] = htmlspecialchars($info[$key]);
					} else {
						$item["value"] = $info[$key];
						if ($val["func"]) {
							$item["file_link"] = call_user_func($val["func"], $id, $info, $key);
						}
					}
				}
				if ($val["type"] == "reference") {
				//	$item["value"] = $info[$key];
					$item["reference_value"] = reference($info[$key], $val["reference"]);
				}
				if ($val["type"] == "date") {
					if (($act == "edit")&&($info[$key])) {
						$item["value"] = array();
						$item["value"]["year"] = substr($info[$key], 0, 4);
						$item["value"]["month"] = substr($info[$key], 5, 2);
						$item["value"]["day"] = substr($info[$key], 8, 2);
					}
					if ($act == "update") {
						$item["value"]["year"] = $_REQUEST[$key . "_year"];
						$item["value"]["month"] = $_REQUEST[$key . "_month"];
						$item["value"]["day"] = $_REQUEST[$key . "_day"];
					}
				}
				if (($val["type"] == "date_sel")||($val["type"] == "yearmonth_sel")) {
					if (($act == "edit")&&($info[$key])) {
						$year = substr($info[$key], 0, 4);
						$item["month" . substr($info[$key], 5, 2)] = "selected";
						if ($val["type"] == "date_sel") {
							$item["day" . substr($info[$key], 8, 2)] = "selected";
						}
					}
					if ($act == "update") {
						$year = $_REQUEST[$key . "_year"];
						$item["month" . $_REQUEST[$key . "_month"]] = "selected";
						if ($val["type"] == "date_sel") {
							$item["day" . $_REQUEST[$key . "_day"]] = "selected";
						}
					}
					// 年の選択範囲の設定
					if ($val["year_s"] || $val["year_e"]) {
						if (!$val["year_s"]) {
							$val["year_s"] = date('Y');
						}
						if (!$val["year_e"]) {
							$val["year_e"] = date('Y');
						}
						for ($y = $val["year_s"]; $y <= $val["year_e"]; $y++) {
							if ($y == $year) {
								$item["years"][] = array("value" => $y, "sel" => "selected");
							} else {
								$item["years"][] = array("value" => $y);
							}
						}
					} else {
						// 当年含めて5年（デフォルト）
						for ($y = date('Y'); $y < date('Y') + YEARS_SEL; $y++) {
							if ($y == $year) {
								$item["years"][] = array("value" => $y, "sel" => "selected");
							} else {
								$item["years"][] = array("value" => $y);
							}
						}
					}
				}
				if ($val["type"] == "time") {
					if (($act == "edit")&&($info[$key])) {
						$item["value"] = array();
						$item["value"]["hour"] = substr($info[$key], 0, 2);
						$item["value"]["min"] = substr($info[$key], 3, 2);
					}
					if ($act == "update") {
						$item["value"]["hour"] = $_REQUEST[$key . "_hour"];
						$item["value"]["min"] = $_REQUEST[$key . "_min"];
					}
				}
				if ($val["type"] == "time_sel") {
					if (($act == "edit")&&($info[$key])) {
						$item["hour" . substr($info[$key], 0, 2)] = "selected";
						$item["min" . substr($info[$key], 3, 2)] = "selected";
					}
					if ($act == "update") {
						$item["hour" . $_REQUEST[$key . "_hour"]] = "selected";
						$item["min" . $_REQUEST[$key . "_min"]] = "selected";
					}
				}
				if ($val["type"] == "datetime") {
					$item["value"] = array();
					if (($act == "edit")&&($info[$key])) {
						$item["value"]["year"] = substr($info[$key], 0, 4);
						$item["value"]["month"] = substr($info[$key], 5, 2);
						$item["value"]["day"] = substr($info[$key], 8, 2);
						$item["value"]["hour"] = substr($info[$key], 11, 2);
						$item["value"]["min"] = substr($info[$key], 14, 2);
					}
					if ($act == "update") {
						$item["value"]["year"] = $_REQUEST[$key . "_year"];
						$item["value"]["month"] = $_REQUEST[$key . "_month"];
						$item["value"]["day"] = $_REQUEST[$key . "_day"];
						$item["value"]["hour"] = $_REQUEST[$key . "_hour"];
						$item["value"]["min"] = $_REQUEST[$key . "_min"];
					}
				}
				if ($val["type"] == "datetime_sel") {
					if (($act == "edit")&&($info[$key])) {
						$year = substr($info[$key], 0, 4);
						$item["month" . substr($info[$key], 5, 2)] = "selected";
						$item["day" . substr($info[$key], 8, 2)] = "selected";
						$item["hour" . substr($info[$key], 11, 2)] = "selected";
						$item["min" . substr($info[$key], 14, 2)] = "selected";
					}
					if ($act == "update") {
						$year = $_REQUEST[$key . "_year"];
						$item["month" . $_REQUEST[$key . "_month"]] = "selected";
						$item["day" . $_REQUEST[$key . "_day"]] = "selected";
						$item["hour" . $_REQUEST[$key . "_hour"]] = "selected";
						$item["min" . $_REQUEST[$key . "_min"]] = "selected";
					}
					if ($val["year_s"] || $val["year_e"]) {
						if (!$val["year_s"]) {
							$val["year_s"] = date('Y');
						}
						if (!$val["year_e"]) {
							$val["year_e"] = date('Y');
						}
						for ($y = $val["year_s"]; $y <= $val["year_e"]; $y++) {
							if ($y == $year) {
								$item["years"][] = array("value" => $y, "sel" => "selected");
							} else {
								$item["years"][] = array("value" => $y);
							}
						}
					} else {
						for ($y = date('Y'); $y < date('Y') + YEARS_SEL; $y++) {
							if ($y == $year) {
								$item["years"][] = array("value" => $y, "sel" => "selected");
							} else {
								$item["years"][] = array("value" => $y);
							}
						}
					}
				}
				if ($val["type"] == "gmap") {
					if (($act == "edit")&&($info[$key])) {
						$ary = explode(",", $info[$key]);
					}
					if ($act == "update") {
						$ary = explode(",", $_REQUEST[$key]);
					}
					unset($item["value"]);
					$item["value"]["lat"] = $ary[0];
					$item["value"]["lon"] = $ary[1];
					$item["value"]["zoom"] = $ary[2];
				}
				if ($val["type"] == "checkbox") {
					if ($val["reference"]) {
						$vlist = reference_list($item["value"], $val["reference"]);
						if ($vlist) {
							$val["list"] = array();
							foreach ($vlist as $k => $v) {
								$val["list"][$v["value"]] = $v["name"];
							}
						}
					}
					if ($val["list"]) {		// 複数
						if (is_array($info[$key])) {
							$v = $info[$key];
						} else {
							$v = explode(",", $info[$key]);
						}
						unset($sel);
						foreach ($val["list"] as $key2 => $val2) {
							$key_name = $key . "_" . $key2;
							unset($opt);
							$opt["key"] = $key_name;
							$opt["value"] = $key2;
							$opt["name"] = $val2;
							if ($key2 == $info[$key_name]) {
								$opt["sel"] = "checked";
							}
							if (in_array($key2, $v)) {
								$opt["sel"] = "checked";
							}
							$sel[] = $opt;
						}
						$item["list"] = $sel;
					} else {	// 単一
						if ($info[$key]) {
							$item["checked"] = "checked";
						}
						$item["value"] = $val["value"];
					}
				} else if ($val["list"]) {
					$sel = array();
					foreach ($val["list"] as $key2 => $val2) {
						unset($opt);
						$opt["value"] = $key2;
						$opt["name"] = $val2;
						if ($key2 == $info[$key]) {
							if ($val["type"] == "select") {
								$opt["sel"] = "selected";
							} else {
								$opt["sel"] = "checked";
							}
						}
						$sel[] = $opt;
					}
					$item["list"] = $sel;
/*
					if ($val["list"][$info[$key]]) {
						$item["name"] = $val["list"][$info[$key]];
					}
*/
				}
				if ($val["type"] == "display") {
					if (($act == "edit")&&($info[$key])) {
					//	$item["value"] = $info[$key];
						if ($val["func"]) {
							$item["value"] = call_user_func($val["func"], $key, $info[$key]);
						} else {
							$item["value"] = html_entity_decode($info[$key]);
						}
					} else {
						if ($val["func"]) {
							$item["value"] = call_user_func($val["func"], $key, $_REQUEST[$key]);
						} else {
							$item["value"] = $_REQUEST[$key];
						}
					}
				}
				if ($val["type"] == "link") {
					if ($val["name"]) {
						$item["name"] = $info[$val["name"]];
					} else {
						$item["name"] = $val["title"];
					}
					$item["value"] = $info[$key];
					$item["link"] = $val["link"];
				}
				// 選択・他テーブル参照
				if (($val["type"] == "select")&& $val["reference"]) {
					$item["list"] = reference_list($item["value"], $val["reference"]);
				}
				if ($msg[$key]) {
					$item["message"] = $msg[$key];
				}
				$list[] = $item;
			}
			$html->set("list", $list);
			//
			$html->set("page_title", $form["title"] . "編集");
			$html->set("form", $form);
			$html->set("mode", "edit");
			$html->set("id", $id);
			if ($form["list"])  {
				$html->set("list_menu", "1");
			}
			if ($form["input"]["buttons"]) {
				$html->set("buttons", $form["input"]["buttons"]);
			}
			if ($form["input"]["actions"]) {
				$html->set("actions", $form["input"]["actions"]);
			}
			if ($form["input"]["script"]) {
				$html->set("script", $form["input"]["script"]);
			}
			$html->set("nosave", $form["input"]["nosave"]);
			//
			if ($msg) {
				$html->set("message", join("<br>", $msg));
			}
			$html->view($form["input"]["form"]);
			exit;
		}
		$html->set("message", "無効なデータです");
		if ($form["list"])  {
			$act = "list";
		}
	}
	if (($act == "list")||($act == "csv")) {
		if ($_REQUEST["page"]) {
			$page = $_REQUEST["page"];
		} else {
			$page = 0;
			if ($_REQUEST["_page_"]) {
				$page = $_REQUEST["_page_"];
			}
		}
		$html->set("page", $page);
		//
		// 検索
		if ($form["list"]["search"] && $class) {	// 検索機能はCLASS指定の場合のみ
			foreach ($form["list"]["search"] as $key => $val) {
				if ($val["field"]) {
					$field = $val["field"];
				} else {
					$field = $key;
				}
				if ($val["func"]) {
					if ($_REQUEST[$key]) {
						$class->search[] = call_user_func($val["func"], $key, $_REQUEST[$key], $_REQUEST);
					}
				} else if ($val["type"] == "date_period") {
					if ($_REQUEST[$key . "_yearfrom"] && $_REQUEST[$key . "_monthfrom"] && $_REQUEST[$key . "_dayfrom"]) {
						$class->search[] = array("expression" => "date_format(" . $field . ",'%Y-%m-%d') >= date_format('" . $_REQUEST[$key . "_yearfrom"] . "-" . $_REQUEST[$key . "_monthfrom"] . "-" . $_REQUEST[$key . "_dayfrom"] . "','%Y-%m-%d')");
					}
					if ($_REQUEST[$key . "_yearto"] && $_REQUEST[$key . "_monthto"] && $_REQUEST[$key . "_dayto"]) {
						$class->search[] = array("expression" => "date_format(" . $field . ",'%Y-%m-%d') <= date_format('" . $_REQUEST[$key . "_yearto"] . "-" . $_REQUEST[$key . "_monthto"] . "-" . $_REQUEST[$key . "_dayto"] . "','%Y-%m-%d')");
					}
				} else if ($val["type"] == "date") {
					if ($_REQUEST[$key . "_year"] && $_REQUEST[$key . "_month"] && $_REQUEST[$key . "_day"]) {
						$class->search[] = array("expression" => "date_format(" . $field . ",'%Y-%m-%d') = date_format('" . $_REQUEST[$key . "_year"] . "-" . $_REQUEST[$key . "_month"] . "-" . $_REQUEST[$key . "_day"] . "','%Y-%m-%d')");
					}
				} else if (($val["type"] == "cate") && $_REQUEST[$key]) {
					$class->search[] = array("field" => $field, "cond" => "in",
						"select" => array("table" => "profile", "where" => 
							array(
								array("field" => "name", "value" => $val["cate_name"], "cond" => "="),
								array("field" => "value", "value" => $_REQUEST[$key], "cond" => "=")
							), "fields" => array("distinct id")));
				} else if ($val["type"] == "range") {
					if ($_REQUEST[$key . "_min"]) {
						$class->search[] = array("field" => $field, "value" => $_REQUEST[$key . "_min"], "cond" => ">=");
					}
					if ($_REQUEST[$key . "_max"]) {
						$class->search[] = array("field" => $field, "value" => $_REQUEST[$key . "_max"], "cond" => "<=");
					}
				} else if (isset($_REQUEST[$key])&&($_REQUEST[$key] !== "")) {
					if ($val["cond"]) {
						if (strcasecmp($val["cond"], "like") == 0) {
							$class->search[] = array("field" => $field, "value" => "%" . $_REQUEST[$key] . "%", "cond" => $val["cond"]);
						} else {
							$class->search[] = array("field" => $field, "value" => $_REQUEST[$key], "cond" => $val["cond"]);
						}
					} else {
						$class->search[] = array("field" => $field, "value" => $_REQUEST[$key], "cond" => "=");
					}
				}
			}
		}
		if ($form["list"]["cond"]) {
			foreach ($form["list"]["cond"] as $val) {
				$class->search[] = $val;
			}
		}
		if ($form["list"]["join"]) {
			foreach ($form["list"]["join"] as $val) {
				$class->join[] = $val;
			}
		}
		if ($form["list"]["script"]) {
			$html->set("script", $form["list"]["script"]);
		}
	}
	if (($act == "csv")&& $class) {		// CSV機能はCLASS指定の場合のみ
		if ($form["list"]["csv"]["filename"]) {
			$filename = $form["list"]["csv"]["filename"];
		} else {
			$filename = $form["name"] . ".csv";
		}
		if ($form["list"]["csv"]["code"]) {
			$code = $form["list"]["csv"]["code"];
		} else {
			$code = "Shift_Jis";
		}
		header("Content-disposition: attachment; filename=" . $filename);
		header("Content-type: text/x-csv; charset=" . $code);
		if ($form["list"]["csv"]["title"]) {
			// タイトル
			$csv = array();
			foreach ($form["list"]["csv"]["items"] as $val) {
				if ($form["list"]["csv"]["title"] == "1") {
					array_push($csv, '"' . htmlentities($val) . '"');
				} else {
					if ($form["input"]["items"][$val]["title"]) {
						array_push($csv, '"' . $form["input"]["items"][$val]["title"] . '"');
					} else {
						array_push($csv, '"' . htmlentities($val) . '"');
					}
				}
			}
			echo mb_convert_encoding(implode(",", $csv), $code, SCRIPT_ENCODING) . "\r\n";
		}
		$count = $class->getCount();
		// 件数が多い場合は小分けに出力することを検討
		$class->limit = 0;
		$list = $class->getList($form["list"]["csv"]["items"]);
		foreach ($list as $val) {
			$csv = array();
			if ($form["list"]["csv"]["func"] && $val[$key]) {
				// 書式化関数指定の場合
				$val = call_user_func($form["list"]["csv"]["func"], $val);
			}
			foreach ($val as $key => $item) {
				if ($form["list"]["csv"]["convert"]) {
					// 自動変換処理
					//$key = $form["list"]["csv"]["items"][$no];
					if ($form["input"]["items"][$key]["type"] == "image") {	// 画像
						$v = Image::getData($item);
						if ($v) {
							$item = $v["file_name"];
						} else if (!$v) {
							$item = "";
						}
					} else if ($form["input"]["items"][$key]["type"] == "file") {	// ファイル
						$v = Files::getData($item);
						if ($v) {
							$item = $v["file_name"];
						} else if (!$v) {
							$item = "";
						}
					} else if (($form["input"]["items"][$key]["type"] == "category")||($form["input"]["items"][$key]["type"] == "category2")) {	// カテゴリ
						$item = disp_category(explode(",", $item), ",");
					} else if ($form["input"]["items"][$key]["list"]) {
						if ($form["input"]["items"][$key]["type"] == "checkbox") {	// 重複回答
							$ary = explode(",", $item);
							if ($ary) {
								$v2 = array();
								foreach ($ary as $v) {
									if (isset($form["input"]["items"][$key]["list"][$v])) {
										$v2[] = $form["input"]["items"][$key]["list"][$v];
									} else {
										$v2[] = $v;
									}
								}
								$item = join(",", $v2);
							}
						} else {
							if (isset($form["input"]["items"][$key]["list"][$item])) {
								$item = $form["input"]["items"][$key]["list"][$item];
							} else {
								;
							}
						}
					} else if ($form["input"]["items"][$key]["reference"]) {
						if ($form["input"]["items"][$key]["type"] == "checkbox") {	// 重複回答
							$vallist = reference_list("", $form["input"]["items"][$key]["reference"], true);
							if ($vallist) {
								$ary = explode(",", $item);
								if ($ary) {
									$v2 = array();
									foreach ($ary as $val2) {
										if ($vallist[$val2]) {
											$v2[] = $vallist[$val2];
										} else {
											$v2[] = $val2;
										}
									}
									$item = join(",", $v2);
								}
							} else {
								;
							}
						} else {
							$item = reference($item, $form["input"]["items"][$key]["reference"]);
						}
					} else {
						;
					}
				}
				array_push($csv, '"' . $item . '"');
			}
			echo mb_convert_encoding(implode(",", $csv), $code, SCRIPT_ENCODING) . "\r\n";
		}
		exit;
	}
	if ($act == "list") {
		if ($class) {
			// 並び替え
			if ($_REQUEST["_sort_"]) {
				if ($_REQUEST["_order_"]) {
					$class->order[] = array("field" => $_REQUEST["_sort_"], $_REQUEST["_order_"] => "1");
				} else {
					$class->order[] = array("field" => $_REQUEST["_sort_"]);
				}
			} else {
				if ($form["list"]["order"]) {	// order指定があればそれが優先
					$class->order = $form["list"]["order"];
				} else if ($form["list"]["items"]["reg_date"]) {	// 一覧にreg_dateがあれば
					$class->order[] = array("field" => "reg_date", "desc" => "1");
				}
			}
			//
			if ($form["list"]["limit"]) {
				$class->limit = $form["list"]["limit"];	// 1ページの表示件数
			} else {
				$class->limit = ADMIN_MAX_PAGE;	// 1ページの表示件数
			}
			$count = $class->getCount();
			$pages = intval(($count + $class->limit - 1) / $class->limit);
			if ($pass) {
				$data["total"] = $count;
			} else {
				$html->set("total", $count);
			}
			$class->page = $page;
			if ($form["list"]["fields"]) {
				$list = $class->getList($form["list"]["fields"]);
			} else {
				$list = $class->getList();
			}
			if ($pages > 1) {
				if ($page >= $pages) {
					$page = $pages - 1;
				}
				if ($pass) {
					$data["pager"] = page_index2($page, $pages);
					$data["start"] = $page * $class->limit + 1;
					$data["end"] = $page * $class->limit + count($list);
					$data["count"] = $count;
				} else {
					$html->set("pager", page_index2($page, $pages));
				}
			}
			$html->set(array("start" => $page * $class->limit + 1, "end" => $page * $class->limit + count($list), "count" => $count));
		} else {
			$db = new Info();
			$db->search[] = array("field" => "kind", "value" => $form["id"], "cond" => "=");
			if ($form["list"]["limit"]) {
				$db->limit = $form["list"]["limit"];	// 1ページの表示件数
			} else {
				$db->limit = ADMIN_MAX_PAGE;	// 1ページの表示件数
			}
			// 検索
			if ($form["list"]["search"]) {
				$where = array();
				foreach ($form["list"]["search"] as $key => $val) {
					if ($_REQUEST[$key]) {
						if ($val["cond"]) {
							$where[$key][] = array("field" => "contents", "value" => $_REQUEST[$key], "cond" => $val["cond"]);
						} else {
							$where[$key][] = array("field" => "contents", "value" => $_REQUEST[$key], "cond" => "=");
						}
						$where[$key][] = array("field" => "kind", "value" => $key, "cond" => "=");
						$db->search[] = array("field" => "info_id", "cond" => "in",
							"select" => array("table" => "info_item", "where" => $where[$key], "fields" => array("info_id")));
					}
				}
			}
			$count = $db->getCount();
			$pages = intval(($count + $db->limit - 1) / $db->limit);
			if ($pages > 1) {
				$html->set("pager", page_index2($page, $pages));
			}
			$db->page = $page;
			$db->order[] = array("field" => "ord");
			$list = $db->getList();
		}
		if ($list) {
			$rownum = 1;
			foreach ($list as $val) {
				unset($item);
				if ($rownum & 1) {
					$item["rowclass"] = "odd";
				} else {
					$item["rowclass"] = "even";
				}
				$rownum++;
/*
				if (!$form["list"]["noid"]) {
					$item["id"] = $val[$form["id"]];
					$item["items"][] = $val[$form["id"]];
				}
*/
				$item["id"] = $val[$form["id"]];
				foreach ($form["list"]["items"] as $key => $val2) {
					$item_val = "";
					if (isset($val[$key])) {
						if ($val2["values"]) {
							if (isset($val2["values"][$val[$key]])) {
								$item_val = $val2["values"][$val[$key]];
							} else {
								$item_val = "[" . $val[$key] . "]";
							}
						} else if ($val2["date_format2"]) {
							$item_val = date_format2($val[$key], $val2["date_format2"]);
						} else if ($val2["reference"]) {
							$item_val = reference($val[$key], $val2["reference"]);
						} else {
							$item_val = $val[$key];
						}
					} else {
					//	$item["items"][] = "";
					}
					if ($val2["link"] && $val[$key] && $item_val) {
						$item_val = "<a href=\"" . $val2["link"] . $val[$key] . "\" target=\"_blank\">" . $item_val . "</a>";
					}
					// 個別の書式化
					if ($val2["func"] && $val[$key]) {
						$item_val = call_user_func($val2["func"], $key, $val[$key], $val);
					}
					$item["items"][] = $item_val;
				}
				// 全体の書式化
				if ($form["list"]["format"]) {
					$item = call_user_func($form["list"]["format"], $item);
				}
				$list_data[] = $item;
			}
			if ($pass) {
				$data["list"] = $list_data;
			} else {
				$html->set("list", $list_data);
			}
		}
		if ($form["list"]["buttons"]) {
			$html->set("buttons", $form["list"]["buttons"]);
		}
		$html->set("nobuttons", $form["list"]["nobuttons"]);
		// CSV
		if ($form["list"]["csv"]) {
			$html->set("csv", $form["list"]["csv"]);
		}
		// 選択
		if ($form["list"]["select"]) {
			unset($item);
			$item = $form["list"]["select"];
			$item["name"] = "select";
			$item["type" . $form["list"]["type"]] = "1";
			$item["cols"] = count($form["list"]["items"]) + 2;
			$html->set("select", $item);
		}
		// 見出し
		unset($title);
		foreach ($form["list"]["items"] as $key => $val) {
			unset($item);
			$item["name"] = $key;
			$item["title"] = $val["title"];
			//
			if ($form["list"]["sort"][$key]) {
				if ($_REQUEST["_sort_"] == $key) {
					if ($_REQUEST["_order_"] == "asc") {
						$item["sort"] = array("key" => $key, "ord" => "asc");
					} else {
						$item["sort"] = array("key" => $key, "ord" => "desc");
					}
				} else {
					$item["sort"] = array("key" => $key, "ord" => "");
				}
				$sort["key"] = $_REQUEST["_sort_"];
				$sort["ord"] = $_REQUEST["_order_"];
				$html->set("sort", $sort);
			}
			$title[] = $item;
		}
		$html->set("title", $title);
		// 検索
		if ($form["list"]["search"]) {
			unset($search);
			foreach ($form["list"]["search"] as $key => $val) {
				unset($item);
				if (($val["type"] == "select")&& $val["reference"]) {
					$list = reference_list($val[$key], $val["reference"]);
					if ($list) {
						foreach ($list as $val2) {
							$val["list"][$val2["value"]] = $val2["name"];
						}
					}
				}
				if ($val["type"] == "date_period") {
					$item["yearfrom"] = make_select_list($val["years"], $_REQUEST[$key . "_yearfrom"]);
					$item["monthfrom"] = make_select_list(array("1","2","3","4","5","6","7","8","9","10","11","12"), $_REQUEST[$key . "_monthfrom"]);
					$item["dayfrom"] = make_select_list(array("1","2","3","4","5","6","7","8","9","10","11","12","13","14","15","16","17","18","19","20","21","22","23","24","25","26","27","28","29","30","31"), $_REQUEST[$key . "_dayfrom"]);
					$item["yearto"] = make_select_list($val["years"], $_REQUEST[$key . "_yearto"]);
					$item["monthto"] = make_select_list(array("1","2","3","4","5","6","7","8","9","10","11","12"), $_REQUEST[$key . "_monthto"]);
					$item["dayto"] = make_select_list(array("1","2","3","4","5","6","7","8","9","10","11","12","13","14","15","16","17","18","19","20","21","22","23","24","25","26","27","28","29","30","31"), $_REQUEST[$key . "_dayto"]);
				}
				if ($val["type"] == "date") {
					$item["year"] = make_select_list($val["years"], $_REQUEST[$key . "_year"]);
					$item["month"] = make_select_list(array("1","2","3","4","5","6","7","8","9","10","11","12"), $_REQUEST[$key . "_month"]);
					$item["day"] = make_select_list(array("1","2","3","4","5","6","7","8","9","10","11","12","13","14","15","16","17","18","19","20","21","22","23","24","25","26","27","28","29","30","31"), $_REQUEST[$key . "_day"]);
				}
				$item["title"] = $val["title"];
				$item["info"] = $val["info"];
				$item[$val["type"]] = "1";
				if ($val["list"]) {
					foreach ($val["list"] as $key2 => $val2) {
						if (isset($_REQUEST[$key])&&($_REQUEST[$key] !== "")&&($_REQUEST[$key] == $key2)) {
							$item["list"][] = array("name" => $val2, "value" => $key2, "sel" => "selected");
						} else {
							$item["list"][] = array("name" => $val2, "value" => $key2);
						}
					}
				}
				if ($val["type"] == "range") {
					$item["value_min"] = $_REQUEST[$key . "_min"];
					$item["value_max"] = $_REQUEST[$key . "_max"];
				}
				if ($val["type"] == "cate") {
					$tmp = category_list(1, 2);
					if ($tmp) {
						foreach ($tmp as $tmp2) {
							unset($v);
							$v["name"] = $tmp2["title"];
							$v["group"] = "1";
							$item["list"][] = $v;
							foreach ($tmp2["level2"] as $tmp3) {
								unset($v);
								$v["name"] = $tmp3["title"];
								$v["value"] = $tmp3["seq"];
								$v["item"] = "1";
								if ($_REQUEST[$key] == $tmp3["seq"]) {
									$v["sel"] = "selected";
								}
								$item["list"][] = $v;
							}
						}
					}
					
				}
				$item["name"] = $key;
				$item["value"] = $_REQUEST[$key];
				$search[] = $item;
			}
			$html->set("search", $search);
		}
//		$html->set("sort", $form["list"]["sort"]);
		//
		$html->set("page_title", $form["title"] . "一覧");
		$html->set("form", $form);
		//
		if ($msg) {
			$html->set("message", join("<br>", $msg));
		}
		if (!$pass) {
			$html->view($form["list"]["form"]);
			exit;
		}
	}
	return $ret;
}
// 特定のテーブルのデータを参照
function reference($id, $reference)
{
	$val = call_user_func(array($reference["class"], "getData"), $id);
	return $val[$reference["field"]];
}
function reference_list($id, $reference, $keylist=false)
{
	$list = array();
/* 空を付与する場合
	$opt["value"] = "";
	$opt["name"] = "";
	$list[] = $opt;
*/
	if ($reference["cond"]) {
		$cond = $reference["cond"];
	} else {
		$cond = array();
	}
	$ret = call_user_func(array($reference["class"], "findData"), $cond, array($reference["value"], $reference["name"]), false, $reference["order"]);
	if ($ret) {
		foreach ($ret as $val) {
			if ($keylist) {
				$list[$val[$reference["value"]]] = $val[$reference["name"]];
			} else {
				unset($opt);
				$opt["value"] = $val[$reference["value"]];
				$opt["name"] = $val[$reference["name"]];
				if ($opt["value"] == $id) {
					$opt["sel"] = "selected";
				}
				$list[] = $opt;
			}
		}
	}
	return $list;
}
// 数値チェック
function num_check($val, $low=null, $high=null) {
	if (!mb_ereg("^[0-9]+$", $val)) {
		return false;
	}
	if (isset($low) && ($val < $low)) {
		return false;
	}
	if (isset($high) && ($val > $high)) {
		return false;
	}
	return true;
}
// 選択リスト作成
function make_select_list($list, $value)
{
	$ret = array();
	foreach ($list as $val) {
		unset($item);
		$item["value"] = $val;
		$item["name"] = $val;
		if ($val == $value) {
			$item["sel"] = "selected";
		}
		$ret[] = $item;
	}
	return $ret;
}
/*
function save_file($file, $file_name)
{
	if ($file["name"]) {
		if (copy($file["tmp_name"], $file_name)) {
			return $file["name"];
		}
	}
	return "";
}
*/
?>
