<html>
<head>
    <title>Razorpay</title>
    <meta name="viewport" content="width=device-width">
    {{-- Make the Pay button centered --}}
    <style>
        .razorpay-payment-button{
            height: 20px;
            width: 120px;
        }
        .razorpay-form{
            width: 100%;
            height: 100%; 
            display: flex;
            align-items: center;
            justify-content: center;            
        }
    </style>
</head>
<body>
    <form action="{{ $parameters['redirect_url'] }}" method="POST" class="razorpay-form">
        {{-- Add all passed-in parameters as hidden variables so that they can be retrieved on response --}}
        @foreach($parameters as $param_key=>$param_value)
        <input type="hidden" name="{{ $param_key }}" value="{{ $param_value  }}" />
        @endforeach
    </form>
    {{-- Programatically add the checkout script to handle loading completed --}}
    {{-- So that the button can be clicked automatically on load --}}
    <script>
        var form = document.getElementsByTagName("form")[0];
        var script = document.createElement("script");
        script.src = "https://checkout.razorpay.com/v1/checkout.js";
        script.setAttribute("data-key","{{ $keyId }}");
        script.setAttribute("data-amount","{{ $amount }}");
        script.setAttribute("data-name","{{ $parameters['merchant_name'] }}");
        @if(!empty($parameters['description']))
        script.setAttribute("data-description","{{ $parameters['description'] }}");
        @endif
        @if(!empty($parameters['image']))
        script.setAttribute("data-image","{{ $parameters['image'] }}");
        @endif
        @if(!empty($parameters['button_text']))
        script.setAttribute("data-buttontext","{{ $parameters['button_text'] }}");
        @else
        script.setAttribute("data-buttontext","Pay with Razorpay");
        @endif
        @if(!empty($parameters['customer_name']))
        script.setAttribute("data-prefill.name","{{ $parameters['customer_name'] }}");
        @endif
        @if(!empty($parameters['email']))
        script.setAttribute("data-prefill.email","{{ $parameters['email'] }}");
        @endif
        @if(!empty($parameters['phone']))
        script.setAttribute("data-prefill.contact","{{ $parameters['phone'] }}");
        @endif
        @if(!empty($parameters['theme_color'] ))    
        script.setAttribute("data-theme.color","{{ $parameters['theme_color'] }}");
        @else
        script.setAttribute("data-theme.color","#F37254");
        @endif
        // Handle Script loading
        var done = false;

        // Attach handlers for all browsers
        script.onload = script.onreadystatechange = function() {
            if ( !done && (!this.readyState ||
                    this.readyState === "loaded" || this.readyState === "complete") ) {
                done = true;
                document.getElementsByClassName("razorpay-payment-button")[0].click();
            }
        };

        form.insertBefore( script, form.firstChild );

    </script>
</body>
</html>

