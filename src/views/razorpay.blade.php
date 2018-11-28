<html>
<head>
    <title>Razorpay</title>
    <meta name="viewport" content="width=device-width">
</head>
<body>
    <form action="{{ $parameters['redirect_url'] }}" method="POST">
        @foreach($parameters as $param_key=>$param_value)
        <input type="hidden" name="{{ $param_key }}" value="{{ $param_value  }}" />
        @endforeach
    </form>
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
                console.log("Script loaded");
                document.getElementsByClassName("razorpay-payment-button")[0].click();
                // Handle memory leak in IE
                script.onload = script.onreadystatechange = null;
                if ( form && script.parentNode ) {
                    form.removeChild( script );
                }
            }
        };

        // Use insertBefore instead of appendChild  to circumvent an IE6 bug.
        // This arises when a base node is used (#2709 and #4378).
        form.insertBefore( script, form.firstChild );

    </script>
</body>
</html>

