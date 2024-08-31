<html>
    <head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Your Laravel App</title>  
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

    <style>
    table {
        width: 100%;
        border-collapse: collapse;
    }

    th, td {
        border: 1px solid #dddddd;
        text-align: left;
        padding: 8px;
    }

    th {
        background-color: #f2f2f2;
    }

    tr:nth-child(even) {
        background-color: #f2f2f2;
    }

    tr:hover {
        background-color: #dddddd;
    }

    .action-column {
        width: 100px; /* Adjust the width as needed */
    }
    </style>
    </head>
    <body>
    <div class="container">
        @auth
            @if(auth()->user()->type == 3)
            <div class="container">
            <table style="margin-top:25px">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>files</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($providers as $provider)
                        <tr>
                            <td>{{ $provider->id }}</td>
                            <td>{{ $provider->name }}</td>
                            <td>
                                <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#userFilesModal{{$provider->id}}">
                                    Show Files for {{$provider->name}}
                                </button>
                            </td>
                            <td>
                                <form method="POST" action="{{ route('accept-provider', $provider->id) }}" style="display: inline;">
                                    @csrf
                                    <button type="submit" class="btn btn-primary">Accept</button>
                                </form>
                                <form method="POST" action="{{ route('refuse-provider', $provider->id) }}" style="display: inline;">
                                    @csrf
                                    <button type="submit" class="btn btn-danger">Refuse</button>
                                </form>
                            </td>

                        </tr>
                        <div class="modal fade" id="userFilesModal{{$provider->id}}" tabindex="-1" role="dialog" aria-labelledby="userFilesModalLabel{{$provider->id}}" aria-hidden="true">
                            <div class="modal-dialog" role="document">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title" id="userFilesModalLabel{{$provider->id}}">Files for {{$provider->name}}</h5>
                                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                            <span aria-hidden="true">&times;</span>
                                        </button>
                                    </div>
                                    <div class="modal-body">
                                        
                                        @foreach($provider->profile as $file)
                                            @if(isset($file->value) && $file->value)
                                                <div style="display: flex; justify-content: space-between;">
                                                    <div style="text-decoration:underline">{{ $file->key}}</div>
                                                    <div><a target="_blank" href="{{ $file->value }}">Download</a></div>
                                                </div>
                                                
                                            @endif
                                        @endforeach

                                    </div>
                                    <div class="modal-footer">
                                        {{-- <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button> --}}
                                         {{-- <form method="POST" action="{{ route('accept-provider', $provider->id) }}" style="display: inline;">
                                            @csrf
                                            <button type="submit" class="btn btn-primary">Accept</button>
                                        </form>
                                        <form method="POST" action="{{ route('refuse-provider', $provider->id) }}" style="display: inline;">
                                            @csrf
                                            <button type="submit" class="btn btn-danger">Refuse</button>
                                        </form> --}}
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </tbody>
            </table>
            </div>
            @else
            <div style="text-align:center">
                <h1 style="text-align:center">You are Not Authorized</h1>
                <form action="{{route('logout')}}" method="POST"> 
                    @csrf
                    <input type="submit" value="Logout" class="btn btn-primary">
                </form>
            </div>
                
            @endif


        @else

        <form action="{{route('login')}}" method="POST"> 
            @csrf
            <input type="email" name="email"><br/><br/>
            <input type="submit" class="btn btn-primary">
        </form>

        @endauth

        <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.4/dist/umd/popper.min.js"></script>
        <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>

    </div>
    </body>
</html>