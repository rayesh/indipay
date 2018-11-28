<html>
<head>
    <title>IndiPay</title>
</head>
<body>
    <form action="{{ $parameters['redirect_url'] }}" method="POST">
        <script
            src="https://checkout.razorpay.com/v1/checkout.js"
            data-key="{{ $keyId }}"
            data-amount="{{ $parameters['amount'] }}"
            data-name="{{ $parameters['merchant_name'] }}"
            @if(!empty($parameters['description']))
                data-description="{{ $parameters['description'] }}"
            @endif
            @if(!empty($parameters['image']))
                data-image="{{ $parameters['image'] }}"
            @endif
            @if(!empty($parameters['button_text']))
                data-buttontext="{{ $parameters['button_text'] }}"
            @else
                data-buttontext="Pay with Razorpay"    
            @endif
            @if(!empty($parameters['customer_name']))
                data-prefill.name="{{ $parameters['customer_name'] }}"
            @endif
            @if(!empty($parameters['customer_email']))
                data-prefill.email="{{ $parameters['customer_email'] }}"
            @endif
            @if(!empty($parameters['theme_color'] ))    
                data-theme.color="{{ $parameters['theme_color'] }}"
            @endif
        >
        </script>
    @foreach($parameters as $param_key=>$param_value)
        <input type="hidden" name="{{ $param_key }}" value="{{ $param_value  }}" />
    @endforeach
    </form>
</body>
</html>

