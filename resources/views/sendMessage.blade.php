<html>
<head>
<script src="http://ajax.googleapis.com/ajax/libs/jquery/1.7.1/jquery.min.js" type="text/javascript"></script>
</head>
<body>

test send message 
<button id="btn1">Click1</button>
<button id="btn2">Click2</button>
<button id="btn3">Click3</button>

<script>
    $(document).ready(function () {
        $("#btn2").click(function(){
        
          $('.successmsg').html('');
          $('.successmsg').removeAttr('style');
          $.ajax({
            url: '<?php echo e(url("/sendMessage")); ?>',
            type: 'POST',
            'headers': {
              'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
          });
        });

    });
</script>
</body>
</html>