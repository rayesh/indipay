<?php namespace Softon\Indipay\Gateways;

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\View;
use Softon\Indipay\Exceptions\IndipayParametersMissingException;

class RazorpayGateway implements PaymentGatewayInterface {

    protected $parameters = array();
    protected $testMode = false;
    protected $keyId = '';
    protected $keySecret = '';
    protected $liveEndPoint = 'https://api.razorpay.com/v1/';
    protected $testEndPoint = 'https://api.razorpay.com/v1/';
    public $response = '';

    function __construct()
    {
        $this->keyId = Config::get('indipay.razorpay.keyId');
        $this->keySecret = Config::get('indipay.razorpay.keySecret');
        $this->testMode = Config::get('indipay.testMode');
        $this->parameters['key_id'] = Config::get('indipay.razorpay.keyId');
        $this->parameters['redirect_url'] = url(Config::get('indipay.razorpay.returnUrl'));
        $this->parameters['cancel_url'] = url(Config::get('indipay.razorpay.cancelUrl'));
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

        Log::info('Indipay Payment Request Initiated: ');
        //Razorpay expects amount to be in Paisa. Convert value to Paisa.
        $amount = ((float)$this->parameters['amount']) * 100;
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
        //Validate the response
        $client = new \GuzzleHttp\Client();
        $response = $client->post($this->getEndPoint('payments/'.$paymentId),
                                        [
                                            'auth' => [$this->keyId, $this->keySecret]
                                        ])->getBody()->getContents();
        $response = json_decode($response);

        if($response->success){
            $this->response = $response;
            $this->response->razorpay_payment_id = $paymentId;
        }
        Log::info("Response is ", [$response]);
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
            Log::error("Validation failed", $validator->failed());
            throw new IndipayParametersMissingException(json_encode($validator->failed()));
        }

    }
}
