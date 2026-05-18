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
    <title>Student Login | Course Registration</title>
    <link rel="stylesheet" href="css/auth.css">
</head>
<body class="auth-body">
    <div class="login-wrapper">
        <div class="login-header">
            <h2>Student Login</h2>
            <p>Please enter your credentials to access the portal</p>
        </div>
        
        <?php if ($error): ?>
            <div class="alert alert-danger">
                <?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>

        <form action="php/auth/login.php" method="POST">
            <div class="form-group">
                <label for="email">Email Address</label>
                <input type="email" id="email" name="email" class="form-control" placeholder="name@student.edu" required autofocus>
            </div>
            
            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" class="form-control" placeholder="••••••••" required>
            </div>
            
            <button type="submit" class="btn-login">Sign In</button>
        </form>
    </div>
</body>
</html>
