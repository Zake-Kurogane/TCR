<?php
session_start();
require_once 'config.php'; // Include your database configuration

// Check if the user is logged in, if not then redirect him to login page
if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true){
  header("location: login.php");
  exit;
}

// Initialize variables
$fullname = '';
$email = '';
$number = '';
$birthdate = '';
$profile_image = '';
$front_license_image = '';
$back_license_image = '';
$success_message = '';
$error_message = '';

// Fetch user data
$user_id = $_SESSION['id'];
$query = "SELECT * FROM users WHERE id = ?";
$stmt = $link->prepare($query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $fullname = $row['fullname'];
    $email = $row['email'];
    $number = $row['number'];
    $birthdate = $row['birthdate'];
    $profile_image = $row['profile_image'];
    $password = $row['password']; 
    $front_license_image = $row['license_front_image'];
    $back_license_image = $row['license_back_image'];
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update_profile'])) {
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
                $profile_image = $upload_file; // Use the new image path
            } else {
                $error_message = 'Error moving uploaded file.';
            }
        } else {
            $error_message = 'Invalid file type. Only JPG, PNG, and GIF files are allowed.';
        }
    }

    // Retrieve other fields
    $fullname = $_POST['fullname'];
    $email = $_POST['email'];
    $number = $_POST['number'];
    $birthdate = $_POST['birthdate'];
    $password = $_POST['password'];

    // Basic validation
    if (empty($fullname) || empty($email) || empty($number) || empty($birthdate)) {
        $_SESSION['error_message'] = "All fields are required.";
        header("Location: customer_profile.php");
        exit();
    }

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
    }

    if (empty($error_message)) {
        // Prepare the update query
        if (!empty($password)) {
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $update_query = "UPDATE users SET fullname = ?, email = ?, number = ?, birthdate = ?, profile_image = ?, password = ? WHERE id = ?";
            $stmt = $link->prepare($update_query);
            $stmt->bind_param("ssssssi", $fullname, $email, $number, $birthdate, $profile_image, $hashed_password, $user_id);
        } else {
            $update_query = "UPDATE users SET fullname = ?, email = ?, number = ?, birthdate = ?, profile_image = ? WHERE id = ?";
            $stmt = $link->prepare($update_query);
            $stmt->bind_param("ssssi", $fullname, $email, $number, $birthdate, $profile_image, $user_id);
        }

        // Execute the statement
        if ($stmt->execute()) {
            $_SESSION['success_message'] = "Profile updated successfully!";
            header("Location: customer_profile.php");
            exit();
        } else {
            $_SESSION['error_message'] = "Error updating profile: " . $stmt->error;
        }
    }
}

// Fetch updated user data again if there was an error
if (isset($error_message)) {
    $query = "SELECT * FROM users WHERE id = ?";
    $stmt = $link->prepare($query);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $fullname = $row['fullname'];
        $email = $row['email'];
        $number = $row['number'];
        $birthdate = $row['birthdate'];
        $profile_image = $row['profile_image'];
        $password = $row['password']; 
        $front_license_image = $row['license_front_image'];
        $back_license_image = $row['license_back_image'];
    }
    $stmt->close();
}

// Handle Front License Upload
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update_front_license'])) {
  if (isset($_FILES['license_front']) && $_FILES['license_front']['error'] == UPLOAD_ERR_OK) {
      $front_image_tmp_name = $_FILES['license_front']['tmp_name'];
      $front_image_name = 'front_' . time() . '_' . basename($_FILES['license_front']['name']);
      $front_image_path = 'licenses/' . $front_image_name;

      // Validate image type
      $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
      if (in_array($_FILES['license_front']['type'], $allowed_types)) {
          if (move_uploaded_file($front_image_tmp_name, $front_image_path)) {
              $update_query = "UPDATE users SET license_front_image = ? WHERE id = ?";
              $stmt = $link->prepare($update_query);
              $stmt->bind_param("si", $front_image_path, $user_id);
              
              if ($stmt->execute()) {
                  $_SESSION['success_message'] = "Front driver's license updated successfully!";
                  header("Location: customer_profile.php");
                  exit();
              } else {
                  $_SESSION['error_message'] = "Error updating front driver's license: " . $stmt->error;
              }
          } else {
              $_SESSION['error_message'] = 'Error moving uploaded front license image.';
          }
      } else {
          $_SESSION['error_message'] = 'Invalid front license image type. Only JPG, PNG, and GIF files are allowed.';
      }
  }
}

