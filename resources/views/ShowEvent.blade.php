<html>
<head></head>
<body>
    Request  => {{$requestId}} 

    <br>

    User => {{auth()->id()}}
<br>
    Messsages 
    <span id="message"></span>


    <script src="{{asset('js/app.js')}}"></script>
    <script>
    let requestId = @json($requestId);
    let user = @json(auth()->id());
    let arr = [133];
    for(let i = 0 ; i < arr.length ; i++){
        
        Echo.private(`channel.${arr[i]}`)
            .listen('.chat',(event) => {
                console.log(event.chat)
            });

        Echo.private(`Notification.${user}`)
            .listen('.Notification',(event) => {
                console.log(event.chat)
            });    
    }
        
    </script>
</body>
</html>