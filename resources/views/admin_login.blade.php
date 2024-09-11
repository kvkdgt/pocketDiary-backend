<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin | KarmTrack</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&family=Roboto:ital,wght@0,100;0,300;0,400;0,500;0,700;0,900;1,100;1,300;1,400;1,500;1,700;1,900&display=swap" rel="stylesheet">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat+Alternates:wght@500&display=swap" rel="stylesheet">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Madimi+One&display=swap" rel="stylesheet">
    <link rel="stylesheet" type="text/css" href="{{ URL::asset('css/admin/login.css'); }} ">
</head>

<body>
    <div class="container">
        <div class="left-container">
            <!-- <img src="{{ URL::asset('imgs/logo.png'); }}" style="width:75%" alt=""> -->
            <span class="admin-panel">KarmTrack</span>

            <span class="hr-line">&nbsp;</span>
            <span class="admin-panel">Admin Panel</span>
        </div>
        <div class="right-container">
            <?php
            if (session()->has('error')) {
            ?>
                <div class="error-msg">
                    {{session()->get('error')}}

                </div>
                <br>
            <?php
                session()->flush();
            }
            ?>
            <span class="signin-text">Sign In</span>
            <div class="login-form">
                <form action="{{ route('loginCheck') }}" method="post">
                    @csrf
                    <label for="username">Email</label>
                    <div class="username">
                        <input type="email" name="email" placeholder="Enter your Email">
                    </div>
                    <label for="password">Password</label>
                    <div class="username">
                        <input type="password" name="password" placeholder="Enter your Password">
                    </div>
                    <div class="submit-btn">
                        <input type="submit" value="Sign In">
                    </div>
                </form>
            </div>
        </div>
    </div>
</body>

</html>