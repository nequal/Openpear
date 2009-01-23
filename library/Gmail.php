<?php
Rhaco::import("network.mail.Mail");
Rhaco::import("util.Logger");
/**
 * gmailでメール送信を行う
 * tls://を利用にするためOpenSSL サポートを有効にしてある必要がある
 * 
 * @author Kazutaka Tokushima
 * @license New BSD License
 * @copyright Copyright 2008 rhaco project. All rights reserved.
 *
 */
class Gmail extends Mail{
	var $login;
	var $password;
	var $error;

	function Gmail($login="",$password=""){
		$this->login = $login;
		$this->password = $password;
		parent::Mail($login);
	}
	
	function _talk(&$fp,$message){
		fputs($fp,$message."\r\n");
		return $this->_is(fgets($fp,4096));
	}
	function _is($msg){
		list($code) = explode(" ",$msg);
		Logger::deep_debug($msg);

		switch($code){
			case 502:
			case 530:
			case 550:
			case 555:
				Logger::warning($msg);
				$this->error = $msg;
				return false; // アクセス拒否
			case 235: 
			case 250: // OK
			case 334: // レスポンス待ち
			case 354: // 入力の開始
			case 221: // 転送チャンネルを閉じる
				return true;
		}
	}
	
	/**
	 * 最後のエラーメッセージを取得する
	 */
	function error(){
		return $this->error;
	}
	
	/**
	 * メールを送信する
	 */
	function send($subject="",$message=""){
		if(!empty($subject)) $this->subject($subject);
		if(!empty($message)) $this->message($message);

		$rtn = false;
		$fp = fsockopen("tls://smtp.gmail.com",465,$errno,$errstr,30);

		if($fp){
			if("" == fgets($fp,4096)) return false;

			$this->_talk($fp,"HELO ".$_SERVER["REMOTE_ADDR"]);
			$this->_talk($fp,"AUTH LOGIN");
			$this->_talk($fp,base64_encode($this->login));
			$this->_talk($fp,base64_encode($this->password));

			$this->_talk($fp,sprintf("MAIL FROM: <%s>",$this->login));
			foreach(array_keys($this->to) as $to){
				$this->_talk($fp,sprintf("RCPT TO: <%s>",$to));
			}
			$this->_talk($fp,"DATA");
			$this->_talk($fp,$this->manuscript().".");
			$rtn = $this->_talk($fp,"QUIT");
			fclose($fp);
		}
		return $rtn;
	}
}
?>