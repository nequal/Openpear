<?php
Rhaco::import("tag.model.TemplateFormatter");
Rhaco::import("tag.model.SimpleTag");
Rhaco::import("network.http.Browser");
Rhaco::import("network.http.Http");

/**
 * OpenID auth
 * vim: noexpandtab
 *
 * OpenID2.0 Auth
 * PHP5 Only
 * require OpenSSL extension
 *
 * @author Takuya Sato <nazo at highfreq dot net>
 * @license New BSD License
 */

class OpenIDAuth
{
	private $browser;
	private $server;
	private $xrds;
	private $optional_headers = array();
	private $immediate = false;
	private $assoc_handle = null;
	private $version = '2.0';
	private $endpoint = '';

	private $_session_namespace = 'arbo_openid_session';

	public function __construct($server = '') {
		$this->server = $server;

		$this->browser = new Browser();
	}
	
	protected function parseResponseHeader($header_text) {
		$ret = array();
		$headers = explode("\n", $header_text);
		foreach($headers as $header) {
			if (preg_match("/^([^:]+) *: *(.*)$/", $header, $matches)) {
				$ret[$matches[1]] = trim($matches[2]);
			}
		}
		return $ret;
	}

	public function request() {
		if (preg_match('/^https?:\/\//', $this->server)) {
			$url = $this->discover_url();
		} else {
			$url = $this->discover_domain();
		}

		if ($url === false) { return false; }

		if ($this->version == '2.0') {
			$this->xrds = $this->parseXRDS($url);
			if ($this->xrds === false) { return false; }
			$this->endpoint = $this->xrds['URI'];
		} else {
			$this->endpoint = $url;
		}
		$assoc = $this->getAssociate();
		$_SESSION[$this->_session_namespace.'mac_key'] = base64_decode($assoc['mac_key']);
		$_SESSION[$this->_session_namespace.'mac_expire'] = time() + intval($assoc['expires_in']);
		$this->assoc_handle = $assoc['assoc_handle'];
		return true;
	}

	public function getEndPointURL() {
		return $this->endpoint;
	}

	public function addParameter($key, $value) {
		$this->optional_headers[$key] = $value;
	}

	public function setImmediate($immediate = true) {
		$this->immediate = $immediate;
	}

	public function getEndPointHeaders($realm, $return_to) {
		if ($this->version == '1.1') {
			return $this->getEndPointHeaders_11($realm, $return_to);
		} else {
			return $this->getEndPointHeaders_20($realm, $return_to);
		}
	}

	public function getEndPointHeaders_11($realm, $return_to) {
		$headers = array();
		$headers['openid.ns'] = 'http://specs.openid.net/auth/1.1';
		$headers['openid.trust_root'] = $realm;
		if ($this->immediate) {
			$headers['openid.mode'] = 'check_immediate';
		} else {
			$headers['openid.mode'] = 'checkid_setup';
		}
		$headers['openid.return_to'] = $return_to;
		$headers['openid.identity'] = $this->server;
		
		$headers += $this->optional_headers;
		$headers['openid.assoc_handle'] = $this->assoc_handle;
		
		return $headers;
	}

	public function getEndPointHeaders_20($realm, $return_to) {
		$headers = array();
		$headers['openid.ns'] = 'http://specs.openid.net/auth/2.0';
		foreach($this->xrds['Type'] as $v) {
			if (preg_match('/\/extensions\/([a-zA-Z0-9_-]+)\/([0-9.]+)$/', $v, $matches)) {
				$headers['openid.ns.'.$matches[1]] = $v;
			}
		}
		$headers['openid.realm'] = $realm;
		if ($this->immediate) {
			$headers['openid.mode'] = 'check_immediate';
		} else {
			$headers['openid.mode'] = 'checkid_setup';
		}
		$headers['openid.return_to'] = $return_to;
		$headers['openid.sreg.required'] = 'nickname';
		$headers['openid.identity'] = 'http://specs.openid.net/auth/2.0/identifier_select';
		$headers['openid.claimed_id'] = 'http://specs.openid.net/auth/2.0/identifier_select';
		
		$headers += $this->optional_headers;
		$headers['openid.assoc_handle'] = $this->assoc_handle;
		
		return $headers;
	}

	public function getAssociateHeaders() {
		$headers = array();
		$headers['openid.ns'] = 'http://specs.openid.net/auth/2.0';
		$headers['openid.assoc_type'] = 'HMAC-SHA1';
		$headers['openid.session_type'] = 'no-encryption';
		$headers['openid.mode'] = 'associate';
		
		return $headers;
	}

