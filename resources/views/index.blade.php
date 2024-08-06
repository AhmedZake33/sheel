<!DOCTYPE html>
<head>
  <title>Pusher Test</title>
  <script src="https://js.pusher.com/8.2.0/pusher.min.js"></script>
</head>
<body>
  <h1>Pusher Test</h1>
  <p>
    Try publishing an event to channel <code>my-channel</code>
    with event name <code>my-event</code>.
  </p>

  <h1 id="heading">test</h1>


  <script>
  window.onload = function(){
    var pusher = new Pusher('e352c1403f81a822031a', {
      cluster: 'eu'
    });    
    var channel = pusher.private('channel.1');
    header = document.getElementById('heading');
    
    channel.listen('my-event', function(data) {
      alert(JSON.stringify(data));
      header.innerHTML = data.message;
    });
  }
  </script>
</body>