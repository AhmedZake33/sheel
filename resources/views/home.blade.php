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


    
<script src="https://cdn.socket.io/4.5.0/socket.io.min.js" integrity="sha384-7EyYLQZgWBi67fBtVxw60/OWl1kjsfrPFcaU0pp0nAh+i8FD068QogUvg85Ewy1k" crossorigin="anonymous">
</script> 

  <script src="https://js.pusher.com/7.0/pusher.min.js"></script>

        
<script src="https://code.jquery.com/jquery-3.6.0.min.js" integrity="sha256-/xUj+3OJU5yExlq6GSYGSHk7tPXikynS7ogEvDej/m4=" crossorigin="anonymous">
</script>
         
<script>  

    
    let token = '{{ csrf_token() }}'
    console.log(token); 

    const pusher = new Pusher('e352c1403f81a822031a', {
        cluster: 'eu',
        authEndpoint: 'http://127.0.0.1:8000/broadcasting/auth',
        headers: {
            "X-CSRF-Token": token,
        },
    });


    var channel = pusher.subscribe('private-channel.173');
    channel.bind('chat', function(data) {
        alert("success");
      console.log("success");
    });



         
</script>     
</body> 
</html>