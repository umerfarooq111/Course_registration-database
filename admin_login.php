<?php
session_start();
if (isset($_SESSION['admin_id'])) {
    header("Location: admin_dashboard.html");
    exit;
}

$error = '';
if (isset($_GET['error'])) {
    if ($_GET['error'] == 'invalid_credentials')
        $error = 'Invalid admin credentials or unauthorized.';
    elseif ($_GET['error'] == 'error')
        $error = 'A system error occurred.';
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin System Portal</title>
    <link rel="stylesheet" href="css/auth.css">
</head>

<body class="auth-body">
    <div class="login-wrapper">
        <div class="login-header">
            <h2>Administration Login</h2>
            <p>Secure Access Only</p>
        </div>

        <?php if ($error): ?>
            <div class="alert alert-danger">
                <?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>

        <form action="php/admin/auth.php" method="POST">
            <div class="form-group">
                <label for="email">Admin Email</label>
                <input type="email" id="email" name="email" class="form-control" placeholder="admin@university.com"
                    required autofocus>
            </div>

            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" class="form-control" placeholder="••••••••"
                    required>
            </div>

            <button type="submit" class="btn-login btn-admin">Access Dashboard</button>

        </form>
    </div>
</body>

</html>