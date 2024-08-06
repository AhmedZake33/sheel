<!DOCTYPE html> 
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">     <head>         
<meta charset="utf-8">         
<meta name="viewport" content="width=device-width, initial-scale=1">          <title>Laravel</title>          
<!-- Fonts -->         
<link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700&display=swap" rel="stylesheet">          <style>             
body {                 
font-family: 'Nunito', sans-serif;             
}         
</style>         
   
</head>     
<body class="antialiased">    
<form id="payment-form">
  <div id="card-element"></div>
  <button type="submit">Submit Payment</button>
</form>


<script src="https://js.stripe.com/v3/"></script>

<script>
    var stripe = Stripe('pk_test_51LwUrGAaVPCFtfhoWfACYmx4HBTWHP1sIEwcSP6zHeoDUsztYJsYty9zRrc4OYnmlmMBUIvJvzfVEylwYnAtF7gQ00DmP7GV4G');
    var elements = stripe.elements();

    var cardElement = elements.create('card');
    cardElement.mount('#card-element');

    var form = document.getElementById('payment-form');
    form.addEventListener('submit', function(event) {
    event.preventDefault();

    stripe.createToken(cardElement).then(function(result) {
        console.log(result.token)
        if (result.error) {
        // Display error.message in your UI
        } else {
        // Send the token to your server
        


            const customer = stripe.createCustomer({
            source: result.token.id,
            email: 'customer@example.com',
            });

            console.log(customer.id);
        }
    });
        });

</script>
</body> 
</html>