	public function getAssociate() {
		$raw = $this->browser->post($this->getEndPointURL(), $this->getAssociateHeaders());

		return $this->parseResponseHeader($raw);
	}

	protected function parseXRDS($url) {
		$ret = array();

		$response = $this->browser->get($url);
		$pTag = new SimpleTag();
		$pTag->set($response, "xrd");
		$service = $pTag->getIn('Service');
		if (count($service) == 0) { return false; }

		$types = $service[0]->getIn('Type');
		$ret['Type'] = array();
		foreach($types as $type) {
			$ret['Type'][] = $type->getValue();
		}

		$uri = $service[0]->getIn('URI');
		if (count($uri) == 0) { return false; }
		$ret['URI'] = $uri[0]->getValue();

		return $ret;
	}

	protected function discover_url() {
		list($head, $response) = $this->discover_http($this->server, 'GET');
		$headers = $this->parseResponseHeader($head);
		if (isset($headers['X-XRDS-Location'])) {
			$this->version = '2.0';
			return $headers['X-XRDS-Location'];
		}

		$this->version = '1.1';
		$pTag = new SimpleTag();
		$pTag->set($response, "html");
		$head = $pTag->getIn('head');
		if (count($head) == 0) { return false; }
		$link = $pTag->getIn('link');
		foreach ($link as $l) {
			$rel = $l->param('rel');
			if ($rel == 'openid.server') {
				$url = $l->param('href');
				if (!empty($url)) {
					return $url;
				}
			}
		}

		return false;
	}

	protected function discover_domain() {
		list($header_text, $response) = $this->discover_http('https://'.$this->server, 'HEAD');
		if ($header_text == null) {
			// httpsが駄目ならhttp
			list($header_text, $response) = $this->discover_http('http://'.$this->server, 'HEAD');
		}
		$headers = $this->parseResponseHeader($header_text);
		if (isset($headers['X-XRDS-Location'])) {
			$this->version = '2.0';
			return $headers['X-XRDS-Location'];
		}

		return false;
	}

	public function validate($variables) {
		if (!isset($_SESSION[$this->_session_namespace.'mac_expire'])) {
			return false;
		}
		if (!isset($_SESSION[$this->_session_namespace.'mac_key'])) {
			return false;
		}
		if ($_SESSION[$this->_session_namespace.'mac_expire'] < time()) {
			return false;
		}
		$signes = explode(",", $variables['openid_signed']);
		$headers = array();
		foreach($signes as $s) {
			$php_sign = str_replace('.', '_', $s);
			if (!isset($variables['openid_'.$php_sign])) {
				return false;
			}
			$headers[$s] = $variables['openid_'.$php_sign];
		}
		$enc = $this->keyValueFormEncoding($headers);
		$enc = base64_encode($this->hmacsha1($_SESSION[$this->_session_namespace.'mac_key'], $enc, true));
		if ($enc != $variables['openid_sig']) {
			return false;
		}
		unset($_SESSION[$this->_session_namespace.'mac_key']);
		unset($_SESSION[$this->_session_namespace.'mac_expire']);

		return true;
	}

	private function keyValueFormEncoding($array) {
		$ret = '';
		foreach($array as $key=>$value) {
			$ret .= $key.':'.$value."\n";
		}
		return $ret;
	}

	private function discover_http($url, $method) {
		$headers = array("Accept"=>"application/xrds+xml");

		list($head,$body) = Http::request($url,$method,$headers);

		if(!empty($head)){
			$this->status = Http::parseStatus($head);
			
			if($this->status != 200){
				if(Http::isRedirect($redirectUrl,$this->status,$head)){
					return $this->discover_http($redirectUrl,$method);
				}
				return null;
			}
		}

		return array($head, $body);
	}

	/**
	 * http://www.php.net/manual/en/function.sha1.php#39492
	 * @author mark at dot BANSPAM dot pronexus dot nl
	 * @license Creative Commons Attribution License
	 */
	//Calculate HMAC-SHA1 according to RFC2104
	// http://www.ietf.org/rfc/rfc2104.txt
	function hmacsha1($key,$data,$binary=false) {
		$blocksize=64;
		$hashfunc='sha1';
		if (strlen($key)>$blocksize)
			$key=pack('H*', $hashfunc($key));
		$key=str_pad($key,$blocksize,chr(0x00));
		$ipad=str_repeat(chr(0x36),$blocksize);
		$opad=str_repeat(chr(0x5c),$blocksize);
		$hmac = pack(
			'H*',$hashfunc(
				($key^$opad).pack(
					'H*',$hashfunc(
						($key^$ipad).$data
					)
				)
			)
		);
		if ($binary) {
			return $hmac;
		}
		return bin2hex($hmac);
	}

}

