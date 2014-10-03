<?php
// テストデータ投入処理

$data = array(
	array(
		"title" => "コンサルタントログイン",
		"url" => "../login.html",
		"data" => array(
			"mode" => "form",
			"email" => "makiyama@palmgate.jp",
			"passwd" => "111777",
		),
	),

	array(
		"title" => "コラム",
		"url" => "../mypage/colum.html",
		"data" => array(
			"mode" => "form",
			"title" => "コラムタイトルあいうえおかきくけこ",
			"contents" => "コラム内容あいうえおかきくけこ\nさしすせそたちつてとなにぬねの",
			"category" => "",
		),
	),
	array(
		"title" => "セミナー",
		"url" => "../mypage/seminar.html",
		"data" => array(
			"mode" => "form",
			"title" => "セミナータイトルあいうえおかきくけこ",
			"contents" => "セミナー内容あいうえおかきくけこ\nさしすせそたちつてとなにぬねの",
			"category" => "",
			"open_date_year" => "2011",
			"open_date_month" => "4",
			"open_date_day" => "1",
			"close_date_year" => "2011",
			"close_date_month" => "4",
			"close_date_day" => "30",
			"place" => "開催場所さしすせそたちつてとなにぬねの",
			"price" => "無料",
		),
	),

	array(
		"title" => "コンサルペディア",
		"url" => "../mypage/pedia.html",
		"data" => array(
			"mode" => "form",
			"title" => "コンサルペディアたいとるあいうえおかきくけこ",
			"summary" => "コンサルペディアようやくあいうえおかきくけこ\nさしすせそたちつてとなにぬねの",
			"contents" => "コンサルペディア内容あいうえおかきくけこ\nさしすせそたちつてとなにぬねの",
			"category" => "",
		),
	),
	array(
		"title" => "書式・文例集",
		"url" => "../mypage/doc.html",
		"data" => array(
			"mode" => "form",
			"title" => "書式・文例集たいとるあいうえおかきくけこ",
			"caption" => "書式・文例集あいうえおかきくけこ\nさしすせそたちつてとなにぬねの",
			"category" => "",
		),
	),

	array(
		"title" => "実績",
		"url" => "../mypage/result.html",
		"data" => array(
			"mode" => "form",
			"title" => "実績タイトルあいうえおかきくけこ",
			"contents" => "実績内容あいうえおかきくけこ\nさしすせそたちつてとなにぬねの",
			"category" => "",
		),
	),

	array(
		"title" => "コンサルタント登録",
		"url" => "../register-consultant.html",
		"data" => array(
			"mode" => "form",
			"name" => "名前あいうえおかきくけこさしすせそ",
			"name_kana" => "名前フリガナあいうえおかきくけこさしすせそ",
			"sex" => "1",
			"birthday_year" => "1966",
			"birthday_month" => "11",
			"hometown" => "出身地あいうえおかきくけこさしすせそ",
			"hoby" => "趣味あいうえおかきくけこさしすせそ",
			"keitai" => "1",
			"company" => "会社名あいうえおかきくけこさしすせそ",
			"company_kana" => "会社名フリガナあいうえおかきくけこさしすせそ",
			"member" => "所員数あいうえおかきくけこさしすせそ",
			"open_time" => "営業時間あいうえおかきくけこさしすせそ",
			"holiday" => "土日祝日対応可否あいうえおかきくけこさしすせそ",
			"zip1" => "123",
			"zip2" => "4567",
			"pref" => "12",
			"address" => "残りの住所あいうえおかきくけこさしすせそ",
			"tel" => "123-4567",
			"fax" => "123-4567",
			"email" => "test@crytus.co.jp",
			"email2" => "test@crytus.co.jp",
			"passwd" => "test1234",
			"passwd2" => "test1234",
			"introducer" => "ykuri@crytus.co.jp",
			"message" => "一言メッセージあいうえおかきくけこさしすせそ",
			"title" => "肩書きあいうえおかきくけこさしすせそ",
			"area" => array("1","5","10"),
			"bunrui" => array("2"),
			"bunrui_other" => "分類その他",
			"screening" => array("1"),
			"screening_other" => "資格その他",
			"result" => array("1"),
			"language" => array("1"),
//			"movie" => "";
//			"data1" => "紹介資料1";
//			"data2" => "紹介資料2";
//			"data3" => "紹介資料3";
//			"data4" => "紹介資料4";
//			"data5" => "紹介資料5";
//			"url" => "";
			"career" => "経歴・PRあいうえおかきくけこさしすせそ",
			"reward" => "報酬基準あいうえおかきくけこさしすせそ",
//			"book1" => "著書実績1";
			"book_title1" => "著書実績1タイトルあいうえおかきくけこさしすせそ",
			"book_comment1" => "著書実績1説明あいうえおかきくけこさしすせそ",
//			"book2" => "著書実績2";
			"book_title2" => "著書実績2タイトルあいうえおかきくけこさしすせそ",
			"book_comment2" => "著書実績2説明あいうえおかきくけこさしすせそ",
//			"book3" => "著書実績3";
			"book_title3" => "著書実績3タイトルあいうえおかきくけこさしすせそ",
			"book_comment3" => "著書実績3説明あいうえおかきくけこさしすせそ",
//			"book4" => "著書実績4";
			"book_title4" => "著書実績4タイトルあいうえおかきくけこさしすせそ",
			"book_comment4" => "著書実績4説明あいうえおかきくけこさしすせそ",
//			"book5" => "著書実績5";
			"book_title5" => "著書実績5タイトルあいうえおかきくけこさしすせそ",
			"book_comment5" => "著書実績5説明あいうえおかきくけこさしすせそ",
		),
	),

	array(
		"title" => "一般利用者登録",
		"url" => "../register-user.html",
		"data" => array(
			"mode" => "form",
			"nickname" => "ニックネームあいうえおかきくけこさしすせそ",
			"email" => "test@crytus.co.jp",
			"email2" => "test@crytus.co.jp",
			"passwd" => "test1234",
			"passwd2" => "test1234",
		),
	),

	array(
		"title" => "問合せ（一般）",
		"url" => "../contact.html",
		"data" => array(
			"mode" => "form",
			"kind" => "2",
			"name" => "お名前あいうえおかきくけこ",
			"name_kana" => "フリガナあいうえおかきくけこ",
			"company" => "会社名あいうえおかきくけこ",
			"company_kana" => "フリガナあいうえおかきくけこ",
			"section" => "所属部署あいうえおかきくけこ",
			"zip1" => "197",
			"zip2" => "0802",
			"pref" => "11",
			"address" => "住所あいうえおかきくけこ",
			"email" => "crytus@gmail.com",
			"tel" => "0425501745",
			"contents" => "お問合わせ内容です。\nあいうえおかきくけこ",
		),
	),

	array(
		"title" => "問合せ（コンサルタント）",
		"url" => "../consultant-contact.html&id=1",
		"data" => array(
			"mode" => "form",
			"kind" => "2",
			"name" => "お名前あいうえおかきくけこ",
			"name_kana" => "フリガナあいうえおかきくけこ",
			"company" => "会社名あいうえおかきくけこ",
			"company_kana" => "フリガナあいうえおかきくけこ",
			"section" => "所属部署あいうえおかきくけこ",
			"zip1" => "197",
			"zip2" => "0802",
			"pref" => "14",
			"address" => "住所あいうえおかきくけこ",
			"email" => "test@crytus.co.jp",
			"email2" => "test@crytus.co.jp",
			"tel" => "0425501745",
			"contents" => "お問合わせ内容\nあいうえおかきくけこ",
		),
	),

	array(
		"title" => "仕事依頼",
		"url" => "../register-job.html",
		"data" => array(
			"mode" => "form",
			"kind" => "2",
			"name" => "お名前あいうえおかきくけこ",
			"name_kana" => "お名前あいうえおかきくけこ（フリガナ）",
			"company" => "会社名あいうえおかきくけこ",
			"company_kana" => "会社名あいうえおかきくけこ（フリガナ）",
			"zip1" => "197",
			"zip2" => "0802",
			"pref" => "13",
			"address" => "住所あいうえおかきくけこ",
			"email" => "test2@crytus.co.jp",
			"email2" => "test2@crytus.co.jp",
			"tel" => "0425501745",
			"title" => "タイトルあいうえおかきくけこ",
			"category" => "",
			"limit_date_year" => "2011",
			"limit_date_month" => "7",
			"limit_date_day" => "15",
			"yosan_low" => "12345",
			"yosan_high" => "23456",
			"comment" => "詳細あいうえおかきくけこ\nあいうえおかきくけこです。",
			"point" => "ポイントあいうえおかきくけこ\nあいうえおかきくけこです。",
		),
	),


	array(
		"title" => "一般ユーザーログイン",
		"url" => "../login.html",
		"data" => array(
			"mode" => "form",
			"email" => "test1@crytus.co.jp",
			"passwd" => "test1234",
		),
	),
);
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="ja" lang="ja">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<title>テストデータ投入</title>
</head>
<body>
<?php foreach ($data as $val) { ?>
<form method="post" action="<?php echo $val["url"];?>">
<?php foreach ($val["data"] as $id => $val2) { ?>
<input type="hidden" name="<?php echo $id;?>" value="<?php echo $val2;?>">
<?php } ?>
<input type="submit" value="<?php echo $val["title"];?>">
</form>
<?php } ?>
</body>
</html>
