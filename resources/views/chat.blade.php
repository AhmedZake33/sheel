<html>
    <head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Your Laravel App</title>

    <!-- Bootstrap CSS via CDN -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Your other stylesheets, meta tags, etc. -->
    </head>
    <body>
    <div class="container">
        @auth
        <h1 style="text-align:center">Welcome To Chat Page</h1>
        <form method="post" action="{{ route('sendMessage',['id' => $id]) }}">
            @csrf

            <div class="form-group">
                <label for="message">Message:</label>
                <textarea name="message" id="message" class="form-control" required></textarea>
            </div>

            <button type="submit" class="btn btn-primary">Send Message</button>
        </form>
        @else

        <form action="{{route('login')}}" method="POST"> 
            @csrf
            <input type="email" name="email"><br/><br/>
            <input type="submit" class="btn btn-primary">
        </form>

        @endauth


        <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.10.2/dist/umd/popper.min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.min.js"></script>
    </div>
    </body>
</html>