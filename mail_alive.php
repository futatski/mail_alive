<?php 

class MaileAlive  {

    static $to_server_port;
    static $to_server_url;
    static $to_mail_adr;
    static $from_mail_adr;
    static $dbg_mode;

    function init($send_mail_adr, $to_mail_adr, $dbg_mode=false){
        $this->to_mail_adr = $to_mail_adr;
        $this->from_mail_adr = $send_mail_adr;
        $this->dbg_mode = $dbg_mode;
    }

    function process() {
        // メールサーバーを取得
        $this->to_server_url = $this->get_to_server_url($this->to_mail_adr);
        $this->dbg_print("to server_url:" . $this->to_server_url);

        // ポート番号取得
        $this->to_server_port = 587;

        // ソケットオープン
        $sock = fsockopen($this->to_server_url, $this->to_server_port);

        // helo
        fputs($sock,"EHLO $this->to_server_url\r\n");
        $result = fgets($sock,512);
        $this->dbg_print( 'result: helo:' . $result );

        // セッションがSTARTTLSなら専用のハンドリングを実施
        if($this->to_server_port == 587) {
            fputs($sock,"STARTTLS \r\n");
            $result = fgets($sock,512);
            $this->dbg_print('result: starttls:' . $result );

            // re helo
            fputs($sock,"EHLO  $this->to_server_url\r\n");
            $result = fgets($sock,512);
            $this->dbg_print( 'result: helo:' . $result );
        }

        // mail from
        fputs($sock,"MAIL FROM:<$this->from_mail_adr>\r\n");
        $result = fgets($sock,512);
        $this->dbg_print( 'result: from:' . $result);

        // mail to
        fputs($sock,"RCPT TO:<$this->to_mail_adr>\r\n");
        $result = fgets($sock,512);
        $this->dbg_print('result: rept to:' . $result);

        // レスポンスを取得して返却
        $user_known_response_code = $result;

        return $user_known_response_code;

    }

    function get_to_server_url($email) {
        $url = '';
        $domain = substr($email, strrpos($email, '@') + 1);

        $_server_url = '';
        switch ($domain) {
            case 'gmail.com':
                # code...
                $_server_url = 'smtp.gmail.com';
                break;

            default:
                # code...
                $_server_url = $this->get_to_mx_record($domain);
                break;
        }

        return $_server_url;
    }

    function get_to_mx_record($domain) {
        $mxhosts = array();
        $checkDomain = getmxrr($domain, $mxhosts);
        if (!empty($mxhosts) && strpos($mxhosts[0], 'hostnamedoesnotexist')) {
            array_shift($mxhosts);
        }
        if (!$checkDomain || empty($mxhosts)) {
            $dns = @dns_get_record($domain, DNS_A);
            if (empty($dns)) {
                return -1;
            }
        }
        return $mxhosts[0];
    }

    function get_to_server_port() {
        $port = 0;
        return $port;
    }

    function dbg_print($str) {
        if($this->dbg_mode)
            print('dbg: ' . $str . "\n");
    }
}

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
$obj->process();

?>
