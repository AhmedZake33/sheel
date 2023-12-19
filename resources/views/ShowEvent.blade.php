<html>
<head></head>
<body>
    Request  => {{$requestId}} 

    Messsages 
    <span id="message"></span>


    <script src="{{asset('js/app.js')}}"></script>
    <script>
    let requestId = @json($requestId);
        
        Echo.private(`channel.${requestId}`)
            .listen('.my-event',(event) => {
                console.log(event.message)
            });
    </script>
</body>
</html>