<?php
require_once '../config.php';
require_once './auth.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';
    
    if (loginAdmin($username, $password)) {
        header('Location: /CircleUp/admin/dashboard.php');
        exit();
    } else {
        $error = 'Invalid username or password';
    }
}

if (isAdminLoggedIn()) {
    header('Location: /CircleUp/admin/dashboard.php');
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CircleUp Admin Login</title>
    <link href="https://fonts.googleapis.com/css2?family=Oswald:wght@400;500;600;700&family=Barlow+Condensed:wght@400;600;700;800&display=swap" rel="stylesheet">
    <style>
        :root {
            --navy: #0a1628;
            --navy-mid: #1a2744;
            --navy-light: #243456;
            --red: #b22234;
            --red-bright: #e8293b;
            --white: #f5f0e8;
            --white-pure: #ffffff;
            --gold: #c9a84c;
            --gold-bright: #ffd700;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Barlow Condensed', sans-serif;
            background: var(--navy);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .flag-stripe {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 6px;
            background: repeating-linear-gradient(
                90deg,
                var(--red) 0px,
                var(--red) 33.33%,
                var(--white-pure) 33.33%,
                var(--white-pure) 66.66%,
                var(--navy-mid) 66.66%,
                var(--navy-mid) 100%
            );
            z-index: 100;
        }
        
        .login-container {
            background: var(--navy-light);
            border: 2px solid var(--gold);
            border-radius: 2px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.6);
            width: 100%;
            max-width: 400px;
            padding: 40px;
        }
        
        .login-header {
            text-align: center;
            margin-bottom: 30px;
        }
        
        .login-header h1 {
            font-family: 'Oswald', sans-serif;
            font-size: 32px;
            color: var(--gold-bright);
            margin-bottom: 10px;
            letter-spacing: 2px;
        }
        
        .login-header h1 span {
            color: var(--red);
        }
        
        .login-header p {
            color: var(--gold);
            font-size: 13px;
            letter-spacing: 1px;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        label {
            display: block;
            margin-bottom: 8px;
            color: var(--gold);
            font-weight: 700;
            font-size: 12px;
            letter-spacing: 1px;
        }
        
        input[type="text"],
        input[type="password"] {
            width: 100%;
            padding: 12px;
            border: 1px solid var(--gold);
            border-radius: 2px;
            font-size: 14px;
            font-family: inherit;
            background: var(--navy-mid);
            color: var(--white);
            transition: all 0.2s;
        }
        
        input[type="text"]:focus,
        input[type="password"]:focus {
            outline: none;
            border-color: var(--red);
            box-shadow: 0 0 10px rgba(232, 41, 59, 0.3);
        }
        
        .error {
            background: rgba(232, 41, 59, 0.2);
            color: var(--red-bright);
            padding: 12px;
            border-radius: 2px;
            margin-bottom: 20px;
            font-size: 13px;
            border-left: 4px solid var(--red);
        }
        
        button {
            width: 100%;
            padding: 14px;
            background: var(--red);
            color: var(--white-pure);
            border: 2px solid var(--gold);
            border-radius: 2px;
            font-family: 'Oswald', sans-serif;
            font-size: 13px;
            font-weight: 700;
            cursor: pointer;
            letter-spacing: 1px;
            text-transform: uppercase;
            transition: all 0.3s;
        }
        
        button:hover {
            background: var(--red-bright);
            box-shadow: 0 0 15px rgba(232, 41, 59, 0.5);
        }
        
        button:active {
            transform: translateY(0);
        }

        .login-footer {
            position: fixed;
            bottom: 0;
            left: 0;
            width: 100%;
            padding: 20px;
            text-align: center;
        }

        .login-footer-nav {
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 20px;
        }

        .login-footer-nav a {
            color: var(--gold);
            text-decoration: none;
            font-family: 'Oswald', sans-serif;
            font-weight: 600;
            font-size: 12px;
            letter-spacing: 1.5px;
            text-transform: uppercase;
            transition: color 0.3s;
            opacity: 0.6;
        }

        .login-footer-nav a:hover {
            color: var(--white-pure);
            opacity: 1;
        }

        .login-footer-dot {
            width: 4px;
            height: 4px;
            background: var(--gold);
            border-radius: 50%;
            opacity: 0.3;
        }
    </style>
</head>
<body>
    <div class="flag-stripe"></div>

    <div class="login-container">
        <div class="login-header">
            <h1>Circle<span>Up</span></h1>
            <p>Admin Access</p>
        </div>
        
        <?php if ($error): ?>
            <div class="error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>
        
        <form method="POST">
            <div class="form-group">
                <label for="username">Username</label>
                <input type="text" id="username" name="username" required autofocus>
            </div>
            
            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" required>
            </div>
            
            <button type="submit">Sign In</button>
        </form>
    </div>

    <div class="login-footer">
        <nav class="login-footer-nav">
            <a href="/CircleUp/">Home</a>
            <span class="login-footer-dot"></span>
            <a href="/CircleUp/store/">Shop</a>
        </nav>
    </div>
</body>
</html>
