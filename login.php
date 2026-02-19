<?php
session_start();
require_once __DIR__ . '/db.php';

// If already logged in
if (isset($_SESSION['user_id'])) {
    header("Location: admin.php");
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($username) || empty($password)) {
        $error = "Please enter username and password.";
    } else {
        $pdo = get_db_connection();
        if ($pdo) {
            try {
                $stmt = $pdo->prepare("SELECT id, password_hash FROM users WHERE username = ?");
                $stmt->execute([$username]);
                $user = $stmt->fetch();

                if ($user && password_verify($password, $user['password_hash'])) {
                    $_SESSION['user_id'] = $user['id'];
                    $_SESSION['username'] = $username;
                    header("Location: admin.php");
                    exit;
                } else {
                    $error = "Invalid credentials.";
                }
            } catch (PDOException $e) {
                // Table not found -> Redirect to setup
                if ($e->getCode() == '42S02') {
                    header("Location: setup.php");
                    exit;
                }
                $error = "Database error: " . $e->getMessage();
            }
        } else {
            $error = "Database connection error.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="ko">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        body {
            display: flex;
            align-items: center;
            justify-content: center;
            height: 100vh;
            background: #f1f5f9;
        }

        .login-card {
            background: white;
            padding: 40px;
            border-radius: 12px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            width: 100%;
            max-width: 400px;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
        }

        .form-group input {
            width: 100%;
            padding: 10px;
            border: 1px solid #cbd5e1;
            border-radius: 6px;
        }

        .error {
            color: #ef4444;
            margin-bottom: 20px;
            font-size: 0.9rem;
            text-align: center;
        }
    </style>
</head>

<body>
    <div class="login-card">
        <h1 style="text-align: center; margin-bottom: 30px; font-size: 1.5rem;">Admin Login</h1>

        <?php if ($error): ?>
            <div class="error">
                <?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>

        <form method="POST">
            <div class="form-group">
                <label>Username</label>
                <input type="text" name="username" required autofocus>
            </div>
            <div class="form-group">
                <label>Password</label>
                <input type="password" name="password" required>
            </div>
            <button type="submit" class="btn" style="width: 100%;">Login</button>
        </form>
        <div style="text-align: center; margin-top: 20px; font-size: 0.8rem; color: #64748b;">
            <a href="index.php">Back to Home</a>
        </div>
    </div>
</body>

</html>