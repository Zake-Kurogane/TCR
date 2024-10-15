<?php
session_start();

require_once "config.php";
 
// Define variables and initialize with empty values
$email = $password = "";
$email_err = $password_err = $login_err = "";

// Processing form data when form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
 
    // Check if email is empty
    if (empty(trim($_POST["email"]))) {
        $email_err = "Please enter your email.";
    } else {
        $email = trim($_POST["email"]);
    }
    
    // Check if password is empty
    if (empty(trim($_POST["password"]))) {
        $password_err = "Please enter your password.";
    } else {
        $password = trim($_POST["password"]);
    }
    
    // Validate credentials
    if (empty($email_err) && empty($password_err)) {
        // Prepare a select statement
        $sql = "SELECT id, email, password, role FROM users WHERE email = ?";
        
        if ($stmt = mysqli_prepare($link, $sql)) {
            mysqli_stmt_bind_param($stmt, "s", $param_email);
            
            // Set parameters
            $param_email = $email;
            
            // Attempt to execute the prepared statement
            if (mysqli_stmt_execute($stmt)) {
                mysqli_stmt_store_result($stmt);
                
                // Check if email exists, if yes then verify password
                if (mysqli_stmt_num_rows($stmt) == 1) {
                    mysqli_stmt_bind_result($stmt, $id, $email, $hashed_password, $role);
                    if (mysqli_stmt_fetch($stmt)) {
                        if (password_verify($password, $hashed_password)) {
                            session_start();
                            
                            // Store data in session variables
                            $_SESSION["loggedin"] = true;
                            $_SESSION["id"] = $id;
                            $_SESSION["email"] = $email;  
                            $_SESSION["role"] = $role;

                            // Redirect user based on role
                            if ($role === 'admin') {
                                header("location: admin_dashboard.php");
                            } else {
                                header("location: customer_dashboard.php");
                            }
                            exit;
                        } else {
                            $login_err = "Invalid email or password.";
                        }
                    }
                } else {
                    $login_err = "Invalid email or password.";
                }
            } else {
                echo '<script type="text/javascript"> alert("Oops! Something went wrong. Please try again later.")</script>';
            }

            // Close statement
            mysqli_stmt_close($stmt);
        }
    }
    
    // Close connection
    mysqli_close($link);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>TC Car Rental</title>
    <link rel="icon" href="img/logo_web.png" type="image/png">
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        .error-input {
            border-color: red;
            background-color: #ffe6e6;
        }
    </style>
</head>
<body class="bg-white-50">

    <div class="flex items-center justify-center min-h-screen">
        <!-- Left Section (Image placeholder) -->
        <div class="w-1/2 flex items-center justify-center mb-20">
            <img src="img/logo-2x.png" alt="Car and TC Car Rental text" class="max-w-full">
        </div>

        <!-- Right Section (Login Form) -->
        <div class="w-1/2 flex items-center justify-center">
            <div class="bg-white p-8 rounded-lg shadow-md max-w-sm w-full">
                <h1 class="text-3xl font-bold mb-4">Welcome <span class="text-blue-500">Driver!</span></h1>
                <p class="text-gray-600 mb-6">I hope you have a good stay and enjoy exploring the application. If you still don’t have an account. <a href="customer_signup.php" class="text-blue-500 underline">Create here</a>.</p>

                <!-- Display error message if it exists -->
                <?php if (!empty($login_err)): ?>
                    <div class="mb-4 text-red-500">
                        <?php echo $login_err; ?>
                    </div>
                <?php endif; ?>
                
                <form action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" method="post">
                    <!-- Email Input Field -->
                    <div class="mb-4 relative">
                        <input type="text" name="email" value="<?php echo $email; ?>" placeholder="Email" 
                            class="pl-10 pr-4 py-2 w-full border border-gray-300 rounded-lg focus:outline-none focus:border-blue-500 <?php echo (!empty($email_err)) ? 'error-input' : ''; ?>" 
                            >
                        <div class="absolute left-2 top-2 text-gray-400">
                            <img src="img/mail.png" alt="Email Icon" class="w-6 h-6">
                        </div>
                        <span class="text-red-500"><?php echo $email_err; ?></span>
                    </div>

                    <!-- Password Input Field -->
                    <div class="mb-4 relative">
                        <input type="password" name="password" value="<?php echo $password; ?>" placeholder="Password" 
                            class="pl-10 pr-4 py-2 w-full border border-gray-300 rounded-lg focus:outline-none focus:border-blue-500 <?php echo (!empty($password_err)) ? 'error-input' : ''; ?>" 
                            >
                        <div class="absolute left-2 top-2 text-gray-400">
                            <img src="img/padlock.png" alt="Password Icon" class="w-6 h-6">
                        </div>
                        <span class="text-red-500"><?php echo $password_err; ?></span>
                    </div>

                    <!-- Remember Me and Forgot Password -->
                    <div class="flex items-center justify-between mb-6">
                        <label class="inline-flex items-center">
                            <input type="checkbox" class="form-checkbox text-blue-500 border-gray-300 rounded-lg">
                            <span class="ml-2 text-gray-600">Remember Me</span>
                        </label>
                        <a href="#" class="text-blue-500 text-sm">Forgot Password?</a>
                    </div>

                    <div class="w-full">
                        <button type="submit" class="bg-blue-500 text-white text-center w-full py-2 rounded-lg hover:bg-blue-600 transition duration-200 block">Log In</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

</body>
</html>
