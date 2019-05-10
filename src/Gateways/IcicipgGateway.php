<?php namespace Softon\Indipay\Gateways;

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\View;
use Softon\Indipay\Exceptions\IndipayParametersMissingException;

class IcicipgGateway implements PaymentGatewayInterface {

    protected $parameters = array();
    protected $testMode = false;
    protected $keyId = '';
    protected $keySecret = '';
    protected $hash = '';
    protected $liveEndPoint = 'https://www4.ipg-online.com/connect/gateway/processing';
    protected $testEndPoint = 'https://test.ipg-online.com/connect/gateway/processing';
    public $response = '';

    function __construct()
    {
        $this->keyId = Config::get('indipay.icicipg.keyId');
        $this->keySecret = Config::get('indipay.icicipg.keySecret');
        $this->testMode = Config::get('indipay.testMode');
        
        $this->parameters['storename'] = $this->keyId;
        $this->parameters['sharedsecret'] = $this->keySecret;
        $this->parameters['responseSuccessURL'] = secure_url(Config::get('indipay.icicipg.returnUrl'));
        $this->parameters['responseFailURL'] = secure_url(Config::get('indipay.icicipg.cancelUrl'));
        $this->parameters['oid'] = $this->generateTransactionID();
        $this->parameters['txndatetime'] = $this->getDateTime() ;
        
        $this->parameters['timezone'] = Config::get('indipay.icicipg.timezone');;
        $this->parameters['txntype'] = Config::get('indipay.icicipg.txntype');
        $this->parameters['authenticateTransaction'] = Config::get('indipay.icicipg.authenticateTransaction');
        $this->parameters['currency'] = Config::get('indipay.icicipg.currency');
        $this->parameters['mode'] = Config::get('indipay.icicipg.mode');
         
        

    }

    public function getEndPoint()
    {
        return $this->testMode?$this->testEndPoint:$this->liveEndPoint;
    }

    public function request($parameters)
    {
        $this->parameters = array_merge($this->parameters,$parameters);
        Log::info($this->parameters);
        $this->checkParameters($this->parameters);

        $this->createHash(); 

        return $this;

    }

    /**
     * @return mixed
     */
    public function send()
    {

        Log::info('Indipay Payment Request Initiated: for ICICI ' . $this->hash) ;

        return View::make('indipay::icicipg')->with('hash',$this->hash)
                             ->with('parameters',$this->parameters)
                             ->with('endPoint',$this->getEndPoint());

    }


    /**
     * Check Response
     * @param $request
     * @return array
     */
    public function response($request)
    {
        $paymentId = $request['endpointTransactionId'];
        $transactionStatus = substr($request['approval_code'],0,1) == "Y" ? "success" : "error";
        $response = $request->all();

        $response = json_decode(json_encode($response), true);
        $this->response = array_merge($request->all(), ["pgPaymentId" => $paymentId, 
                                                        "transactionStatus"=>$transactionStatus,
                                                        "productinfo" => $request['customParam_productinfo'],
                                                        "firstname" => $request['customParam_firstname'],
                                                        "phone" => $request['customParam_phone'],
                                                        "udf1" => $request['customParam_udf1'],
                                                        "udf2" => $request['customParam_udf2'],
                                                        "amount" => $request['chargetotal']]);

                                      
                                                       
        // $response_hash = $this->decrypt($response);

        // if($response_hash!=$response['hash']){
        //     return 'Hash Mismatch Error';
        // }

        return $this->response;
    }


    /**
     * @param $parameters
     * @throws IndipayParametersMissingException
     */
    public function checkParameters($parameters)
    {
        $validator = Validator::make($parameters, [
            'storename' => 'required',
            'sharedsecret' => 'required',
            'responseSuccessURL' => 'required|url',
            'responseFailURL' => 'required|url',
            'email' => 'required',
            'amount' => 'required|numeric'
        ]);

        if ($validator->fails()) {
            throw new IndipayParametersMissingException;
        }

    }

    

    /**
     * PayUMoney Decrypt Function
     *
     * @param $plainText
     * @param $key
     * @return string
     */
    protected function decrypt($response)
    {

        $hashSequence = "status||||||udf5|udf4|udf3|udf2|udf1|email|firstname|productinfo|amount|txnid|key";
        $hashVarsSeq = explode('|', $hashSequence);
        $hash_string = $this->salt."|";

        foreach($hashVarsSeq as $hash_var) {
            $hash_string .= isset($response[$hash_var]) ? $response[$hash_var] : '';
            $hash_string .= '|';
        }

        $hash_string = trim($hash_string,'|');

        return strtolower(hash('sha512', $hash_string));
    }



    public function generateTransactionID()
    {
        return substr(hash('sha256', mt_rand() . microtime()), 0, 20);
    }
    // icici Payment Gateway Changes 
    function getDateTime() {
        date_default_timezone_set('Asia/Kolkata');
        return date('Y:m:d-H:i:s');;
	
    }
    protected function createHash() {
		
      $this->hash = '';
        
		  $stringToHash = $this->parameters['storename'].$this->parameters['txndatetime'].$this->parameters['amount'].$this->parameters['currency'].$this->parameters['sharedsecret'];
		
		  $ascii = bin2hex($stringToHash);

      $this->hash = sha1($ascii);
        
		
	  }


}