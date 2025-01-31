<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Provider</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f0f0f0;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }
        .container {
            text-align: center;
        }
        h1 {
            color: #008000;
        }
    </style>
</head>
<body>
    <div class="container">
    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="50" height="50" fill="#008000">
        <path d="M0 0h24v24H0z" fill="none"/>
        <path d="M9 16.2l-3.5-3.5-1.4 1.4L9 19 21 7l-1.4-1.4L9 16.2z"/>
    </svg>

    <h1>Provider Approved</h1>
    </div>


    <script>
        // JavaScript to handle the redirection after 5 seconds
        function redirectToPreviousPage() {
            setTimeout(function() {
                window.location.href = "{{ $redirectTo }}";
            }, 3000); // 5000 milliseconds = 5 seconds
        }

        // Call the function when the page loads
        window.onload = redirectToPreviousPage;
    </script>

</body>
</html>
