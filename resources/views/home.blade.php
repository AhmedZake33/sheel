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
<script src="{{ asset('js/app.js') }}" defer>
</script>     
</head>     
<body class="antialiased">    

    @auth
        <p>Welcome, {{ auth()->user()->name }}! {{ auth()->id() }}</p>

        Active Users <span id="length"></span>
        <form action="{{route('logout')}}" method="POST"> 
            @csrf
            <input type="submit" value="Logout" class="btn btn-primary">
        </form>
    @else
        <form action="{{route('login')}}" method="POST"> 
            @csrf
            <input type="email" name="email">
            <input type="submit" class="btn btn-primary">
        </form>
@endauth


@php
    $appUrl = env('APP_URL');
@endphp


    
<script src="https://cdn.socket.io/4.5.0/socket.io.min.js" integrity="sha384-7EyYLQZgWBi67fBtVxw60/OWl1kjsfrPFcaU0pp0nAh+i8FD068QogUvg85Ewy1k" crossorigin="anonymous">
</script> 

  <script src="https://js.pusher.com/7.0/pusher.min.js"></script>

        
<script src="https://code.jquery.com/jquery-3.6.0.min.js" integrity="sha256-/xUj+3OJU5yExlq6GSYGSHk7tPXikynS7ogEvDej/m4=" crossorigin="anonymous">
</script>
            {{-- "X-CSRF-Token": token, --}}
            {{-- "Authorization" : "Bearer " + "eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiJ9.eyJhdWQiOiI5OWE3MmRjMC0wZjU3LTQ5NmItYmY1ZS0wN2Y3ZjM0YmMwOGEiLCJqdGkiOiJkZTUwZmY2M2M4M2Q1YjY0NWY3MTc3YTk5ZDMwYjQ4ZThjZDkyOTRjZDZlMDdlZWQwYjM5NDk1OGFjYjMwNDk2MWUzMjk0YTBhNjc5YWNlZiIsImlhdCI6MTcxMzA1NjM1Ny4yMDk5NDIsIm5iZiI6MTcxMzA1NjM1Ny4yMDk5NDUsImV4cCI6MTcxMzE0Mjc1Ny4xMjk1MTIsInN1YiI6IjQ4Iiwic2NvcGVzIjpbXX0.bnbs7LA_TTotK5qERtoNMgD5Ko0jTIH2Qqig02T4GvpYWFLobzBNfFsFBNAt_jiNaa6XAiJDwOyZmWFXeCxrry5o2kIBbNYOPaveqSJ1AaOUCXe1CWhGMu3e5SfvdxpysTn0KMfPHX_MqkMXaTpzyxrxUV6Fr3q84HDmEw5gq1NQ3pJu4rFIrXGR-XoaARt5OlbCUovPPaTFrlPCXyn-MwGA3ejXW1NgBB3KjONA5UlPDjwruSVeOTBoYahNYbtENMkrijoa2LUHt2WvqH9-0zN1LHXlXe_ZgRDoT8quuLdmQx7Iv1hgPIeYNRBglQ3oCn4I4VxSMqWEnPASzXZEA_RKqBgm2zoBahe4zRV6PAuFvqWhx6-z_ubSAGjiIFdQ3mIy-XgF8-MvX-KFX_UX83y83NtbNewoRGHR-6Ywtxgz9HySWiQnDev4Lcfwj7sjG0lrehedU69XBH8vkYb-MiL51mfNJGYPWDtWHj5FA0_-OLKCYpzDFidSlnNUkk_1BnCk_YQCH8PqwUw_QZqTpIdQkOef1KF458baazfBlXgeXx2P8Hz0DZlU_ZMzUmd9V_kgFqiY_KOtIgCkNyBwqEFQcBBG_PQPAp8ydIqsvUxxJ1IKyfqQN9GwH3cNmx9b0opbce1A70z3MHyFJuYrN7IAKktwyRa_JCcpoNPZM6U" --}}

         
<script>  

    
    let token = '{{ csrf_token() }}'
    console.log(token); 
    var appUrl = '{{ $appUrl }}';
    const pusher = new Pusher('e352c1403f81a822031a', {
        cluster: 'eu',
        authEndpoint: appUrl + '/broadcasting/auth',
    });


    var channel = pusher.subscribe('private-privateNotification.48');
    channel.bind('NotificationEvent', function(data) {
        alert("success");
        console.log("success");
    });

    var channel = pusher.subscribe('public-channel');
    channel.bind('PublicEvent', function(data) {
        alert("success");
    });

    var channel = pusher.subscribe('private-requestChannel.172');
    channel.bind('RequestEvent', function(data) {
        alert("success");
    });


    var channel = pusher.subscribe('private-message.48');
    channel.bind('ChatMessageEvent', function(data) {
        alert("success");
    });



         
</script>     
</body> 
</html>