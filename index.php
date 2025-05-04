<?php

session_start();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Get Started - OnlyStream</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Montserrat', Arial, sans-serif; 
        }
        body {
            background-color: #f5f5f5; 
            color: #333;
            display: flex;
            flex-direction: column; 
            min-height: 100vh;
            text-align: center;
            padding: 20px;
        }
        .container {
            max-width: 800px;
            width: 100%;
            margin: 0 auto; 
            flex-grow: 1; 
            display: flex;
            flex-direction: column;
            justify-content: center; 
        }
       
        .website-name {
            font-family: 'Montserrat', Arial, sans-serif;
            font-size: 36px;
            font-weight: 700;
            color: #e50914;
            margin-bottom: 30px;
            text-transform: uppercase;
            letter-spacing: 2px;
        }
        .header h1 {
            font-size: 3.5rem;
            font-weight: 700;
            margin-bottom: 20px;
            color: #e50914; 
            text-transform: uppercase;
            letter-spacing: 2px;
        }
        .intro-text {
            font-size: 1.2rem;
            line-height: 1.6;
            margin-bottom: 40px;
            color: #666; 
            padding: 0 20px;
        }
        .options {
            display: flex;
            flex-direction: column;
            gap: 20px;
            align-items: center;
        }
        .options a {
            display: inline-block;
            padding: 15px 40px;
            font-size: 1.2rem;
            font-weight: 600;
            text-decoration: none;
            color: #e5e5e5; 
            background: linear-gradient(145deg, #e50914, #b20710); 
            border-radius: 50px;
            box-shadow: 0 4px 15px rgba(229, 9, 20, 0.5);
            transition: background 0.3s ease, transform 0.3s ease, box-shadow 0.3s ease;
            width: 100%;
            max-width: 300px;
        }
        .options a:hover {
            background: linear-gradient(145deg, #b20710, #e50914); 
            transform: translateY(-3px); 
            box-shadow: 0 6px 20px rgba(229, 9, 20, 0.7); 
        }
        .options .guest {
            background: linear-gradient(145deg, #333, #222);
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.3); 
        }
        .options .guest:hover {
            background: linear-gradient(145deg, #444, #333); 
            transform: translateY(-3px);
            box-shadow: 0 6px 20px rgba(229, 9, 20, 0.3); 
        }
        
        @media (max-width: 600px) {
            .website-name {
                font-size: 28px;
            }
            .header h1 {
                font-size: 2.5rem;
            }
            .intro-text {
                font-size: 1rem;
            }
            .options a {
                padding: 12px 30px;
                font-size: 1rem;
                max-width: 250px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="website-name">
            OnlyStream
        </div>
        <div class="header">
            <h1>Welcome to OnlyStream</h1>
        </div>
        <div class="intro-text">
          
Stream movies and music effortlessly with OnlyStream! Watch anytime, upload your favorites, and connect with a vibrant community. 

        </div>
        <div class="options">
            <a href="signup.php">Sign Up</a>
            <a href="login.php">Login</a>
            <a href="retreive_videos.php" class="guest">Watch as a Guest</a>
        </div>
    </div>
</body>
</html>