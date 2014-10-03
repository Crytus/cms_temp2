<?php
require_once("crypt.php");
/*
 * ログイン情報のためのCookie操作
 */
// ログイン処理用
if (!defined("LOGIN_COOKIE")) {
	define("LOGIN_COOKIE", "login_info");
}
if (!defined("CRYPT_SEED")) {
	define("CRYPT_SEED", "0fZ89g26cx3ug7");
}
if (!defined("LOGIN_EXPIRE")) {
	define("LOGIN_EXPIRE", 3*30*24*3600);	// 有効期間３ヶ月
}
// ログイン情報（ユーザIDとニックネーム）をCookieに保存する
function setLoginCookie($name, $user_id, $cookie=LOGIN_COOKIE)
{
	CryptUtil::setKey(CRYPT_SEED);
	$en = CryptUtil::encrypt($name);
	$ei = CryptUtil::encrypt($user_id);
	$string = sprintf("%s:<>:%s", $en, $ei);
	$string = CryptUtil::encrypt($string);
	$expire = time() + LOGIN_EXPIRE;
	setcookie($cookie, $string, $expire);
}
// ログイン情報をCookieから削除する（ログアウト）
function removeLoginCookie($cookie=LOGIN_COOKIE)
{
	$expire = time() - 1;
	setcookie($cookie, "", $expire);
}
// ログイン情報をCookieから取得する
function isLogin($cookie=LOGIN_COOKIE)
{
	CryptUtil::setKey(CRYPT_SEED);
	$str = @$_COOKIE[$cookie];
	if ($str) {
		$string = CryptUtil::decrypt($str);
		$ret = split(":<>:", $string);
		$item["name"] = CryptUtil::decrypt($ret[0]);
		$item["user_id"] = CryptUtil::decrypt($ret[1]);
		return $item;
	}
	return null;
}
?>
