<?php
/*
 * 復号可能な暗号化ユーティリティ
 */
class CryptUtil {
	/**
	 * @des	暗号化用キーを設定、取得する
	 */
	function setKey($str = null)
	{
		static $key = 'defultkey';
		if ($str != null) {
			$key = $str;
		}
		return $key;
	}
	/**
	 * @des	最終的な暗号化文字列を生成する（内部用）
	 */
	function Generator($string, $encryptKey)
	{
		$encryptKey = md5($encryptKey);
		$cur = 0;
		$buf = null;
		for ($i = 0 ; $i < strlen($string); $i++) {
			if ($cur == strlen($encryptKey)) {
				$cur = 0;
			};
			$buf .= substr($string, $i ,1) ^ substr($encryptKey, $cur++, 1);
		}
		return $buf;
	} 
	/**
	 * @des	暗号化する
	 */
	function encrypt($string)
	{
		$key = CryptUtil::setKey();
		srand((double)microtime() * 1000000);
		$encryptKey = md5(rand(0, 32000));
		$cur = 0;
		$buf = null;
		for ($i = 0; $i<strlen($string); $i++) {
			if ($cur == strlen($encryptKey)) {
				$cur = 0;
			}
			$buf .= substr($encryptKey, $cur, 1) . (substr($string, $i, 1) ^ substr($encryptKey, $cur++, 1));
		}
		return base64_encode(CryptUtil::Generator($buf, $key));
	} 
	/**
	 * @desc 復号化する
	 */
	function decrypt($string) 
	{
		$key    = CryptUtil::setKey();
		$string = CryptUtil::Generator(base64_decode($string), $key);
		$buf = null;
		for ($i = 0; $i < strlen($string); $i++) {
			$md5 = substr($string, $i++, 1);
			$buf .= (substr($string, $i, 1) ^ $md5);
		}
		return $buf;
	}
}
?>
