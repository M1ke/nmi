<?php
class Nmi3Step {
	protected $api_key;
	protected $url='https://secure.networkmerchants.com/api/v2/three-step';
	protected $redirect='';
	protected $debug=false;

	function __construct($api_key,$redirect=null){
		$this->api_key=$api_key;
		$this->set_redirect($redirect);
		return this;
	}

	function set_debug($debug=false){
		$this->debug=$debug;
		return $this;
	}

	function set_redirect($redirect=null){
		$this->redirect=!empty($redirect) ? $redirect : 'http://'.$_SERVER['HTTP_HOST'].$_SERVER['PHP_SELF'];
		return $this;
	}

	function get_url($amount){
		if (!is_numeric($amount)){
			throw new InvalidArgumentException('You must enter a valid amount.');
		}
		$request=[
			'sale'=>[
				'api-key'=>$this->api_key,
				'redirect-url'=>$this->redirect,
				'amount'=>$amount,
			],
		];

		$result=$this->request_xml($request);

		$form_url=$result->{'form-url'};

		if (empty($form_url)){
			throw new Exception('No form URL was returned.');
		}

		return $form_url;
	}

	function submit_payment($token){
		if (empty($token)){
			throw new InvalidArgumentException('No token was supplied.');
		}
		$request=[
			'complete-action'=>[
				'api-key'=>$this->api_key,
				'token-id'=>$token,
			],
		];

		$result=$this->request_xml($request);

		switch ($result->result){
			case 1:
				// transaction has succeeded, no exception needed
			break;
			case 2:
				throw new NmiException('Payment was declined. The gateway also reported "'.$result->{'result-text'}.'".');
			break;
			default:
				throw new NmiException('An error occurred with processing. The gateway also reported "'.$result->{'result-text'}.'".');
			break;
		}
		return $result;
	}

	private function request_xml($request){
		$request_xml=self::array_to_xml($request);
		if ($this->debug){
			var_dump($request_xml);
		}

		$result_xml=self::http_stream($this->url,$request_xml,'xml');
		if ($this->debug){
			var_dump($result_xml);
		}
		$result=new SimpleXMLElement($result_xml);
		if ($this->debug){
			var_dump($result);
		}
		return $result;
	}

	// These utility classes are made accessible as static components and should be substituted with components from a common library when possible

	static final function array_to_xml($arr,$top_key=null){
	    if (empty($top_key)){
	        $top_key=array_keys($arr);
	        $top_key=$top_key[0];
	        $arr=$arr[$top_key];
	    }
	    // creating object of SimpleXMLElement
	    $xml_arr=new SimpleXMLElement("<?xml version=\"1.0\"?><$top_key></$top_key>");

	    // function call to convert array to xml
	    self::array_to_xml_loop($arr,$xml_arr);

	    return $xml_arr->asXML();
	}
	static final function array_to_xml_loop($arr,&$xml_arr){
	    foreach ($arr as $key => $value){
	        if (is_array($value)){
	            if (!is_numeric($key)){
	                $subnode = $xml_arr->addChild("$key");
	                self::array_to_xml_loop($value, $subnode);
	            }
	            else {
	                self::array_to_xml_loop($value,$xml_arr);
	            }
	        }
	        else {
	            $xml_arr->addChild("$key","$value");
	        }
	    }
	}

	static final function http_stream($url,$data,$content='post'){
		switch ($content){
			case 'post':
				$data=http_build_query($data);
				$content_type="application/x-www-form-urlencoded";
			break;
			case 'xml':
				$content_type='text/xml';
			break;
		}
		$options=array(
			'http'=>array(
				'header'=>"Content-type: $content_type\r\n",
				'method'=>'POST',
				'content'=>$data,
			),
		);
		$context=stream_context_create($options);
		$result=file_get_contents($url,false,$context);
		return $result;
	}
}

class NmiException extends Exception {

}