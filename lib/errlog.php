<?php
/** 
 * @file errlog.inc
 *
 * @desc エラーログファイルを出力する
 **/

// ログ出力ディレクトリ、プリフィックスの設定が無い場合は、この設定が有効になる
if (!defined("LOG_PRIFIX")) {
	// ログファイルプリフィックス
	define("LOG_PRIFIX", "");
}
if (!defined("LOG_DIR")) {
	// ログファイル出力ディレクトリ
	define("LOG_DIR",  dirname(__FILE__) . "/../log/");
}

// ログレベルの宣言
if (!defined("LOG_FATAL")) {
	define("LOG_FATAL", 1);	// 致命的エラー
}
if (!defined("LOG_ERROR")) {
	define("LOG_ERROR", 3);	// エラー
}
if (!defined("LOG_INFO")) {
	define("LOG_INFO", 5);	// 警告
}
if (!defined("LOG_INFO")) {
	define("LOG_INFO", 7);	// 一般情報
}
if (!defined("LOG_DEBUG")) {
	define("LOG_DEBUG", 9);	// デバッグ情報
}

class ErrLog {
	var $logfile = null;
	var $loglevel = 0;
	/**
	 * @name ErrLog
	 * @desc コンストラクタ
	 * @param $logfile ログ出力ファイル名（省略可、省略時は年月日）
	 * @param $loglevel ログ出力レベル（省略可、省略時はエラー、致命的エラーのみ出力）
	 **/
	function ErrLog($logfile=null, $loglevel=3) {
		//
		$this->logfile = $logfile;
		$this->loglevel = $loglevel;
		// ファイル名が指定されない場合のデフォルト（年月日）
		if ($logfile == null) {
			$cur = getdate(time());
			$this->logfile = sprintf(LOG_PRIFIX . "%04d%02d%02d.log", $cur["year"], $cur["mon"], $cur["mday"]);
		}
	}
    /**
     * @name getInstance
	 * @desc インスタンス作成
     */
    function &getInstance($logfile=null, $loglevel=1) {
        static $instance = null;
        if ($instance == null) {
            $instance = new ErrLog($logfile, $loglevel);
        }
        return $instance;
    }

	/**
	 * @name ErrLog_Write
	 * @desc ログファイル出力処理
	 * @param $loglevel ログ出力レベル
	 * @param $logmsg 出力メッセージ（単独の文字列、または文字列の配列）
	 * @param $file エラー発生ファイル（省略可）
	 * @param $line エラー発生行数（省略可、$fileと両方そろっていないと出力しない）
	 **/
	function ErrLog_Write($loglevel, $logmsg, $file=null, $line=null) {
		if ($this->loglevel >= $loglevel) {
			if (($fp = fopen(LOG_DIR . $this->logfile, "a"))) {
				if ($file && $line) {
					$msg = "File: " . $file . " Line: " . $line . "\r\n";
					fputs($fp, $msg);
				}
				if (is_array($logmsg)) {
					$msg = implode("\r\n", $logmsg);
				} else {
					$msg = $logmsg;
				}
				$cur = getdate(time());
				$buf = sprintf("%02d:%02d %d %s\r\n", $cur["hours"], $cur["minutes"], $loglevel, $msg);
				fputs($fp, $buf);
				fclose($fp);
			}
		}
	}
}
?>
