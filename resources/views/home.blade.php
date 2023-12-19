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
    <p>Welcome, {{ auth()->user()->name }}!</p>

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
    $(function(){                 
        //let socket = io('http://localhost:3000');    
        let ip = "http://localhost";
        let socket_port = '3000';
        let socket = io(ip + ':'+ socket_port);
        let user = @json(auth()->user()->id);
        let token = '{{ csrf_token() }}'
        console.log(token);

        const pusher = new Pusher('e352c1403f81a822031a', {
            cluster: 'eu',
            authEndpoint: 'http://127.0.0.1:8000/pusher/auth',
            headers: {
                "X-CSRF-Token": token,
            }, // Replace with your server's URL

        });

        const channel = pusher.subscribe('private-user-channel');
        channel.bind('my-event', (data) => {
            console.log('Received:', data.message);
        });

        socket.emit('message', { message: 'Hello from the client' });

        socket.on('connect',function(){
            socket.emit('user_connected',user);
        });       

        socket.on('updateUserStatus',(data) => {
            length = data.filter(value => (value !== null)).length;
            document.getElementById('length').innerHTML = length
            $.each(data, function(key , val){
                if( val !== null && val !== 0){
                    console.log(key);
                }
            })
        });

        socket.on('private-event', (data) => {
            console.log('Received private message:', data.message);
        });

        //socket.on('private-user-channel-2:App\\Events\\ChatEvent', (data) => {
        //     console.log('Received private message:', data.message);
        // });
    });             
    //socket.on('connection');         
</script>     
</body> 
</html>