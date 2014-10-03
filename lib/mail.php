<?php
/*
 * メール送信処理
 *
 * UTF8またはJISコードで送信する
 *
 * 2011/06/21 添付ファイルに対応
 *
 */
if (!defined("MAIL_ENCODING")) {
	define("MAIL_ENCODING", "JIS");
}
if (!defined("SCRIPT_ENCODING")) {
	define("SCRIPT_ENCODING", ini_get("mbstring.internal_encoding"));
}

include_once("errlog.php");

function logoutput($message, $level=9, $file="", $line="")
{
	$log = ErrLog::getInstance(null, 5);
	$log->ErrLog_Write($level, $message, $file, $line);
}
/*
 * メール送信処理、本文をファイルから
 *
 * 差し込み処理、長いサブジェクトに対応
 */
function sendmail_file($mail_from, $mail_to, $mail_subject, $body_file, $data, $reply=null, $from_name=null, $attach=null)
{
	$body = file_get_contents($body_file);
	return sendmail($mail_from, $mail_to, $mail_subject, $body, $data, $reply, $from_name, $attach);
}
/*
 * メール送信処理、本文を変数から
 *
 * 差し込み処理、長いサブジェクトに対応
 */
function sendmail($mail_from, $mail_to, $mail_subject, $body, $data, $reply=null, $from_name=null, $attach=null)
{
	$mail_from = trim($mail_from);
	$mail_to = trim($mail_to);
	$mail_subject = trim($mail_subject);
	$body = str_replace("\r", "", trim($body));
	$reply = trim($reply);
	$from_name = trim($from_name);
	//
	if (defined("LOG_DIR")) {
		logoutput("mail:" . $mail_to . "(" . $mail_subject . ")");
	}
	// 本文内の{～}を$dataの値に置き換える
	foreach ($data as $key => $val) {
		$keyword = "{" . $key . "}";
		$body = str_ireplace($keyword, $val, $body);
	//	$body = str_replace($keyword, $val, $body);	// PHP4
	}
	// 送信元情報の作成
	if ($from_name) {
		$header = "From: ";
		if (MAIL_ENCODING == "UTF8") {
			$header .= '=?utf-8?B?';
		} else {
			$header .= '=?iso-2022-jp?B?';
		}
		$header .= base64_encode(mb_convert_encoding($from_name, MAIL_ENCODING, SCRIPT_ENCODING)) .
			 '?= <' . $mail_from . ">";
	} else {
		$header = "From: " . $mail_from;
	}
	if ($attach) {	// ファイル添付
		if ($attach["filepath"]) {	// 実ファイルのPATH
			// ファイルの内容をBASE64エンコード
			$handle = fopen($attach["filepath"], 'r');
			if ($handle) {
				$attachFile = fread($handle, filesize($attach["filepath"]));
				fclose($handle);
				$attach["filebody"] = base64_encode($attachFile);
			}
		}
		if ($attach["filebody"]) {	// ファイルの実体があれば
			$filename = $attach["filename"];
			$filebody = $attach["filebody"];
			$filetype = $attach["filetype"];
			//$uniq_id = "__PHPRECIPE__";
			$uniq_id = uniqid('boundary');
			$header .= "\r\nMIME-Version: 1.0";
			$header .= "\r\nContent-Type: multipart/mixed; boundary=\"" . $uniq_id . "\"\r\n";
			$header .= "\r\n";
			// 本文の加工
			$mailMessage = $body;
			$body  = "--" . $uniq_id . "\r\n";
			if (MAIL_ENCODING == "UTF8") {
				$body .= "Content-Type: text/plain; charset=\"UTF-8\"\r\n";
			} else {
				$body .= "Content-Type: text/plain; charset=\"ISO-2022-JP\"\r\n";
			}
			$body .= "\r\n";
			$body .= $mailMessage . "\r\n";
			$body .= "--" . $uniq_id . "\r\n";
	
			$body .= "Content-Type: $filetype; name=\"$filename\"\r\n";
			$body .= "Content-Transfer-Encoding: base64\r\n";
			$body .= "Content-Disposition: attachment; filename=\"$filename\"\r\n";
			$body .= "\r\n";
			$body .= chunk_split($filebody) . "\r\n";
			$body .= "--" . $uniq_id . "--\r\n";
		}
	} else {
		// 漢字コードの指定
		if (MAIL_ENCODING == "UTF8") {
			$header .= "\nContent-Type: text/plain;\n\tformat=flowed;\n\tcharset=\"utf-8\";\n\treply-type=original";
		} else {
			$header .= "\nContent-Type: text/plain;\n\tcharset=\"iso-2022-jp\"\nContent-Transfer-Encoding: 7bit";
		}
	}
	// 返信先指定
	if ($reply) {
		$header .= "\nReply-to: <" . $reply . ">";
	}
	if ($_SERVER["HTTP_HOST"] == "localhost") {	// テスト環境用設定
		$header .= "\nMessage-ID: <" . time() . "@crytus.co.jp>";
	}
	// 件名の変換
	$subject_str = '';
	while ($mail_subject) {
		if ($subject_str) {
			$subject_str .= "\n\t";
		}
		if (mb_strlen($mail_subject, SCRIPT_ENCODING) < 20) {
			if (MAIL_ENCODING == "UTF8") {
				$subject_str .= '=?utf-8?B?';
			} else {
				$subject_str .= '=?iso-2022-jp?B?';
			}
			$subject_str .= base64_encode(mb_convert_encoding($mail_subject, MAIL_ENCODING, SCRIPT_ENCODING)) . '?=';
			$mail_subject = '';
		} else {
			if (MAIL_ENCODING == "UTF8") {
				$subject_str .= '=?utf-8?B?';
			} else {
				$subject_str .= '=?iso-2022-jp?B?';
			}
			$subject_str .= base64_encode(mb_convert_encoding(mb_substr($mail_subject, 0, 20, SCRIPT_ENCODING), MAIL_ENCODING, SCRIPT_ENCODING)) . '?=';
			$mail_subject = mb_substr($mail_subject, 20, mb_strlen($mail_subject, SCRIPT_ENCODING) - 20, SCRIPT_ENCODING);
		}
	}
	if ($reply) {
		$return = "-f" . $reply;
	} else {
		$return = "-f" . $mail_from;
	}
	// 送信処理
	return @mail($mail_to, $subject_str, mb_convert_encoding($body, MAIL_ENCODING, SCRIPT_ENCODING), $header, $return);
}
?>
