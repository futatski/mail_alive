<?php 

/**
 * MaileAlive
 */
class MaileAlive  {

    public $to_server_port;
    public $to_server_url;
    public $to_mail_adr;
    public $from_mail_adr;
    public $dbg_mode;
    
    /**
     * init
     *
     * @param  mixed $send_mail_adr
     * @param  mixed $to_mail_adr
     * @param  mixed $dbg_mode
     * @return void
     */
    function init($send_mail_adr, $to_mail_adr, $dbg_mode=false){
        $this->to_mail_adr = $to_mail_adr;
        $this->from_mail_adr = $send_mail_adr;
        $this->dbg_mode = $dbg_mode;
    }
    
    /**
     * process
     *
     * @return void
     */
    function process() {
        // メールサーバーを取得
        $this->to_server_url = $this->get_to_server_url($this->to_mail_adr);
        $this->dbg_print("to server_url:" . $this->to_server_url);
        if($this->to_server_url == -1) {
            return -1;
        }

        // ポート番号取得
        $this->to_server_port = $this->get_to_server_port($this->to_mail_adr);;

        // ソケットオープン
        $sock = fsockopen($this->to_server_url, $this->to_server_port);
        if($sock == false){
            return -1;
        }

        $result = $this->get_socket($sock);
        $this->dbg_print( 'result: socketopen:' . $result );

        // EHLO
        $this->put_socket($sock,"EHLO $this->to_server_url");
        $result = $this->get_socket($sock);
        $this->dbg_print( 'result: helo1:' . $result );

        // セッションがSTARTTLSなら専用のハンドリングを実施
        if($this->to_server_port == 587) {
            $this->put_socket($sock,"STARTTLS");
            $result = $this->get_socket($sock);
            $this->dbg_print('result: starttls:' . $result );

            // if(false == stream_socket_enable_crypto($sock, true, STREAM_CRYPTO_METHOD_TLS_CLIENT)){
            //     // fclose($smtp); // unsure if you need to close as I haven't run into a security fail at this point
            //     die("unable to start tls encryption");
            // }
            // re helo
            $this->put_socket($sock,"EHLO  $this->to_server_url");
            $result = $this->get_socket($sock);
            $this->dbg_print( 'result: helo2:' . $result );
        }

        // mail from
        $this->put_socket($sock,"MAIL FROM:<$this->from_mail_adr>");
        $result = $this->get_socket($sock);
        $this->dbg_print( 'result: from:' . $result);

        // mail to
        $this->put_socket($sock,"RCPT TO:<$this->to_mail_adr>");
        $result = $this->get_socket($sock);
        $this->dbg_print('result: rept to:' . $result);

        $user_known_msg = $result;

        $this->put_socket($sock,"QUIT");
        $result = $this->get_socket($sock);
        $this->dbg_print('result: QUIT:' . $result);

        // レスポンスを取得して返却
//        $user_known_response_code = substr($user_known_msg, 0, 3 );
        $user_known_response_code = $user_known_msg;

        return $user_known_response_code;

    }
    
    /**
     * get_socket
     *
     * @param  mixed $socket
     * @param  mixed $length
     * @return void
     */
    function get_socket($socket,$length=1024){
        $send = '';
        $sr = fgets($socket,$length);
        while( $sr ){
            $send .= $sr;
            if( $sr[3] != '-' ){ break; }
            $sr = fgets($socket,$length);
        }
        return $send;
    }
    
    /**
     * put_socket
     *
     * @param  mixed $socket
     * @param  mixed $cmd
     * @param  mixed $length
     * @return void
     */
    function put_socket($socket,$cmd,$length=1024){
        fputs($socket,$cmd."\r\n",$length);
    }
    
    /**
     * get_to_server_url
     *
     * @param  mixed $email
     * @return void
     */
    function get_to_server_url($email) {
        $url = '';
        $domain = substr($email, strrpos($email, '@') + 1);

        $_server_url = '';
        switch ($domain) {
            // case 'gmail.com':
            //     # code...
            //     $_server_url = 'smtp.gmail.com';
            //      break;

            default:
                # code...
                $_server_url = $this->get_to_mx_record($domain);
                break;
        }

        return $_server_url;
    }
    
    /**
     * get_to_mx_record
     *
     * @param  mixed $domain
     * @return void
     */
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
        if(count($mxhosts) == 0) {
            return -1;
        }

        return $mxhosts[0];
    }
    
    /**
     * get_to_server_port
     *
     * @param  mixed $email
     * @return void
     */
    function get_to_server_port($email) {
        $port = 0;
        $domain = substr($email, strrpos($email, '@') + 1);

        $_server_url = '';
        switch ($domain) {
            case 'gmail.com':
                $port = 25;
                 break;

            default:
                $port = 25;
                break;
        }

        return $port;
    }
    
    /**
     * dbg_print
     *
     * @param  mixed $str
     * @return void
     */
    function dbg_print($str) {
        if($this->dbg_mode)
            print('dbg: ' . $str . "\n");
    }
}

?>
