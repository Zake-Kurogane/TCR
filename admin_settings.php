<?php
session_start();
require_once 'config.php'; // Include your database configuration

// Check if the user is logged in and is an admin
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true || $_SESSION["role"] !== 'admin') {
  header("location: login.php");
  exit;
}
// Initialize variables
$fullname = '';
$position = '';
$email = '';
$number = '';
$profile_image = '';
$success_message = '';
$error_message = '';
$old_password_err = $new_password_err = '';

// Fetch user data
$user_id = $_SESSION['id'];
$query = "SELECT fullname, position, email, number, profile_image FROM users WHERE id = ?";
$stmt = $link->prepare($query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $fullname = $row['fullname'];
    $position = $row['position'];
    $email = $row['email'];
    $number = $row['number'];
    $profile_image = $row['profile_image'];
}
$stmt->close();

// Handle profile image upload and save changes
if (isset($_POST['update_profile'])) {
    // Check if profile image is uploaded
    if (isset($_FILES['profile_image']) && $_FILES['profile_image']['error'] == UPLOAD_ERR_OK) {
        $image_tmp_name = $_FILES['profile_image']['tmp_name'];
        $image_name = $_FILES['profile_image']['name'];
        $image_type = $_FILES['profile_image']['type'];
        $upload_dir = 'profiles/';
        $upload_file = $upload_dir . basename($image_name);

        // Validate image type
        $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
        if (in_array($image_type, $allowed_types)) {
            if (move_uploaded_file($image_tmp_name, $upload_file)) {
                $profile_image = $upload_file;
            } else {
                $error_message = 'Error moving uploaded file.';
            }
        } else {
            $error_message = 'Invalid file type. Only JPG, PNG, and GIF files are allowed.';
        }
    }

    // Update user profile details
    $fullname = $_POST['fullname'];
    $position = $_POST['position'];
    $email = $_POST['email'];
    $number = $_POST['number'];

    // Check if email already exists
    $email_check_query = "SELECT * FROM users WHERE email = ? AND id != ?";
    $email_check_stmt = $link->prepare($email_check_query);
    $email_check_stmt->bind_param("si", $email, $user_id);
    $email_check_stmt->execute();
    $email_result = $email_check_stmt->get_result();

    // Check if number already exists
    $number_check_query = "SELECT * FROM users WHERE number = ? AND id != ?";
    $number_check_stmt = $link->prepare($number_check_query);
    $number_check_stmt->bind_param("si", $number, $user_id);
    $number_check_stmt->execute();
    $number_result = $number_check_stmt->get_result();

    // Set error messages if duplicates are found
    if ($email_result->num_rows > 0) {
        $error_message = "Email already exists.";
    } elseif ($number_result->num_rows > 0) {
        $error_message = "Phone number already exists.";
    } else {
        // Proceed with the update if no duplicates found
        $update_query = "UPDATE users SET fullname = ?, position = ?, email = ?, number = ?, profile_image = ? WHERE id = ?";
        $stmt = $link->prepare($update_query);
        $stmt->bind_param("sssssi", $fullname, $position, $email, $number, $profile_image, $user_id);

        if ($stmt->execute()) {
            // Set success message in session and redirect
            $_SESSION['success_message'] = "Profile updated successfully!";
            header("Location: admin_settings.php");
            exit(); // Always exit after header redirection
        } else {
            $_SESSION['error_message'] = "Error updating profile: " . $stmt->error;
        }
        $stmt->close();
    }

    // Close the check statements
    $email_check_stmt->close();
    $number_check_stmt->close();
}

// Fetch updated user data again if there was an error
if (isset($error_message)) {
    $query = "SELECT fullname, position, email, number, profile_image FROM users WHERE id = ?";
    $stmt = $link->prepare($query);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $fullname = $row['fullname'];
        $position = $row['position'];
        $email = $row['email'];
        $number = $row['number'];
        $profile_image = $row['profile_image'];
    }
    $stmt->close();
}


