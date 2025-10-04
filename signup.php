<?php
include 'db.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'];
    $email = $_POST['email'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    
    try {
        $stmt = $pdo->prepare("INSERT INTO users (username, email, password) VALUES (?, ?, ?)");
        $stmt->execute([$username, $email, $password]);
        echo "<script>window.location.href='index.php';</script>";
    } catch (PDOException $e) {
        $error = "Error: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gmail Clone - Sign Up</title>
    <style>
        body {
            font-family: 'Roboto', Arial, sans-serif;
            background: #f1f3f4;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
        }
        .signup-container {
            background: white;
            padding: 40px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            width: 100%;
            max-width: 400px;
        }
        .signup-container h1 {
            text-align: center;
            color: #202124;
            font-size: 24px;
            margin-bottom: 20px;
        }
        .signup-container input {
            width: 100%;
            padding: 12px;
            margin: 10px 0;
            border: 1px solid #dadce0;
            border-radius: 4px;
            font-size: 16px;
        }
        .signup-container button {
            width: 100%;
            padding: 12px;
            background: #1a73e8;
            color: white;
            border: none;
            border-radius: 4px;
            font-size: 16px;
            cursor: pointer;
        }
        .signup-container button:hover {
            background: #1557b0;
        }
        .error {
            color: red;
            text-align: center;
            margin-bottom: 10px;
        }
        .login-link {
            text-align: center;
            margin-top: 20px;
        }
        .login-link a {
            color: #1a73e8;
            text-decoration: none;
        }
        @media (max-width: 600px) {
            .signup-container {
                padding: 20px;
            }
        }
    </style>
</head>
<body>
    <div class="signup-container">
        <h1>Create Account</h1>
        <?php if (isset($error)) echo "<p class='error'>$error</p>"; ?>
        <form method="POST">
            <input type="text" name="username" placeholder="Username" required>
            <input type="email" name="email" placeholder="Email" required>
            <input type="password" name="password" placeholder="Password" required>
            <button type="submit">Sign Up</button>
        </form>
        <div class="login-link">
            <p>Already have an account? <a href="javascript:window.location.href='index.php'">Sign In</a></p>
        </div>
    </div>
</body>
</html>