// Handle Back License Upload
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update_back_license'])) {
  if (isset($_FILES['license_back']) && $_FILES['license_back']['error'] == UPLOAD_ERR_OK) {
      $back_image_tmp_name = $_FILES['license_back']['tmp_name'];
      $back_image_name = 'back_' . time() . '_' . basename($_FILES['license_back']['name']);
      $back_image_path = 'licenses/' . $back_image_name;

      // Validate image type
      $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
      if (in_array($_FILES['license_back']['type'], $allowed_types)) {
          if (move_uploaded_file($back_image_tmp_name, $back_image_path)) {
              $update_query = "UPDATE users SET license_back_image = ? WHERE id = ?";
              $stmt = $link->prepare($update_query);
              $stmt->bind_param("si", $back_image_path, $user_id);
              
              if ($stmt->execute()) {
                  $_SESSION['success_message'] = "Back driver's license updated successfully!";
                  header("Location: customer_profile.php");
                  exit();
              } else {
                  $_SESSION['error_message'] = "Error updating back driver's license: " . $stmt->error;
              }
          } else {
              $_SESSION['error_message'] = 'Error moving uploaded back license image.';
          }
      } else {
          $_SESSION['error_message'] = 'Invalid back license image type. Only JPG, PNG, and GIF files are allowed.';
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
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Account Information - TC Car Rental</title>
    <link rel="icon" href="img/logo_web.png" type="image/png">
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
<style>
/* Hide the default date picker icon for Chrome and other WebKit browsers */
input[type="date"]::-webkit-calendar-picker-indicator {
    opacity: 0;
    pointer-events: none;
}

/* Hide the default icon for Firefox */
input[type="date"]::-moz-calendar-picker-indicator {
    opacity: 0;
    pointer-events: none;
}

/* Hide the default icon for Edge */
input[type="date"]::-ms-expand {
    display: none;
}

/* Allow the input field to still function as a date picker */
.date-picker-input {
    cursor: pointer;
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

    <!-- Main container -->
    <div class="flex min-h-screen">

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
            <a href="customer_dashboard.php" class="flex justify-center items-center space-x-2"><img src="img/home.png" alt="Home Icon" class="w-7 h-7"></a>
            <a href="customer_calendar.php" class="flex justify-center items-center space-x-2"><img src="img/calendar.png" alt="Mail Icon" class="w-6 h-6"></a>
            <a href="customer_favourites.php" class="flex justify-center items-center space-x-2"><img src="img/heart.png" alt="Settings Icon" class="w-6 h-6"></a>
            <a href="customer_recents.php" class="flex justify-center items-center space-x-2 text-blue-500"><img src="img/watch.png" alt="Chat Icon" class="w-7 h-7"></a>
            <a href="customer_profile.php" class="flex justify-center items-center space-x-2 text-blue-500"><img src="img/id-card.png" alt="Chat Icon" class="w-7 h-7"></a>
            <a href="customer_chat.php" class="flex justify-center items-center space-x-2 text-blue-500"><img src="img/bubble-chat.png" alt="Chat Icon" class="w-6 h-6"></a>
            <div class="px-6 py-2">
              <a href="logout.php" class="hover:text-blue-500 fixed mt-28 flex justify-center items-center space-x-2"><img src="img/logout.png" alt="Logout Icon" class="w-6 h-6"></a>
            </div>
          </nav>
        </div>
      </nav>
        <!-- Main content -->
        <div class="flex-grow p-6 ml-40">
            <!-- Account Information and Driver's License -->
            <div class="text-xl font-bold mb-4">Settings:</div>

            <form action="customer_profile.php" method="POST" enctype="multipart/form-data">
            <!-- General Information Card -->
            <div class="bg-white p-6 rounded-lg shadow-lg mb-6 ml-20 mr-20">
              <div class="text-lg font-bold mb-1">GENERAL</div>      
              <div class="text-sm text-gray-500 mb-4">Public information about your account</div>
              <div class="grid grid-cols-3 gap-4 items-center">
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
                <div class="col-span-2">
                  <div class="flex items-center space-x-4 mr-10">
                      <label class="text-gray-600 text-sm w-1/4">Full Name</label>
                      <div class="relative w-full">
                        <input type="text" class="w-full border rounded-lg p-2 pr-10 mt-1" id="fullname" name="fullname" value="<?php echo htmlspecialchars($fullname); ?>" required>
                        <i class="fas fa-edit absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-500"></i>
                      </div>
                  </div>
                  <div class="flex items-center space-x-4 mt-4 mr-10">
                      <label class="text-gray-600 text-sm w-1/4">Email</label>
                      <div class="relative w-full">
                        <input type="email" class="w-full border rounded-lg p-2 pr-10 mt-1" id="email" name="email" value="<?php echo htmlspecialchars($email); ?>" required>
                        <i class="fas fa-edit absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-500"></i>
                      </div>
                  </div>
                  <div class="flex items-center space-x-4 mt-4 mr-10">
                      <label class="text-gray-600 text-sm w-1/4">Phone Number</label>
                      <div class="relative w-full">
                        <input type="text" class="w-full border rounded-lg p-2 pr-10 mt-1" id="number" name="number" value="<?php echo htmlspecialchars(string: $number); ?>" requireds>
                        <i class="fas fa-edit absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-500"></i>
                      </div>
                  </div>
                  
                  <div class="flex items-center space-x-4 mt-4 mr-10">
                      <label class="text-gray-600 text-sm w-1/4">Birthday</label>
                      <div class="relative w-full">
                          <!-- Date input with hidden default icon -->
                          <input type="date" id="birthday" class="w-full border rounded-lg p-2 pr-10 mt-1 hide-default-icon cursor-pointer" id="birthdate" name="birthdate" value="<?php echo htmlspecialchars($birthdate); ?>">
                          <!-- Custom calendar icon -->
                          <i class="fas fa-calendar-alt absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-500 cursor-pointer"></i>
                      </div>
                  </div>

              
                  <div class="flex items-center space-x-4 mt-4 mr-10">
                      <label class="text-gray-600 text-sm w-1/4">Password</label>
                      <div class="relative w-full">
                        <input type="password" id="password" name="password" class="w-full border rounded-lg p-2 pr-10 mt-1" value="<?php echo htmlspecialchars(string: $password); ?>" required>
                        <i class="fas fa-eye absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-500 cursor-pointer" id="togglePassword" onclick="togglePasswordVisibility()"></i>
                      </div>
                  </div>
                  <div class="flex justify-end mt-6 mr-10">
                    <button type="submit" name="update_profile" class="bg-blue-500 text-white px-4 py-2 rounded-lg">Save Changes</button>
                </div>
              </div>
              </form>
            </div>
            
          </div>
                
          <!-- Driver's License Section -->
          <div class="bg-white p-6 rounded-lg shadow-lg mb-6 ml-20 mr-20">
              <h2 class="text-2xl font-semibold mb-1">Driver's License</h2>
              <div class="text-sm text-gray-500 mb-4">The requirement for booking a car</div>
              <div class="grid grid-cols-2 gap-6">
                  <!-- Front License Upload Form -->
                  <form method="POST" enctype="multipart/form-data" id="frontLicenseForm">
                      <div class="flex flex-col items-center">
                          <div class="relative w-full h-72 bg-gray-300 mb-4 rounded-md cursor-pointer" id="frontPreview" 
                              style="background-image: url('<?php echo htmlspecialchars($front_license_image); ?>'); background-size: cover; background-position: center; background-repeat: no-repeat;" 
                              onclick="document.getElementById('license_front').click();">
                              <input type="file" name="license_front" id="license_front" accept="image/*" class="hidden" onchange="previewImage(event, 'frontPreview')">
                              
                          </div>
                          <button type="submit" name="update_front_license" class="bg-blue-500 text-white px-4 py-2 rounded shadow hover:bg-blue-600 mt-1">
                              <i class="fas fa-upload"></i> Upload Photo (Front)
                          </button>
                      </div>
                  </form>

                  <!-- Back License Upload Form -->
                  <form method="POST" enctype="multipart/form-data" id="backLicenseForm">
                      <div class="flex flex-col items-center">
                          <div class="relative w-full h-72 bg-gray-300 mb-4 rounded-md cursor-pointer" id="backPreview" 
                              style="background-image: url('<?php echo htmlspecialchars($back_license_image); ?>'); background-size: cover; background-position: center; background-repeat: no-repeat;" 
                              onclick="document.getElementById('license_back').click();">
                              <input type="file" name="license_back" id="license_back" accept="image/*" class="hidden" onchange="previewImage(event, 'backPreview')">
                          
                          </div>
                          <button type="submit" name="update_back_license" class="bg-blue-500 text-white px-4 py-2 rounded shadow hover:bg-blue-600 mt-1">
                              <i class="fas fa-upload"></i> Upload Photo (Back)
                          </button>
                      </div>
                  </form>
              </div>
          </div>           
        </div>
    </div>
<script>
  // Preview Driver's License Images
  function previewImage(event, previewId) {
    const file = event.target.files[0];
    const reader = new FileReader();

    reader.onload = function(e) {
        document.getElementById(previewId).style.backgroundImage = `url(${e.target.result})`;
    }

    if (file) {
        reader.readAsDataURL(file);
    }
}

  // Toggle sidebar visibility
  function toggleSidebar() {
      document.getElementById('sidebar').classList.toggle('-translate-x-full');
      document.getElementById('overlay').classList.toggle('hidden');
  }

  function togglePasswordVisibility() {
      const passwordInput = document.getElementById('password');
      const togglePassword = document.getElementById('togglePassword');

      // Check the current type of the password input
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

  // Ensure the date picker works when the input is clicked
  document.getElementById('birthday').addEventListener('click', function() {
    this.showPicker();
  });

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