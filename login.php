<?php
session_start();
// If already logged in, redirect to dashboard
if (isset($_SESSION['student_id'])) {
    header("Location: dashboard.html");
    exit;
}

$error = '';
if (isset($_GET['error'])) {
    if ($_GET['error'] == 'invalid_credentials') {
        $error = 'Invalid credentials';
    } elseif ($_GET['error'] == 'error') {
        $error = 'An error occurred. Please try again.';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Course Registration</title>
    <link rel="stylesheet" href="css/styles.css">
</head>
<body class="login-page">
    <div class="login-container">
        <h2 style="text-align: center; margin-bottom: 30px;">Student Login</h2>
        
        <?php if ($error): ?>
            <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <form action="php/auth/login.php" method="POST">
            <div class="form-group">
                <label for="email">Email</label>
                <input type="email" id="email" name="email" class="form-control" placeholder="Enter your email" required autofocus>
            </div>
            
            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" class="form-control" placeholder="Enter your password" required>
            </div>
            
            <button type="submit" class="btn-login">Submit</button>
        </form>
    </div>
</body>
</html>
