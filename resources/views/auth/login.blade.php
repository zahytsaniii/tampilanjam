<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Login Admin</title>

    <meta name="viewport" content="width=device-width, initial-scale=1">

    <style>
        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            min-height: 100vh;
            font-family: 'Segoe UI', sans-serif;
            background: linear-gradient(135deg, #1a73e8, #0d47a1);
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .login-box {
            width: 360px;
            padding: 35px 30px;
            background: rgba(255,255,255,0.95);
            border-radius: 12px;
            box-shadow: 0 20px 40px rgba(0,0,0,0.25);
            animation: fadeIn 0.6s ease;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(15px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .login-box h3 {
            text-align: center;
            margin-bottom: 25px;
            color: #0d47a1;
            font-size: 22px;
        }

        .form-group {
            margin-bottom: 18px;
        }

        label {
            font-size: 14px;
            font-weight: 600;
            color: #333;
            display: block;
            margin-bottom: 6px;
        }

        input {
            width: 100%;
            padding: 11px 12px;
            border-radius: 6px;
            border: 1px solid #ccc;
            font-size: 14px;
            transition: 0.2s;
        }

        input:focus {
            outline: none;
            border-color: #1a73e8;
            box-shadow: 0 0 0 2px rgba(26,115,232,0.15);
        }

        button {
            width: 100%;
            padding: 11px;
            background: linear-gradient(to right, #1a73e8, #0d47a1);
            color: #fff;
            border: none;
            border-radius: 7px;
            font-size: 15px;
            font-weight: 600;
            cursor: pointer;
            transition: 0.25s;
            margin-top: 5px;
        }

        button:hover {
            transform: translateY(-1px);
            box-shadow: 0 6px 15px rgba(26,115,232,0.4);
        }

        .error {
            background: #ffebee;
            color: #c62828;
            padding: 10px 12px;
            border-radius: 6px;
            font-size: 14px;
            margin-bottom: 15px;
            text-align: center;
        }

        .footer-text {
            margin-top: 18px;
            text-align: center;
            font-size: 12px;
            color: #777;
        }
    </style>
</head>
<body>

<div class="login-box">

    <h3>Login Administrator</h3>

    @if($errors->any())
        <div class="error">{{ $errors->first() }}</div>
    @endif

    <form action="{{ route('login.process') }}" method="POST">
        @csrf

        <div class="form-group">
            <label>Email</label>
            <input type="email" name="email" required>
        </div>

        <div class="form-group">
            <label>Password</label>
            <input type="password" name="password" required>
        </div>

        <button type="submit">Masuk</button>
    </form>

    <div class="footer-text">
        © {{ date('Y') }} Sistem Display Masjid
    </div>

</div>

</body>
</html>