if (isset($_POST['change_password'])) {
  $old_password = $_POST['old_password'];
  $new_password = $_POST['new_password'];
  $confirm_password = $_POST['confirm_password'];
  $user_id = $_SESSION['id'];  

  // Initialize error variables
  $old_password_err = "";
  $new_password_err = "";

  // Check if old password is empty
  if (empty($old_password)) {
      $old_password_err = "Old password cannot be blank!";
  } elseif (empty($new_password)) {
      $new_password_err = "New password cannot be blank!";
  } elseif (strlen($new_password) < 6) {
      // Check if the new password is at least 6 characters long
      $new_password_err = "New password must be at least 6 characters long!";
  } elseif ($new_password !== $confirm_password) {
      $new_password_err = "New password and confirm password do not match!";
  } else {
      // Fetch the current (hashed) password from the database
      $query = "SELECT password FROM users WHERE id = ?";
      $stmt = $link->prepare($query);
      $stmt->bind_param('i', $user_id);
      $stmt->execute();
      $stmt->bind_result($hashed_password);
      $stmt->fetch();
      $stmt->close();

      // Verify the old password
      if (!password_verify($old_password, $hashed_password)) {
          $old_password_err = "Old password is incorrect!";
      } else {
          // Hash the new password
          $new_hashed_password = password_hash($new_password, PASSWORD_DEFAULT);

          // Update the new password in the database
          $update_query = "UPDATE users SET password = ? WHERE id = ?";
          $stmt = $link->prepare($update_query);
          $stmt->bind_param('si', $new_hashed_password, $user_id);
          if ($stmt->execute()) {
              // Set success message in session
              $_SESSION['success_message'] = "Password changed successfully!";
              header("Location: admin_settings.php");
              exit();
          } else {
              $_SESSION['error_message'] = "Error updating password!";
          }
          $stmt->close();
      }
  }
}

// After redirection, retrieve success or error messages from the session
if (isset($_SESSION['success_message'])) {
  $success_message = $_SESSION['success_message'];
  unset($_SESSION['success_message']);
} elseif (isset($_SESSION['error_message'])) {
  $error_message = $_SESSION['error_message'];
  unset($_SESSION['error_message']);
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Settings Page</title>
  <link rel="icon" href="img/logo_web.png" type="image/png">
  <script src="https://cdn.tailwindcss.com"></script>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">

  <style>
    @media (max-width: 1024px) {
      #sidebar {
        position: fixed;
        top: 0;
        left: -100%;
        height: 100%;
        width: 5rem;
        transition: left 0.3s ease-in-out;
      }
      #sidebar.open {
        left: 0;
      }
      #overlay {
        display: none;
      }
      #overlay.active {
        display: block;
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0,0,0,0.5);
        z-index: 20;
      }
    }

    @media (min-width: 1024px) {
      #sidebar {
        width: 8rem;
      }
    }

    #sidebar img.logo {
      width: 5rem;
    }
  </style>
</head>
<body class="bg-gray-100">

<!-- Modal Structure -->
<div id="successModal" class="fixed inset-0 flex items-center justify-center hidden bg-black bg-opacity-50 z-50">
    <div class="bg-white rounded-lg shadow-lg p-6 w-80">
        <button class="absolute top-2 right-2 text-gray-500 hover:text-gray-800" onclick="closeModal()">&times;</button>
        <p class="text-center text-lg font-semibold" id="modalMessage"></p>
        <div class="flex justify-center mt-4">
            <button onclick="closeModal()" class="bg-blue-500 text-white py-2 px-4 rounded hover:bg-blue-600">Close</button>
        </div>
    </div>
