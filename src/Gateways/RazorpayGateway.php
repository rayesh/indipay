<?php namespace Softon\Indipay\Gateways;

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\View;
use Softon\Indipay\Exceptions\IndipayParametersMissingException;
use Softon\Indipay\PaymentGatewayHelperInterface;

class RazorpayGateway implements PaymentGatewayInterface {

    protected $parameters = array();
    protected $testMode = false;
    protected $keyId = '';
    protected $keySecret = '';
    protected $liveEndPoint = 'https://api.razorpay.com/v1/';
    protected $testEndPoint = 'https://api.razorpay.com/v1/';
    public $response = array();
    protected $paymentGatewayTransLog;

    function __construct(PaymentGatewayHelperInterface $paymentGatewayTransLog)
    {
        $this->paymentGatewayTransLog = $paymentGatewayTransLog;

        $configfromenv = 	Config::get('indipay.configfromenv');

        if(!$configfromenv){
            $this->keyId = Config::get('PG_KEY_ID');
            $this->keySecret = Config::get('PG_KEY_SECRET');
            $this->testMode =  Config::get('PG_TESTMODE');
           
            $this->parameters['key_id'] = $this->keyId;
            $this->parameters['redirect_url'] = secure_url(Config::get('PG_REDIRECT_URL'));
            $this->parameters['cancel_url'] = secure_url(Config::get("PG_CANCEL_URL"));
        }else{

        
            $this->keyId = Config::get('indipay.razorpay.keyId');
            $this->keySecret = Config::get('indipay.razorpay.keySecret');
            $this->testMode = Config::get('indipay.testMode');
            $this->parameters['key_id'] = Config::get('indipay.razorpay.keyId');
            $this->parameters['redirect_url'] = secure_url(Config::get('indipay.razorpay.returnUrl'));
            $this->parameters['cancel_url'] = secure_url(Config::get('indipay.razorpay.cancelUrl'));
        }
    
        $this->parameters['txnid'] = $this->generateTransactionID();
    }

    public function getEndPoint()
    {
        return $this->testMode?$this->testEndPoint:$this->liveEndPoint;
    }

    public function request($parameters)
    {
        $this->parameters = array_merge($this->parameters,$parameters);
        $this->checkParameters($this->parameters);
        return $this;

    }

    /**
     * @return mixed
     */
    public function send()
    {

        Log::debug('Indipay Payment Request Initiated: ');
        //Razorpay expects amount to be in Paisa. Convert value to Paisa.
        $amount = ((float)$this->parameters['amount']) * 100;

        
        $this->paymentGatewayTransLog->paymentGatewayTransactionLogging($this->parameters,$this->parameters["txnid"]);

        return View::make('indipay::razorpay')
                             ->with('keyId',$this->keyId)
                             ->with('amount', $amount)
                             ->with('parameters',$this->parameters);

    }


    /**
     * Check Response
     * @param $request
     * @return array
     */
    public function response($request)
    {
        $paymentId = $request['razorpay_payment_id'];
        if(!empty($paymentId)){
            //Validate the response
            $client = new \GuzzleHttp\Client();
            try{
                $response = $client->get($this->getEndPoint().'payments/'.$paymentId,
                                                [
                                                    'auth' => [$this->keyId, $this->keySecret]
                                                ]);

                if($response->getStatusCode() == 200){
                    $response = json_decode($response->getBody()->getContents());
                    $response->razorpay_payment_id = $paymentId;
                    $response->amount = ((float)$response->amount)/100;
                    //Convert to array
                    $response = json_decode(json_encode($response), true);
                    $this->response = array_merge($request->all(), $response);
                }
            }catch(\Exception $ex){
                Log::error("Exception",[$ex]);
            }
        }else{
            $this->response = array_merge($request->all(), ["razorpay_payment_id" => "", "status"=>"error", "error_code"=>"500"]);
        }
        $commonStatus			=	isset($this->response['error_code']) ? $this->response['error_code'] : "success";
        $transactionPaymentId   =	$this->response['razorpay_payment_id'];
        
        $bookingId = $this->paymentGatewayTransLog->getBookingId( $this->response['txnid']);

        $this->response = array_merge( $this->response,[ "commonStatus" => $commonStatus,
                                                        "transactionPaymentId" => $transactionPaymentId,
                                                        "pgBookingId" => $bookingId]); 
        $this->paymentGatewayTransLog->populateRequestResponse($this->response);
        return $this->response;
    }


    /**
     * @param $parameters
     * @throws IndipayParametersMissingException
     */
    public function checkParameters($parameters)
    {
        $validator = Validator::make($parameters, [
            'key_id' => 'required',
            'amount' => 'required|numeric',            
            'merchant_name' => 'required',
            'redirect_url' => 'required|url',
            'image'  => 'url'
        ]);

        if ($validator->fails()) {
            Log::debug("Validation failed", $validator->failed());
            throw new IndipayParametersMissingException(json_encode($validator->failed()));
        }

    }
    public function generateTransactionID()
    {
        return substr(hash('sha256', mt_rand() . microtime()), 0, 20);
    }
    

}
