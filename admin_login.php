<?php
session_start();
if (isset($_SESSION['admin_id'])) {
    header("Location: admin_dashboard.html");
    exit;
}

$error = '';
if (isset($_GET['error'])) {
    if ($_GET['error'] == 'invalid_credentials') $error = 'Invalid admin credentials or unauthorized.';
    elseif ($_GET['error'] == 'error') $error = 'A system error occurred.';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin System Portal</title>
    <link rel="stylesheet" href="css/styles.css">
</head>
<body class="login-page" style="background-color: #2c3e50;">
    <div class="login-container">
        <h2 style="text-align: center; margin-bottom: 20px; color:#c0392b;">Administration</h2>
        <p style="text-align:center; color:#7f8c8d; margin-bottom: 20px;">Secure Access Only</p>
        
        <?php if ($error): ?>
            <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <form action="php/admin/auth.php" method="POST">
            <div class="form-group">
                <label for="email">Admin Email</label>
                <input type="email" id="email" name="email" class="form-control" placeholder="admin@university.com" required autofocus>
            </div>
            
            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" class="form-control" placeholder="••••••••" required>
            </div>
            
            <button type="submit" class="btn-login" style="background-color: #c0392b; color: white;">Access Dashboard</button>
            <div style="text-align: center; margin-top: 15px;">
                <a href="login.php" style="color: #3498db; text-decoration: none; font-size: 0.9em;">Back to Student Portal</a>
            </div>
        </form>
    </div>
</body>
</html>