</div>

  <div class="flex">
    <!-- Sidebar -->
    <nav class="h-screen w-30 bg-white shadow-lg flex flex-col items-center pt-6 space-y-8 fixed">
        <div class="px-6">
          <!-- Logo and navigation links -->
          <div class="flex justify-between items-center">
            <img src="img/logo-2x.png" alt="TC Car Rental" class="w-20">
            <button onclick="toggleSidebar()" class="lg:hidden focus:outline-none text-gray-500">
              <i class="fas fa-times fa-lg"></i>
            </button>
          </div>
          <nav class="flex flex-col space-y-8 text-gray-500 mt-14">
          <a href="admin_dashboard.php" class="flex justify-center w-full items-center space-x-2"><img src="img/home.png" alt="Home Icon" class="w-7 h-7"></a>
          <a href="admin_notification.php" class="flex justify-center items-center space-x-2"><img src="img/mail.png" alt="Mail Icon" class="w-6 h-6"></a>
          <a href="admin_settings.php" class="flex justify-center items-center space-x-2"><img src="img/setting (1).png" alt="Settings Icon" class="w-6 h-6"></a>
          <a href="admin_chat.php" class="flex justify-center items-center space-x-2 text-blue-500"><img src="img/bubble-chat.png" alt="Chat Icon" class="w-7 h-7"></a>
          <div class="px-6 py-44">
            <a href="login.php" class="fixed mt-14 flex justify-center items-center space-x-2"><img src="img/logout.png" alt="Logout Icon" class="w-6 h-6"></a>
          </div>
        </nav>
        </div>
      </nav>

    <!-- Overlay for mobile sidebar -->
    <div id="overlay" onclick="closeSidebar()"></div>

    <!-- Main content -->
    <div class="flex-grow pl-6 pr-6 ml-[130px]">
      <!-- Mobile header with hamburger menu -->
      <div class="flex justify-between items-center mb-4 lg:hidden">
        <button onclick="toggleSidebar()" class="focus:outline-none text-gray-500">
          <i class="fas fa-bars fa-2x"></i>
        </button>
        
      </div>
      <div class="text-xl font-bold mb-4">Settings:</div>
      <form action="admin_settings.php" method="POST" enctype="multipart/form-data" class="space-y-6">
      <div class="space-y-6">
        <!-- General Information Card -->
        <div class="bg-white p-6 rounded-lg shadow-lg">
          <div class="text-lg font-bold mb-1">GENERAL</div>      
          <div class="text-sm text-gray-500 mb-4">Public information about your account</div>
          <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 items-center">
        
            <!-- Profile Image -->
            <div class="flex flex-col items-center mb-24">
                <!-- Image Preview Area -->
                <div id="imagePreview" class="h-24 w-24 bg-gray-100 flex items-center justify-center rounded-full overflow-hidden mb-4 border-2 border-gray-300">
                  <img src="<?php echo htmlspecialchars($profile_image ? $profile_image : 'https://via.placeholder.com/100'); ?>" alt="Profile" class="object-cover h-full w-full">
                </div>

                <!-- Hidden file input field -->
                <input type="file" name="profile_image" id="profile_image" class="hidden" accept="image/*">
                
                <!-- Upload button placed under the image preview -->
                <button type="button" class="bg-blue-500 text-white px-2 py-1 justify-center rounded-lg text-sm"
                    onclick="document.getElementById('profile_image').click();">
                    Upload Image
                </button>
            </div>

           <!-- Input Fields -->
          <div class="lg:col-span-2 space-y-4">
              <div class="flex flex-col sm:flex-row items-center">
                  <label class="text-gray-600 text-sm w-full sm:w-1/4 mb-1 sm:mb-0">Full Name</label>
                  <div class="relative w-full">
                      <input type="text" class="w-full border rounded-lg p-2 pr-10 mt-1 sm:mt-0" name="fullname" id="fullname" value="<?php echo htmlspecialchars($fullname); ?>" required>
                      <i class="fas fa-edit absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-500"></i>
                  </div>
              </div>
              <div class="flex flex-col sm:flex-row items-center">
                  <label class="text-gray-600 text-sm w-full sm:w-1/4 mb-1 sm:mb-0">Position</label>
                  <div class="relative w-full">
                      <input type="text" class="w-full border rounded-lg p-2 pr-10 mt-1 sm:mt-0" name="position" id="position" value="<?php echo htmlspecialchars($position); ?>" required>
                      <i class="fas fa-edit absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-500"></i>
                  </div>
              </div>
              <div class="flex flex-col sm:flex-row items-center">
                  <label class="text-gray-600 text-sm w-full sm:w-1/4 mb-1 sm:mb-0">Email</label>
                  <div class="relative w-full">
                      <input type="email" class="w-full border rounded-lg p-2 pr-10 mt-1 sm:mt-0" name="email" id="email" value="<?php echo htmlspecialchars($email); ?>" required>
                      <i class="fas fa-edit absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-500"></i>
                  </div>
              </div>
              <div class="flex flex-col sm:flex-row items-center">
                  <label class="text-gray-600 text-sm w-full sm:w-1/4 mb-1 sm:mb-0">Phone Number</label>
                  <div class="relative w-full">
                      <input type="tel" class="w-full border rounded-lg p-2 pr-10 mt-1 sm:mt-0" name="number" id="number" value="<?php echo htmlspecialchars($number); ?>" required>
                      <i class="fas fa-edit absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-500"></i>
                  </div>
              </div>
              <div class="flex justify-end">
                  <button type="submit" name="update_profile" class="bg-blue-500 text-white px-4 py-2 rounded-lg">Save Changes</button>
              </div>
          </div>
        </div>
      </div>
    </form>

  <form id="passwordChangeForm" method="POST" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">

  <!-- Login Information Card -->
  <div class="bg-white p-6 rounded-lg shadow-lg">
    <div class="text-lg font-bold mb-1">LOGIN INFORMATION</div>
    <div class="text-sm text-gray-500 mb-4">The credentials for authorization</div>
    
    <div class="space-y-4">
      <!-- Old Password Input -->
      <div class="flex flex-col sm:flex-row items-center mb-5">
          <label class="text-gray-600 text-sm w-full sm:w-1/4 mb-1 sm:mb-0">Old Password</label>
          <div class="relative w-full">
              <input type="password" id="oldPasswordInput" name="old_password" class="w-full border rounded-lg p-2 pr-10 mb-1" placeholder="Old Password">
              
              <i class="fas fa-eye absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-500 cursor-pointer" id="toggleOldPassword" onclick="toggleOldPasswordVisibility()"></i>
              <span class="text-red-500 text-sm block absolute"><?php echo $old_password_err; ?></span>
          </div>
      </div>

      <!-- New Password Input -->
      <div class="flex flex-col sm:flex-row items-center mb-5">
          <label class="text-gray-600 text-sm w-full sm:w-1/4 mb-1 sm:mb-0">New Password</label>
          <div class="relative w-full">
              <input type="password" id="newPasswordInput" name="new_password" class="w-full border rounded-lg p-2 pr-10 mb-1" placeholder="New Password">
              
              <i class="fas fa-eye absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-500 cursor-pointer" id="toggleNewPassword" onclick="toggleNewPasswordVisibility()"></i>
              <span class="text-red-500 text-sm block absolute"></span>
          </div>
      </div>

      <!-- Confirm Password Input -->
      <div class="flex flex-col sm:flex-row items-center mb-5">
          <label class="text-gray-600 text-sm w-full sm:w-1/4 mb-1 sm:mb-0">Confirm Password</label>
          <div class="relative w-full">
              <input type="password" id="confirmPasswordInput" name="confirm_password" class="w-full border rounded-lg p-2 pr-10 mb-1" placeholder="Confirm Password">
              
              <i class="fas fa-eye absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-500 cursor-pointer" id="toggleConfirmPassword" onclick="toggleConfirmPasswordVisibility()"></i>
              <span class="text-red-500 text-sm block absolute"><?php echo $new_password_err; ?></span>
          </div>
      </div>

      <div class="flex justify-end">
          <button type="submit" name="change_password" class="bg-blue-500 text-white px-4 py-2 rounded-lg">Change Password</button>
      </div>
    </div>
  </div>
