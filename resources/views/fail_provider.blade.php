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
            color: red;
        }
    </style>
</head>
<body>
    <div class="container">
    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="50" height="50" fill="#FF0000">
        <path d="M0 0h24v24H0z" fill="none"/>
        <path d="M19 6.41L17.59 5 12 10.59 6.41 5 5 6.41 10.59 12 5 17.59 6.41 19 12 13.41 17.59 19 19 17.59 13.41 12z"/>
    </svg>

    <h1>Provider Refused</h1>
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
