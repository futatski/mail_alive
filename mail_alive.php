<?php 

require("LC_Mail_Alive.php");

if(count($argv) < 3) {
    echo "引数が不正です:" . $argv . ":\n";
    exit(-1);
} 

$to_mail_adr = $argv[1]; // チェックしたいメールアドレス
$from_mail_adr = $argv[2]; // 送信側メールアドレス
if(count($argv) >= 4 && $argv[3] != "") {
    $dbg_mode = true;
}
else {
    $dbg_mode = false;
}

if($dbg_mode) {
    @var_dump($argv);
}

$obj = new MaileAlive();
$obj->init($from_mail_adr, $to_mail_adr, $dbg_mode);
$response_code = $obj->process();

// 画面にフォーマットを出力
print($response_code)

?>
