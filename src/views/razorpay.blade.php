<html>
<head>
    <title>Razorpay</title>
    <meta name="viewport" content="width=device-width">
    {{-- Make the Pay button centered --}}
    <style>
        body{
            margin: 0px;
        }
        input[type=submit],
        input[type="button"]{
            height: 40px;
            @if(!empty($parameters['theme_color'] ))
            background: {{ $parameters['theme_color'] }};
            border: 1px solid {{ $parameters['theme_color'] }};
            @else
            background: #CA4242;
            border: 1px solid #CA4242;
            @endif
            box-sizing: border-box;
            color: #fff;
            font: 400 18px/40px 'Open Sans', sans-serif;
            text-align: center;
            float: left;
            margin: 5px;
            transition: .3s all;
        }
        input[type=submit]:hover,
        input[type="button"]:hover{
            @if(!empty($parameters['theme_color'] ))
            color: {{ $parameters['theme_color'] }};
            @else
            color: #CA4242;
            @endif
            background: #fff;
        }
        #overlay{
            background: #666666;
            position: absolute;
            width: 100%;
            height: 100%;
            visibility: hidden;
        }
        #loader {
            position: absolute;
            left: 50%;
            top: 50%;
            border: 2px solid #f3f3f3;
            border-radius: 50%;
            @if(!empty($parameters['theme_color'] ))            
            border-top: 2px solid {{ $parameters['theme_color'] }};
            @else
            border-top: 2px solid #CA4242;
            @endif
            width: 40px;
            height: 40px;
            margin: -20px 0 0 -20px;
            -webkit-animation: spin 2s linear infinite; /* Safari */
            animation: spin 2s linear infinite;
        }

        /* Safari */
        @-webkit-keyframes spin {
            0% { -webkit-transform: rotate(0deg); }
            100% { -webkit-transform: rotate(360deg); }
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
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
    <div id="overlay">
        <div id="loader"></div> 
    </div>
    <form action="{{ $parameters['redirect_url'] }}" method="POST" class="razorpay-form">
        {{-- Add all passed-in parameters as hidden variables so that they can be retrieved on response --}}
        @foreach($parameters as $param_key=>$param_value)
        <input type="hidden" name="{{ $param_key }}" value="{{ $param_value  }}" />
        @endforeach
        <input type="button" id="cancelBtn" class="razorpay-cancel-button" value="Cancel" onclick="cancelPayment()">
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
        script.setAttribute("data-theme.color","#CA4242");
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
        {{--  Add cancel handler --}}

        function cancelPayment(){
            var form = document.getElementsByTagName("form")[0];
            @if(!empty($parameters['cancel_url']))
            form.action = "{{ $parameters['cancel_url'] }}";
            form.submit();
            @else
            window.history.back();
            @endif
        }

        window.addEventListener('beforeunload', function (e) {
            document.getElementById("overlay").style.visibility = "visible";
            // Chrome requires returnValue to be set.
            e.returnValue = '';
        });

    </script>
</body>
</html>
