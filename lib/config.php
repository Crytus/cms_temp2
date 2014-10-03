<?php

define("SITENAME", "サイト名");

// -------------------------------------------
// データベースの初期設定
if ($_SERVER["HTTP_HOST"] == "localhost") {
	// 開発環境
	// MySQLのID
	$mysql_id	= "root";
	// MySQLのパスワード
	$mysql_pass	= "test1234";
	// MySQLのデータベース
	$mysql_db		= "npc_db";
	$mysql_debug_db = "npc_db";
	// MySQLのサーバ
	$mysql_server = "192.168.1.6";

	define("TOP", "/test/");
	define("TOP_URL", "http://localhost/test/");

	define("DEBUG", "1");
} else {
	// 本番環境
	// MySQLのID
	$mysql_id	= "";
	// MySQLのパスワード
	$mysql_pass	= "";
	// MySQLのデータベース
	$mysql_db		= "";
	$mysql_debug_db = "";
	// MySQLのサーバ
	$mysql_server = "localhost";

	define("TOP", "/");
	define("TOP_URL", "http://");
}

$DB_URI = array(
	"host" => $mysql_server,
	"db" => $mysql_db,
	"user" => $mysql_id,
	"pass" => $mysql_pass,
);

// ライブラリのパス
$path[] = get_include_path();
//if (isset($_SERVER["WINDIR"])) {	// Windows
if (strncmp(php_uname(), "Windows", 7) == 0) { 
	define("BASE_DIR", realpath(dirname(__FILE__) . "/../") . "");
	$path[] = BASE_DIR . "lib\\";
	$path[] = dirname(__FILE__) . "\\";
	set_include_path(join(';', $path));		// for Windows
} else {
	define("BASE_DIR", realpath(dirname(__FILE__) . "/../") . "/");
	$path[] = BASE_DIR . "lib/";
	$path[] = dirname(__FILE__) . "/";
	set_include_path(join(':', $path));	// for Linux
}
//var_dump(get_include_path());

define("SCRIPT_ENCODING", "UTF-8");
define("DB_ENCODING", "UTF-8");


// 登録画像保存
define("IMAGE_DIR", BASE_DIR . "/userdata/images/");
//define("IMAGE_FOLDER", BASE_DIR . "/userdata/images/");
define("IMAGE_URL", TOP . "userdata/images/");

// 登録ファイルの保存
define("FILE_DIR", BASE_DIR . "/userdata/files/");
//define("FILE_FOLDER", BASE_DIR . "/userdata/files/");
define("FILE_URL", TOP . "userdata/files/");

// -------------------------------------------
// 設定

define("INFO_SETUP", 1);
define("INFO_MAIL", 100);

// -------------------------------------------
//	使用可能なファイルタイプ
//
$image_type = array(
	'image/pjpeg' => 'jpg',
	'image/jpeg'  => 'jpg',
	'image/gif'   => 'gif',
	'image/x-png' => 'png',
	'image/png'   => 'png',
);

define("IMAGE_MAX", 512 * 1024);

// -------------------------------------------

//	都道府県
$pref_list = array(	
	"1" => "北海道",
	"2" => "青森県",
	"3" => "岩手県",
	"4" => "宮城県",
	"5" => "秋田県",
	"6" => "山形県",
	"7" => "福島県",
	"8" => "茨城県",
	"9" => "栃木県",
	"10" => "群馬県",
	"11" => "埼玉県",
	"12" => "千葉県",
	"13" => "東京都",
	"14" => "神奈川県",
	"15" => "新潟県",
	"16" => "富山県",
	"17" => "石川県",
	"18" => "福井県",
	"19" => "山梨県",
	"20" => "長野県",
	"21" => "岐阜県",
	"22" => "静岡県",
	"23" => "愛知県",
	"24" => "三重県",
	"25" => "滋賀県",
	"26" => "京都府",
	"27" => "大阪府",
	"28" => "兵庫県",
	"29" => "奈良県",
	"30" => "和歌山県",
	"31" => "鳥取県",
	"32" => "島根県",
	"33" => "岡山県",
	"34" => "広島県",
	"35" => "山口県",
	"36" => "徳島県",
	"37" => "香川県",
	"38" => "愛媛県",
	"39" => "高知県",
	"40" => "福岡県",
	"41" => "佐賀県",
	"42" => "長崎県",
	"43" => "熊本県",
	"44" => "大分県",
	"45" => "宮崎県",
	"46" => "鹿児島県",
	"47" => "沖縄県",
	"48" => "海外",
	"99" => "非公開",
);

$category_list = array(
	"" => "",
);


$open_list = array(
	"1" => "公開",
	"2" => "非公開",
);
?>
