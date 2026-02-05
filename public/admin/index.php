<?php
// Initialize the session
session_start();
 
// Check if the user is already logged in, if yes then redirect him to dashboard
if(isset($_SESSION["loggedin"]) && $_SESSION["loggedin"] === true){
    header("location: dashboard.php");
    exit;
}
 
// Include config file
require_once "../../config.php";
 
// Define variables and initialize with empty values
$username = $password = "";
$username_err = $password_err = $login_err = "";
 
// Processing form data when form is submitted
if($_SERVER["REQUEST_METHOD"] == "POST"){
 
    // Check if username is empty
    if(empty(trim($_POST["username"]))){
        $username_err = "Silakan masukkan username.";
    } else{
        $username = trim($_POST["username"]);
    }
    
    // Check if password is empty
    if(empty(trim($_POST["password"]))){
        $password_err = "Silakan masukkan password Anda.";
    } else{
        $password = trim($_POST["password"]);
    }
    
    // Validate credentials
    if(empty($username_err) && empty($password_err)){
        // Prepare a select statement
        $sql = "SELECT id, username, password FROM users WHERE username = ?";
        
        if($stmt = $mysqli->prepare($sql)){
            // Bind variables to the prepared statement as parameters
            $stmt->bind_param("s", $param_username);
            
            // Set parameters
            $param_username = $username;
            
            // Attempt to execute the prepared statement
            if($stmt->execute()){
                // Store result
                $stmt->store_result();
                
                // Check if username exists, if yes then verify password
                if($stmt->num_rows == 1){                    
                    // Bind result variables
                    $stmt->bind_result($id, $username, $hashed_password);
                    if($stmt->fetch()){
                        if(password_verify($password, $hashed_password)){
                            // Password is correct, so start a new session
                            // session_start(); // Session already started at the top
                            
                            // Store data in session variables
                            $_SESSION["loggedin"] = true;
                            $_SESSION["id"] = $id;
                            $_SESSION["username"] = $username;                            
                            
                            // Redirect user to dashboard
                            header("location: dashboard.php");
                            exit;
                        } else{
                            // Password is not valid
                            $login_err = "Username atau password salah.";
                        }
                    }
                } else{
                    // Username doesn't exist
                    $login_err = "Username atau password salah.";
                }
            } else{
                $login_err = "Terjadi kesalahan sistem. Coba lagi nanti.";
            }

            // Close statement
            $stmt->close();
        }
    }
    
    // Close connection
    $mysqli->close();
}
?>
 
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login | RS JHC Tasikmalaya</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        :root {
            --jhc-red-dark: #8a3033;
            --jhc-red-light: #bd3030;
            --jhc-gradient: linear-gradient(90deg, #8a3033 0%, #bd3030 100%);
        }

        body { 
            background-color: #f4f7f6;
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        .login-card {
            width: 100%;
            max-width: 400px;
            padding: 40px;
            background: #ffffff;
            border-radius: 20px;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.1);
            border: 1px solid rgba(0,0,0,0.05);
        }

        .login-header {
            text-align: center;
            margin-bottom: 30px;
        }

        .login-header img {
            max-width: 120px;
            margin-bottom: 15px;
        }

        .login-header h2 {
            font-weight: 800;
            color: var(--jhc-red-dark);
            text-transform: uppercase;
            font-size: 1.5rem;
            letter-spacing: 1px;
        }

        .form-label {
            font-weight: 600;
            color: #555;
            font-size: 0.9rem;
        }

        .form-control {
            border-radius: 10px;
            padding: 12px 15px;
            border: 1px solid #ddd;
            transition: all 0.3s ease;
        }

        .form-control:focus {
            border-color: var(--jhc-red-light);
            box-shadow: 0 0 0 0.25rem rgba(138, 48, 51, 0.1);
        }

        .btn-jhc-login {
            background: var(--jhc-gradient);
            border: none;
            color: white;
            width: 100%;
            padding: 12px;
            border-radius: 10px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-top: 10px;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(138, 48, 51, 0.3);
        }

        .btn-jhc-login:hover {
            opacity: 0.9;
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(138, 48, 51, 0.4);
        }

        .alert {
            border-radius: 10px;
            font-size: 0.85rem;
            border: none;
        }
    </style>
</head>
<body>

    <div class="login-card">
        <div class="login-header">
            <i class="fas fa-user-shield fa-3x text-muted opacity-25 mb-3"></i>
            <h2>Admin Panel</h2>
            <p class="text-muted small">Silakan masuk untuk mengelola konten website.</p>
        </div>

        <?php 
        if(!empty($login_err)){
            echo '<div class="alert alert-danger d-flex align-items-center"><i class="fas fa-exclamation-circle me-2"></i>' . $login_err . '</div>';
        }        
        ?>

        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
            <div class="mb-3">
                <label class="form-label">Username</label>
                <div class="input-group">
                    <span class="input-group-text bg-light text-muted border-end-0"><i class="fas fa-user"></i></span>
                    <input type="text" name="username" class="form-control border-start-0 <?php echo (!empty($username_err)) ? 'is-invalid' : ''; ?>" value="<?php echo $username; ?>" placeholder="Masukkan username">
                    <span class="invalid-feedback"><?php echo $username_err; ?></span>
                </div>
            </div>    

            <div class="mb-4">
                <label class="form-label">Password</label>
                <div class="input-group">
                    <span class="input-group-text bg-light text-muted border-end-0"><i class="fas fa-lock"></i></span>
                    <input type="password" name="password" class="form-control border-start-0 <?php echo (!empty($password_err)) ? 'is-invalid' : ''; ?>" placeholder="Masukkan password">
                    <span class="invalid-feedback"><?php echo $password_err; ?></span>
                </div>
            </div>

            <button type="submit" class="btn btn-jhc-login">
                Masuk Sekarang <i class="fas fa-sign-in-alt ms-2"></i>
            </button>
        </form>

        <div class="text-center mt-4">
            <a href="../../index.php" class="text-muted small text-decoration-none">
                <i class="fas fa-arrow-left me-1"></i> Kembali ke Website Utama
            </a>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>