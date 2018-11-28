<?php namespace Softon\Indipay\Gateways;

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\View;
use Softon\Indipay\Exceptions\IndipayParametersMissingException;

class RazorpayGateway implements PaymentGatewayInterface {

    protected $parameters = array();
    protected $merchantData = '';
    protected $encRequest = '';
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
    }

    public function getEndPoint()
    {
        return $this->testMode?$this->testEndPoint:$this->liveEndPoint;
    }

    public function request($parameters)
    {
        $this->parameters = array_merge($this->parameters,$parameters);

        $this->checkParameters($this->parameters);

        foreach($this->parameters as $key=>$value) {
            $this->merchantData .= $key.'='.$value.'&';
        }

        $this->encRequest = $this->encrypt($this->merchantData,$this->workingKey);

        return $this;

    }

    /**
     * @return mixed
     */
    public function send()
    {

        Log::info('Indipay Payment Request Initiated: ');
        return View::make('indipay::razorpay')
                             ->with('keyId',$this->keyId)
                             ->with('parameters',$this->parameters);

    }


    /**
     * Check Response
     * @param $request
     * @return array
     */
    public function response($request)
    {
        $encResponse = $request->encResp;

        $rcvdString = $this->decrypt($encResponse,$this->workingKey);
        parse_str($rcvdString, $decResponse);

        return $decResponse;
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
            throw new IndipayParametersMissingException;
        }

    }

    /**
     * CCAvenue Encrypt Function
     *
     * @param $plainText
     * @param $key
     * @return string
     */
    protected function encrypt($plainText,$key)
    {
        $secretKey = $this->hextobin(md5($key));
        $initVector = pack("C*", 0x00, 0x01, 0x02, 0x03, 0x04, 0x05, 0x06, 0x07, 0x08, 0x09, 0x0a, 0x0b, 0x0c, 0x0d, 0x0e, 0x0f);
        $openMode = @mcrypt_module_open(MCRYPT_RIJNDAEL_128, '','cbc', '');
        $blockSize = @mcrypt_get_block_size(MCRYPT_RIJNDAEL_128, 'cbc');
        $plainPad = $this->pkcs5_pad($plainText, $blockSize);
        if (@mcrypt_generic_init($openMode, $secretKey, $initVector) != -1)
        {
            $encryptedText = @mcrypt_generic($openMode, $plainPad);
            @mcrypt_generic_deinit($openMode);

        }
        return bin2hex($encryptedText);
    }

    /**
     * CCAvenue Decrypt Function
     *
     * @param $encryptedText
     * @param $key
     * @return string
     */
    protected function decrypt($encryptedText,$key)
    {
        $secretKey = $this->hextobin(md5($key));
        $initVector = pack("C*", 0x00, 0x01, 0x02, 0x03, 0x04, 0x05, 0x06, 0x07, 0x08, 0x09, 0x0a, 0x0b, 0x0c, 0x0d, 0x0e, 0x0f);
        $encryptedText=$this->hextobin($encryptedText);
        $openMode = @mcrypt_module_open(MCRYPT_RIJNDAEL_128, '','cbc', '');
        @mcrypt_generic_init($openMode, $secretKey, $initVector);
        $decryptedText = @mdecrypt_generic($openMode, $encryptedText);
        $decryptedText = rtrim($decryptedText, "\0");
        @mcrypt_generic_deinit($openMode);
        return $decryptedText;

    }


    /**
     * @param $plainText
     * @param $blockSize
     * @return string
     */
    protected function pkcs5_pad($plainText, $blockSize)
    {
        $pad = $blockSize - (strlen($plainText) % $blockSize);
        return $plainText . str_repeat(chr($pad), $pad);
    }


    /**
     * @param $hexString
     * @return string
     */
    protected function hextobin($hexString)
    {
        $length = strlen($hexString);
        $binString="";
        $count=0;
        while($count<$length)
        {
            $subString =substr($hexString,$count,2);
            $packedString = pack("H*",$subString);
            if ($count==0)
            {
                $binString=$packedString;
            }

            else
            {
                $binString.=$packedString;
            }

            $count+=2;
        }
        return $binString;
    }




}
