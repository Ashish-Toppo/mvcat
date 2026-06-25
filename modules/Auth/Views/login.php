<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login | Auth Module</title>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;600&display=swap" rel="stylesheet">
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; font-family: 'Outfit', sans-serif; }
        body {
            background: linear-gradient(135deg, #1f005c, #5b0060, #870160, #ac255e, #ca485c, #e16b5c, #f39060, #ffb56b);
            background-size: 400% 400%;
            animation: gradientBG 15s ease infinite;
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #fff;
        }
        @keyframes gradientBG {
            0% { background-position: 0% 50%; }
            50% { background-position: 100% 50%; }
            100% { background-position: 0% 50%; }
        }
        .login-card {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border-radius: 20px;
            border: 1px solid rgba(255, 255, 255, 0.2);
            padding: 40px;
            width: 100%;
            max-width: 400px;
            box-shadow: 0 15px 35px rgba(0,0,0,0.2);
            transform: translateY(0);
            transition: all 0.3s ease;
        }
        .login-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 25px 45px rgba(0,0,0,0.3);
        }
        h2 { text-align: center; margin-bottom: 30px; font-weight: 600; font-size: 28px; letter-spacing: 1px; }
        .form-group { margin-bottom: 25px; position: relative; }
        label { display: block; margin-bottom: 8px; font-size: 14px; font-weight: 300; opacity: 0.9; }
        input {
            width: 100%;
            padding: 12px 15px;
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid rgba(255, 255, 255, 0.2);
            border-radius: 10px;
            color: #fff;
            font-size: 16px;
            outline: none;
            transition: all 0.3s ease;
        }
        input:focus {
            background: rgba(255, 255, 255, 0.15);
            border-color: rgba(255, 255, 255, 0.5);
        }
        input::placeholder { color: rgba(255, 255, 255, 0.5); }
        button {
            width: 100%;
            padding: 14px;
            background: linear-gradient(135deg, #ffb56b, #e16b5c);
            border: none;
            border-radius: 10px;
            color: #fff;
            font-size: 18px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(225, 107, 92, 0.4);
        }
        button:hover {
            transform: scale(1.02);
            box-shadow: 0 6px 20px rgba(225, 107, 92, 0.6);
        }
        .error {
            background: rgba(255, 0, 0, 0.2);
            border-left: 4px solid #ff4d4d;
            padding: 10px;
            margin-bottom: 20px;
            border-radius: 4px;
            font-size: 14px;
        }
    </style>
</head>
<body>

    <div class="login-card">
        <h2>Welcome Back</h2>

        <?php $error = flash('error'); if ($error): ?>
            <div class="error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <form action="/auth/login" method="POST">
            <div class="form-group">
                <label for="email">Email Address</label>
                <input type="email" name="email" id="email" placeholder="admin@example.com" required>
            </div>
            
            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" name="password" id="password" placeholder="••••••••" required>
            </div>

            <button type="submit">Sign In</button>
        </form>
    </div>

</body>
</html>
