<?php
// Include config file
require_once "config.php";

// Define variables and initialize with empty values
$fullname = $email = $password = $confirm_password = $number = $profile_image = "";
$fullname_err = $email_err = $password_err = $confirm_password_err = $number_err = $profile_image_err = "";

// Processing form data when form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (empty(trim($_POST["fullname"]))) {
        $fullname_err = "Please enter your full name.";
    } else {
        $fullname = trim($_POST["fullname"]);
    }

    // Validate email
    if (empty(trim($_POST["email"]))) {
        $email_err = "Please enter an email.";
    } else {
        $sql = "SELECT id FROM users WHERE email = ?";
        
        if ($stmt = mysqli_prepare($link, $sql)) {
            mysqli_stmt_bind_param($stmt, "s", $param_email);
            $param_email = trim($_POST["email"]);
            
            if (mysqli_stmt_execute($stmt)) {
                mysqli_stmt_store_result($stmt);
                
                if (mysqli_stmt_num_rows($stmt) == 1) {
                    $email_err = "This email is already taken.";
                } else {
                    $email = trim($_POST["email"]);
                }
            } else {
                echo "Oops! Something went wrong. Please try again later.";
            }
            mysqli_stmt_close($stmt);
        }
    }

    // Validate phone number
    if (empty(trim($_POST["number"]))) {
        $number_err = "Please enter your phone number.";
    } else {
        $number = trim($_POST["number"]);
        
        // Check if the number is exactly 11 digits
        if (!preg_match('/^\d{11}$/', $number)) {
            $number_err = "Phone number must be exactly 11 digits.";
        } else {
            // Check if phone number already exists
            $sql = "SELECT id FROM users WHERE number = ?";
            
            if ($stmt = mysqli_prepare($link, $sql)) {
                mysqli_stmt_bind_param($stmt, "s", $param_number);
                $param_number = $number;
                
                if (mysqli_stmt_execute($stmt)) {
                    mysqli_stmt_store_result($stmt);
                    
                    if (mysqli_stmt_num_rows($stmt) == 1) {
                        $number_err = "This phone number is already registered.";
                    }
                } else {
                    echo "Oops! Something went wrong. Please try again later.";
                }
                mysqli_stmt_close($stmt);
            }
        }
    }

    // Validate password
    if (empty(trim($_POST["password"]))) {
        $password_err = "Please enter a password.";     
    } elseif (strlen(trim($_POST["password"])) < 6) {
        $password_err = "Password must have at least 6 characters.";
    } else {
        $password = trim($_POST["password"]);
    }

    // Validate confirm password
    if (empty(trim($_POST["confirm_password"]))) {
        $confirm_password_err = "Please confirm password.";     
    } else {
        $confirm_password = trim($_POST["confirm_password"]);
        if (empty($password_err) && ($password != $confirm_password)) {
            $confirm_password_err = "Password did not match.";
        }
    }

    // Handle image upload
    if (isset($_FILES['profile_image']) && $_FILES['profile_image']['error'] == UPLOAD_ERR_OK) {
        $image_tmp_name = $_FILES['profile_image']['tmp_name'];
        $image_name = $_FILES['profile_image']['name'];
        $image_type = $_FILES['profile_image']['type'];
        $upload_dir = 'profiles/';
        $upload_file = $upload_dir . basename($image_name);

        // Validate image type
        $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
        if (in_array($image_type, $allowed_types)) {
            // Move the uploaded file to the profiles directory
            if (move_uploaded_file($image_tmp_name, $upload_file)) {
                $profile_image = $upload_file;
            } else {
                $profile_image_err = "Failed to upload image.";
            }
        } else {
            $profile_image_err = "Only JPG, PNG, and GIF images are allowed.";
        }
    } else {
        // Set a default image if none is uploaded
        $profile_image = 'profiles/profile.jpg';
    }

    // Check input errors before inserting in database
    if (empty($fullname_err) && empty($email_err) && empty($number_err) && empty($password_err) && empty($confirm_password_err) && empty($profile_image_err)) {
        
        // Prepare an insert statement with the profile_image column
        $sql = "INSERT INTO users (fullname, email, number, password, role, profile_image) VALUES (?, ?, ?, ?, 'customer', ?)";
         
        if ($stmt = mysqli_prepare($link, $sql)) {
            mysqli_stmt_bind_param($stmt, "sssss", $param_fullname, $param_email, $param_number, $param_password, $param_profile_image);
            
            $param_fullname = $fullname;
            $param_email = $email;
            $param_number = $number;
            $param_password = password_hash($password, PASSWORD_DEFAULT);
            $param_profile_image = $profile_image;
            
            if (mysqli_stmt_execute($stmt)) {
                echo '<script type="text/javascript"> alert("Sign-up Completed!"); location="login.php"; </script>';
            } else {
                echo '<script type="text/javascript"> alert("Oops! Something went wrong. Please try again later.");</script>';
            }

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
    <title>TC Car Rental - Sign Up</title>
    <link rel="icon" href="img/logo_web.png" type="image/png">
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        input[type=number]::-webkit-inner-spin-button,
        input[type=number]::-webkit-outer-spin-button {
        -webkit-appearance: none;
        margin: 0;
        }
    </style>
</head>
<body class="bg-white-50">

    <div class="flex items-center justify-center min-h-screen">
        <!-- Left Section (Image placeholder) -->
        <div class="w-1/2 flex items-center justify-center mb-20">
            <img src="img/logo-2x.png" alt="Car and TC Car Rental text" class="max-w-full">
        </div>

        <!-- Right Section (Sign Up Form) -->
        <div class="w-1/2 flex items-center justify-center">
            <div class="bg-white p-8 rounded-lg shadow-md max-w-lg w-full">
                <h1 class="text-3xl font-bold mb-4">Welcome <span class="text-blue-500">Driver!</span></h1>
                <p class="text-gray-600 mb-6">If you already have an account, <a href="login.php" class="text-blue-500 underline">Login here</a>.</p>

                <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">

                    <!-- Fullname Input Field -->
                    <div class="mb-4 relative">
                        <input type="text" name="fullname" value="<?php echo $fullname; ?>" placeholder="Full-name" 
                            class="pl-10 pr-4 py-2 w-full border border-gray-300 rounded-lg focus:outline-none focus:border-blue-500" required>
                        <div class="absolute left-2 top-2 text-gray-400">
                            <img src="img/user.png" alt="User Icon" class="w-6 h-6">
                        </div>
                        <span class="text-red-500"><?php echo $fullname_err; ?></span>
                    </div>

                    <!-- Number Input Field with 11 digits validation -->
                    <div class="mb-4 relative">
                        <input type="number" name="number" value="<?php echo $number; ?>" placeholder="Phone Number" 
                            class="pl-10 pr-4 py-2 w-full border border-gray-300 rounded-lg focus:outline-none focus:border-blue-500" 
                            pattern="\d{11}" title="Number must be exactly 11 digits" required>
                        <div class="absolute left-2 top-2 text-gray-400">
                            <img src="img/mobile-phone.png" alt="Phone Icon" class="w-6 h-6">
                        </div>
                        <span class="text-red-500"><?php echo $number_err; ?></span>
                    </div>

                    <!-- Email Input Field -->
                    <div class="mb-4 relative">
                        <input type="email" name="email" value="<?php echo $email; ?>" placeholder="Email" class="pl-10 pr-4 py-2 w-full border border-gray-300 rounded-lg focus:outline-none focus:border-blue-500">
                        <div class="absolute left-2 top-2 text-gray-400">
                            <img src="img/mail.png" alt="Email Icon" class="w-6 h-6">
                        </div>
                        <span class="text-red-500"><?php echo $email_err; ?></span>
                    </div>

                    <!-- Password Input Field -->
                    <div class="mb-4 relative">
                        <input type="password" name="password" placeholder="Password" class="pl-10 pr-4 py-2 w-full border border-gray-300 rounded-lg focus:outline-none focus:border-blue-500">
                        <div class="absolute left-2 top-2 text-gray-400">
                            <img src="img/padlock.png" alt="Password Icon" class="w-6 h-6">
                        </div>
                        <span class="text-red-500"><?php echo $password_err; ?></span>
                    </div>

                    <!-- Confirm Password Input Field -->
                    <div class="mb-4 relative">
                        <input type="password" name="confirm_password" placeholder="Confirm Password" class="pl-10 pr-4 py-2 w-full border border-gray-300 rounded-lg focus:outline-none focus:border-blue-500">
                        <div class="absolute left-2 top-2 text-gray-400">
                            <img src="img/padlock.png" alt="Confirm Password Icon" class="w-6 h-6">
                        </div>
                        <span class="text-red-500"><?php echo $confirm_password_err; ?></span>
                    </div>

                    <div class="w-full">
                        <button type="submit" class="bg-blue-500 text-white text-center w-full py-2 rounded-lg hover:bg-blue-600 transition duration-200">Sign Up</button>
                    </div>

                </form>
            </div>
        </div>
    </div>

</body>
</html>
