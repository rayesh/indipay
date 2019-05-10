<html>
<head>
    <title>IndiPay</title>
</head>
<body>
    <form method="post" name="redirect" action="{{ $endPoint }}">

        <input type=hidden name="timezone" value="{{ $parameters['timezone'] }}">
        <input type=hidden name="authenticateTransaction" value="false">
        <input type=hidden name="txntype" value="{{ $parameters['txntype'] }}">
        <input type="hidden" name="txndatetime" value="{{ $parameters['txndatetime'] }}" />
        <input type=hidden name="hash" value="{{ $hash }}">
        <input type=hidden name="currency" value="{{ $parameters['currency'] }}">
        <input type=hidden name="mode" value="{{ $parameters['mode'] }}">
        <input type=hidden name="storename" value="{{ $parameters['storename'] }}">
        <input type=hidden name="chargetotal" value="{{ $parameters['amount'] }}">
        <input type=hidden name="oid" value="{{ $parameters['oid'] }}">
        <input type=hidden name="sharedsecret" value="{{ $parameters['sharedsecret'] }}">
        <input type=hidden name="responseSuccessURL" value="{{ $parameters['responseSuccessURL'] }}">
        <input type=hidden name="responseFailURL" value="{{ $parameters['responseFailURL'] }}">
        <input type=hidden name="email" value="{{ $parameters['email'] }}">
        <input type=hidden name="hash_algorithm" value="SHA1">
        
        <input type=hidden name="customParam_firstname" value="{{ $parameters['firstname'] }}">
        <input type=hidden name="customParam_phone" value="{{ $parameters['phone'] }}">
        <input type=hidden name="customParam_productinfo" value="{{ $parameters['productinfo'] }}">
        <input type=hidden name="customParam_udf1" value="{{ $parameters['udf1'] or '' }}">
        <input type=hidden name="customParam_udf2" value="{{ $parameters['udf2'] or '' }}">
       
        
    </form>
<script language='javascript'>document.redirect.submit();</script>
</body>
</html>

