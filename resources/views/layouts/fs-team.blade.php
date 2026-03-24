<!DOCTYPE html>
<html>

<head>
    <title>FS Team Portal</title>
    <style>
        body {
            display: flex;
            font-family: Arial, sans-serif;
            margin: 0;
        }

        .sidebar {
            width: 250px;
            background: #2c3e50;
            color: white;
            height: 100vh;
            padding: 20px;
        }

        .sidebar a {
            color: white;
            display: block;
            margin-bottom: 15px;
            text-decoration: none;
        }

        .content {
            flex: 1;
            padding: 30px;
        }

        /* Stylish Divider */
        .divider {
            border: 0;
            height: 2px;
            background-image: linear-gradient(to right, rgba(0, 0, 0, 0), rgba(255, 255, 255, 0.75), rgba(0, 0, 0, 0));
            margin: 20px 0;
        }
    </style>
</head>

<body>

    <div class="sidebar">
        <h3>FS Team Menu</h3>
        <hr class="divider">
        <a href="{{ route('team.dashboard') }}">Home</a>
        <a href="{{ route('team.downloadables') }}">Downloadable Forms</a>
        <a href="{{ route('team.resolutions') }}">IA Resolutions</a>

        <hr class="divider">
        <form action="{{ route('logout') }}" method="POST">
            @csrf
            <button type="submit">Logout</button>
        </form>
    </div>

    <div class="content">
        @yield('content')
    </div>

</body>

</html>