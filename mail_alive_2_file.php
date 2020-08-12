<?php 

require("LC_Mail_Alive.php");

if(count($argv) < 2) {
    echo "引数が不正です:" . $argv . ":\n";
    exit(-1);
} 

$csv_name = $argv[1]; // 対象となるCSV

if(count($argv) >= 3 && $argv[2] != "") {
    $dbg_mode = true;
}
else {
    $dbg_mode = false;
}

if($dbg_mode) {
    @var_dump($argv);
}

$read_data = array();

$row = 1;
if (($handle = fopen($csv_name, "r")) !== FALSE) {
    // 1行ずつfgetcsv()関数を使って読み込む
    while (($data = fgetcsv($handle))) {
        $read_data[$row] = $data;
        if($dbg_mode) {
            print("read:" . $row . ":" . $read_data[$row][0] . "," . $read_data[$row][1]);
        }
        $row++;
        if($dbg_mode) {
    print("--------------->" . $row . "read data.\n");
}
    }
    fclose($handle);
}

if($dbg_mode) {
    print("--------------->" . $row . "read data.\n");
}

$obj = new MaileAlive();
foreach ($read_data as $key => $value) {
    $from_mail_adr = $value[1];
    $obj->init("test@example.com",$from_mail_adr, false);
    $response_code = $obj->process();

    // 画面にフォーマットを出力
    print($value[0] . "," . $value[1] . "," . $response_code . "\n");
}

print('finished.........................\n')



?>
