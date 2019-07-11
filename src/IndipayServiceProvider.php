<?php namespace Softon\Indipay;

use Illuminate\Support\Facades\Config;
use Illuminate\Support\ServiceProvider;
use Softon\Indipay\PaymentGatewayHelperInterface;
use Illuminate\Contracts\Support\DeferrableProvider;

class IndipayServiceProvider extends ServiceProvider {

	/**
	 * Indicates if loading of the provider is deferred.
	 *
	 * @var bool
	 */
	protected $defer = false;

	/**
	 * Register the service provider.
	 *
	 * @return void
	 */
	
	

    public function boot(PaymentGatewayHelperInterface $dfltGateway ){
		
			$configfromenv = 	Config::get('indipay.configfromenv');
		
		
				if(!$configfromenv){
					$gateway  =  $dfltGateway->getDefaultPaymentGateway();

					$this->publishes([
						__DIR__.'/config/config.php' => base_path('config/indipay.php'),
						config( $dfltGateway->getPaymentGatewayDetails()),
            __DIR__.'/views/middleware.blade.php' => base_path('app/Http/Middleware/VerifyCsrfMiddleware.php'),
					]);

					$this->app->bind('Softon\Indipay\Gateways\PaymentGatewayInterface','Softon\Indipay\Gateways\\'.$gateway.'Gateway');
				}else{
					$this->publishes([
						__DIR__.'/config/config.php' => base_path('config/indipay.php'),
            __DIR__.'/views/middleware.blade.php' => base_path('app/Http/Middleware/VerifyCsrfMiddleware.php'),
				]);
				}
		$this->loadViewsFrom(__DIR__.'/views', 'indipay');

    }

		public function register()
	{
				$configfromenv = 	Config::get('indipay.configfromenv');
				
				if($configfromenv){
	      $gateway = Config::get('indipay.gateway');
        $this->app->bind('indipay', 'Softon\Indipay\Indipay');

       $this->app->bind('Softon\Indipay\Gateways\PaymentGatewayInterface','Softon\Indipay\Gateways\\'.$gateway.'Gateway');
			}else{
				$this->app->bind('indipay', 'Softon\Indipay\Indipay');

			}
	}
	/**
	 * Get the services provided by the provider.
	 *
	 * @return array
	 */
	public function provides()
	{
		return [

        ];
	}

}