</form>

<script>
  document.getElementById('profile_image').addEventListener('change', function (event) {
    const file = event.target.files[0];
    const reader = new FileReader();

    reader.onload = function (e) {
      const imagePreview = document.getElementById('imagePreview');
      imagePreview.innerHTML = '<img src="' + e.target.result + '" alt="Profile Image" class="object-cover h-full w-full rounded-full">';
    };

    // Check if a file is selected
    if (file) {
    reader.readAsDataURL(file);
    } else {
    // Reset the preview if no file is selected
    document.getElementById('imagePreview').innerHTML = '<img src="https://via.placeholder.com/100" alt="Profile" class="object-cover h-full w-full">';
    }
  });

  function toggleOldPasswordVisibility() {
    const passwordInput = document.getElementById('oldPasswordInput');
    const togglePassword = document.getElementById('toggleOldPassword');
      
    if (passwordInput.type === 'password') {
      passwordInput.type = 'text';
      togglePassword.classList.remove('fa-eye');
      togglePassword.classList.add('fa-eye-slash');
    } else {
      passwordInput.type = 'password';
      togglePassword.classList.remove('fa-eye-slash');
      togglePassword.classList.add('fa-eye');
    }
  }

  function toggleNewPasswordVisibility() {
    const passwordInput = document.getElementById('newPasswordInput');
    const togglePassword = document.getElementById('toggleNewPassword');
      
    if (passwordInput.type === 'password') {
      passwordInput.type = 'text';
      togglePassword.classList.remove('fa-eye');
      togglePassword.classList.add('fa-eye-slash');
    } else {
      passwordInput.type = 'password';
      togglePassword.classList.remove('fa-eye-slash');
      togglePassword.classList.add('fa-eye');
    }
  }

  function toggleConfirmPasswordVisibility() {
    const confirmPasswordInput = document.getElementById('confirmPasswordInput');
    const togglePassword = document.getElementById('toggleConfirmPassword');
      
    if (confirmPasswordInput.type === 'password') {
      confirmPasswordInput.type = 'text';
      togglePassword.classList.remove('fa-eye');
      togglePassword.classList.add('fa-eye-slash');
    } else {
      confirmPasswordInput.type = 'password';
      togglePassword.classList.remove('fa-eye-slash');
      togglePassword.classList.add('fa-eye');
    }
  }

  function toggleSidebar() {
    const sidebar = document.getElementById('sidebar');
    const overlay = document.getElementById('overlay');
    sidebar.classList.toggle('open');
    overlay.classList.toggle('active');
  }

  // Close sidebar when clicking outside on mobile
  function closeSidebar() {
    const sidebar = document.getElementById('sidebar');
    const overlay = document.getElementById('overlay');
    sidebar.classList.remove('open');
    overlay.classList.remove('active');
  }
  document.addEventListener('DOMContentLoaded', function () {
  <?php if ($success_message): ?>
      document.getElementById('modalMessage').innerText = "<?php echo htmlspecialchars($success_message); ?>";
      document.getElementById('successModal').classList.remove('hidden');
      document.getElementById('modalHeader').innerText = "Success";
  <?php elseif ($error_message): ?>
      document.getElementById('modalMessage').innerText = "<?php echo htmlspecialchars($error_message); ?>";
      document.getElementById('successModal').classList.remove('hidden');
      document.getElementById('modalHeader').innerText = "Error";
  <?php endif; ?>
  });

  function closeModal() {
    document.getElementById('successModal').classList.add('hidden');
  }
</script>
</body>
</html>
