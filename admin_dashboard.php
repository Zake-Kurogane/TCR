<?php
session_start();
include('config.php');

// Check if the user is logged in and is an admin
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true || $_SESSION["role"] !== 'admin') {
    header("location: login.php");
    exit;
}

// Fetch user information once at the start
$resultf = mysqli_query($link, "SELECT * FROM users WHERE id='" . $_SESSION['id'] . "'");
$rowf = mysqli_fetch_array($resultf);


// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Validate and set form data
    $car_status = $_POST['car_status'] ?? '';
    $car_brand = $_POST['car_brand'] ?? '';
    $body_type = $_POST['body_type'] ?? '';
    $car_description = $_POST['car_description'] ?? '';
    $rent_price = $_POST['rent_price'] ?? '';
    $transmission = $_POST['transmission'] ?? '';
    $fuel_type = $_POST['fuel_type'] ?? '';

    //image types
    $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];

    // Check if it's an insert (add new car)
    if (!isset($_POST['id'])) {
        // Handle the image upload
        if (isset($_FILES['car_image']) && $_FILES['car_image']['error'] == UPLOAD_ERR_OK) {
            $image_tmp_name = $_FILES['car_image']['tmp_name'];
            $image_name = $_FILES['car_image']['name'];
            $image_type = $_FILES['car_image']['type'];
            $upload_dir = 'uploads/';
            $upload_file = $upload_dir . basename($image_name);

            // Validate image type
            if (in_array($image_type, $allowed_types)) {
                // Move the uploaded file
                if (move_uploaded_file($image_tmp_name, $upload_file)) {
                    // Prepare SQL query for inserting new car
                    $sql = "INSERT INTO cars (car_status, car_brand, car_description, rent_price, body_type, transmission, fuel_type, car_image) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
                    $stmt = $link->prepare($sql);
                    if ($stmt) {
                        $stmt->bind_param("ssssssss", $car_status, $car_brand, $car_description, $rent_price, $body_type, $transmission, $fuel_type, $upload_file);

                        // Execute the statement
                        if ($stmt->execute()) {
                            $_SESSION['success_message'] = "New car added successfully!";
                        } else {
                            $_SESSION['error_message'] = "Error executing query: " . $stmt->error;
                        }
                        $stmt->close();
                    } else {
                        $_SESSION['error_message'] = "Error preparing statement: " . $link->error;
                    }
                } else {
                    $_SESSION['error_message'] = "Error moving uploaded file.";
                }
            } else {
                $_SESSION['error_message'] = "Invalid file type. Only JPG, PNG, and GIF files are allowed.";
            }
        } else {
            $_SESSION['error_message'] = "No file uploaded or upload error.";
        }
        header("Location: admin_dashboard.php");
        exit();
    }

    //update (edit car info)
    if (isset($_POST['id'])) {
        $id = $_POST['id'];
        $car_status = $_POST['car_status_edit'] ?? '';
        $car_brand = $_POST['car_brand_edit'] ?? '';
        $body_type = $_POST['body_type_edit'] ?? '';
        $car_description = $_POST['car_description_edit'] ?? '';
        $rent_price = $_POST['rent_price_edit'] ?? '';
        $transmission = $_POST['transmission_edit'] ?? '';
        $fuel_type = $_POST['fuel_type_edit'] ?? '';

        // Check if a new image is uploaded
        $upload_file = null; // Initialize in case of no new upload
        if (isset($_FILES['car_image_edit']) && $_FILES['car_image_edit']['error'] == UPLOAD_ERR_OK) {
            $image_tmp_name = $_FILES['car_image_edit']['tmp_name'];
            $image_name = $_FILES['car_image_edit']['name'];
            $image_type = $_FILES['car_image_edit']['type'];
            $upload_dir = 'uploads/';
            $upload_file = $upload_dir . basename($image_name);

            // Validate image type
            if (in_array($image_type, $allowed_types)) {
                // Move the uploaded file
                if (!move_uploaded_file($image_tmp_name, $upload_file)) {
                    $_SESSION['error_message'] = "Error moving uploaded file.";
                    header("Location: admin_dashboard.php");
                    exit();
                }
            } else {
                $_SESSION['error_message'] = "Invalid file type. Only JPG, PNG, and GIF files are allowed.";
                header("Location: admin_dashboard.php");
                exit();
            }
        }

        //updating car
        if ($upload_file) {
            // Update SQL query including the new image
            $sql = "UPDATE cars SET car_status=?, car_brand=?, car_description=?, rent_price=?, body_type=?, transmission=?, fuel_type=?, car_image=? WHERE id=?";
            $stmt = $link->prepare($sql);
            if ($stmt) {
                $stmt->bind_param("ssssssssi", $car_status, $car_brand, $car_description, $rent_price, $body_type, $transmission, $fuel_type, $upload_file, $id);
            } else {
                $_SESSION['error_message'] = "Error preparing statement: " . $link->error;
                header("Location: admin_dashboard.php");
                exit();
            }
        } else {
            // If no new image is uploaded, keep the existing one
            $sql = "UPDATE cars SET car_status=?, car_brand=?, car_description=?, rent_price=?, body_type=?, transmission=?, fuel_type=? WHERE id=?";
            $stmt = $link->prepare($sql);
            if ($stmt) {
                $stmt->bind_param("sssssssi", $car_status, $car_brand, $car_description, $rent_price, $body_type, $transmission, $fuel_type, $id);
            } else {
                $_SESSION['error_message'] = "Error preparing statement: " . $link->error;
                header("Location: admin_dashboard.php");
                exit();
            }
        }

        // Execute the statement
        if ($stmt->execute()) {
            $_SESSION['success_message'] = "Car record updated successfully!";
        } else {
            $_SESSION['error_message'] = "Error executing query: " . $stmt->error;
        }
        $stmt->close();

        header("Location: admin_dashboard.php");
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>TC Car Rental</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <link rel="icon" href="img/logo_web.png" type="image/png">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
  <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">


<style>

input[type=number]::-webkit-inner-spin-button,
input[type=number]::-webkit-outer-spin-button {
    -webkit-appearance: none;
    margin: 0;
}

input[type=number] {
    -moz-appearance: textfield;
}

select {
    -webkit-appearance: none; /* Safari */
    -moz-appearance: none; /* Firefox */
    appearance: none; /* Modern browsers */
    background-image: none; /* Remove background image if any */
    border: 1px solid #ccc; /* Optional: custom border */
    padding: 10px; /* Optional: padding */
}

.custom-select {
    position: relative;
}

.custom-select::after {
    content: '';
    position: absolute;
    right: 10px;
    top: 50%;
    transform: translateY(-50%);
    width: 10px;
    height: 10px;
    border-left: 2px solid #333;
    border-top: 2px solid #333;
    transform: rotate(135deg);
}

.filter-btn {
            background-color: #e5e7eb;
            border-radius: 0 80% 80% 0;
            position: absolute;
            top: 10%;
            left: 130px;
            height: 50px;
            width: 20px;
            cursor: pointer;
            display: flex;
            justify-content: center;
            align-items: center;
            transition: background-color 0.3s ease;
        }
        .filter-btn:hover {
            background-color: #d1d5db;
        }

        .filter-btn svg {
            fill: black;
            width: 16px;
            height: 16px;
        }

</style>
</head>
<body class="bg-gray-100">

<!-- success modal -->
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
  <div class="flex">

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
          <a href="admin_dashboard.php" class="flex justify-center w-full items-center space-x-2"><img src="img/home (1).png" alt="Home Icon" class="w-7 h-7"></a>
          <a href="admin_notification.php" class="flex justify-center items-center space-x-2"><img src="img/mail.png" alt="Mail Icon" class="w-6 h-6"></a>
          <a href="admin_settings.php" class="flex justify-center items-center space-x-2"><img src="img/setting (2).png" alt="Settings Icon" class="w-6 h-6"></a>
          <a href="admin_chat.php" class="flex justify-center items-center space-x-2 text-blue-500"><img src="img/bubble-chat.png" alt="Chat Icon" class="w-7 h-7"></a>
          <div class="px-6 py-44">
            <a href="login.php" class="fixed mt-14 flex justify-center items-center space-x-2"><img src="img/logout.png" alt="Logout Icon" class="w-6 h-6"></a>
          </div>
        </nav>
        </div>
      </nav>

    <!-- Overlay for mobile sidebar -->
    <div id="overlay" class="fixed inset-0 bg-black bg-opacity-50 z-20 lg:hidden hidden" onclick="toggleSidebar()"></div>

    <!-- Main content -->
    <div class="flex-grow pl-6 pr-6 ml-[130px]">
      <div class="absolute top-4 left-4">
      </div>

    <!-- Filter Button -->
  <div class="filter-btn fixed" onclick="toggleFilterPanel()">
    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" class="h-4 w-4">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2a1 1 0 01-.293.707l-7.414 7.414A1 1 0 0112 14v5a1 1 0 01-.553.894l-2 1A1 1 0 018 20v-6.586a1 1 0 01.293-.707l7.414-7.414A1 1 0 0116 6V4H4v2a1 1 0 01-.293.707L1.293 4.293A1 1 0 013 4z"/>
    </svg>
  </div>

<!-- Filter Panel -->
<div id="filterPanel" class="hidden fixed top-0 h-full w-70 bg-white shadow-lg p-4 overflow-y-auto" style="left: 8.5%;">
    <div class="flex justify-end">
        <button class="close-btn" onclick="toggleFilterPanel()" style="background:none; border:none;">&times;</button>
    </div>

    <div class="w-full flex justify-between pb-2 pt-2">
        <h2 class="text-lg font-semibold">Filter by:</h2>
        <button class="text-sm text-gray-500 underline" onclick="resetFilters()" style="background:none; border:none;">Reset</button>
    </div>

    <!-- Search -->
    <div class="mb-4">
        <form id="searchForm" action="admin_dashboard.php" method="GET">
            <input class="w-full h-6 pl-2 border-none focus:outline-none" type="text" name="search_query" id="searchInput" placeholder="Search..." 
                   value="<?php echo isset($_GET['search_query']) ? htmlspecialchars($_GET['search_query']) : ''; ?>">

            <div class="my-2 border-t border-gray-500 mb-4"></div>        
            <!-- Car Brand -->
            <div class="mb-4">
                <h3 class="text-sm font-semibold">Car Brand:</h3>
                <div class="grid grid-cols-2 gap-2 mt-2">
                    <?php 
                    $selected_brands = isset($_GET['brand']) ? $_GET['brand'] : [];
                    $brands = ['Toyota', 'Mitsubishi', 'Honda', 'Suzuki', 'Nissan', 'Kia', 'Ford', 'Hyundai'];
                    foreach ($brands as $brand): 
                        $checked = in_array($brand, $selected_brands) ? 'checked' : ''; 
                    ?>
                    <label class="flex items-center">
                        <input type="checkbox" class="form-checkbox text-blue-500 mr-2" name="brand[]" value="<?php echo $brand; ?>" <?php echo $checked; ?>> <?php echo $brand; ?>
                    </label>
                    <?php endforeach; ?>
                </div>
            </div>

            <div class="my-2 border-t border-gray-500 mb-4"></div>

            <!-- Body Type -->
            <div class="mb-4">
                <h3 class="text-sm font-semibold">Body Type:</h3>
                <div class="grid grid-cols-2 gap-2 mt-2">
                    <?php 
                    $selected_body_types = isset($_GET['body_type']) ? $_GET['body_type'] : [];
                    $body_styles = ['Sedan', 'Wagon', 'Coupe', 'Hatchback', 'Pickup', 'SUV', 'Crossover', 'Van'];
                    foreach ($body_styles as $body_style): 
                        $checked = in_array($body_style, $selected_body_types) ? 'checked' : ''; 
                    ?>
                    <label class="flex items-center">
                        <input type="checkbox" class="form-checkbox text-blue-500 mr-2" name="body_type[]" value="<?php echo $body_style; ?>" <?php echo $checked; ?>> <?php echo $body_style; ?>
                    </label>
                    <?php endforeach; ?>
                </div>
            </div>

            <div class="my-2 border-t border-gray-500 mb-4"></div>

            <!-- Transmission -->
            <div class="mb-4">
                <h3 class="text-sm font-semibold">Transmission:</h3>
                <div class="flex space-x-2 mt-2">
                    <input type="checkbox" name="transmission[]" value="Manual" <?php echo (isset($_GET['transmission']) && in_array('Manual', $_GET['transmission'])) ? 'checked' : ''; ?>> Manual
                    <input type="checkbox" name="transmission[]" value="Automatic" <?php echo (isset($_GET['transmission']) && in_array('Automatic', $_GET['transmission'])) ? 'checked' : ''; ?>> Automatic
                </div>
            </div>

            <div class="my-2 border-t border-gray-500 mb-4"></div>

            <!-- Fuel Type -->
            <div class="mb-4">
                <h3 class="text-sm font-semibold">Fuel Type:</h3>
                <div class="flex space-x-2 mt-2">
                    <input type="checkbox" name="fuel_type[]" value="Gasoline" <?php echo (isset($_GET['fuel_type']) && in_array('Gasoline', $_GET['fuel_type'])) ? 'checked' : ''; ?>> Gasoline
                    <input type="checkbox" name="fuel_type[]" value="Diesel" <?php echo (isset($_GET['fuel_type']) && in_array('Diesel', $_GET['fuel_type'])) ? 'checked' : ''; ?>> Diesel
                    <input type="checkbox" name="fuel_type[]" value="Electric" <?php echo (isset($_GET['fuel_type']) && in_array('Electric', $_GET['fuel_type'])) ? 'checked' : ''; ?>> Electric
                </div>
            </div>
        </form>
    </div>
</div>

<main class="flex-1 overflow-y-auto py-6 overflow-x-auto">
    <div class="flex justify-between items-center mx-6">
        <h1 class="text-2xl font-semibold">Cars:</h1>
        <div class="flex items-center bg-gray-100 rounded-full px-4 py-2 shadow">
    <!-- Profile Image -->
    <div class="h-10 w-10 mr-3">
    <img src="<?php echo htmlspecialchars($rowf['profile_image'] ? $rowf['profile_image'] : 'https://via.placeholder.com/100'); ?>" 
         alt="Profile" 
         class="object-cover h-full w-full rounded-full">
</div>
    <!-- Full Name -->
    <span class="text-lg font-medium"><?php echo htmlspecialchars($rowf['fullname']); ?></span>
</div>

    </div>

    <div id="results"></div>

    <button onclick="toggleModal()" class="fixed bottom-5 right-5 mr-10 bg-gray-400 text-black rounded-full w-16 h-16 flex items-center justify-center shadow-lg hover:bg-blue-600 transition-all duration-300">
        <i class="fas fa-plus text-xl"></i>
    </button>

    <!-- Modal Backdrop -->
    <div id="modal-backdrop" class="hidden fixed inset-0 bg-gray-900 bg-opacity-50 z-40"></div>



<!-- Modal Content -->
<div id="modal" class="hidden fixed inset-0 z-50 flex items-center justify-center">
    <div class="fixed inset-0 flex items-center justify-center bg-black bg-opacity-50">
        <!-- Modal Box -->
        <div class="bg-white rounded-lg shadow-lg p-8 w-full max-w-4xl relative">
            <button id="closeButton" class="absolute top-4 right-4 text-gray-400 hover:text-gray-600">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>

            <!-- Modal Header -->
            <h2 class="text-xl font-bold mb-6">NEW CAR INFO</h2>

            <div class="flex flex-wrap md:flex-nowrap space-x-0 md:space-x-6">
                <!-- Form Section -->
                <div class="container">
                    <form id="carForm" method="post" enctype="multipart/form-data">
                        <div class="flex flex-wrap md:flex-nowrap space-x-0 md:space-x-6">
                            <!-- Image Upload Section -->
                            <div class="flex flex-col items-center w-full md:w-1/3 mb-6 md:mb-0 relative">
                                <div id="imagePreview" class="border-2 border-gray-300 bg-gray-100 rounded-lg flex items-center justify-center h-48 w-full mb-4">
                                    <span class="text-gray-500">Add Photo</span>
                                </div>
                                <input type="file" name="car_image" id="car_image" class="hidden" accept="image/*">
                                <button type="button" class="text-blue-500" onclick="document.getElementById('car_image').click();">Upload Image</button>
                                <!-- Error Icon -->
                                <div id="image_error_icon" class="absolute right-2 top-2 text-red-500 hidden">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M12 4.75a7.25 7.25 0 100 14.5 7.25 7.25 0 000-14.5z" />
                                    </svg>
                                </div>
                            </div>

                            <div class="w-full md:w-2/3">
                                <!-- Car Status (Dropdown) -->
                                <div class="mb-4 relative">
                                    <select id="car_status" name="car_status" class="pl-10 pr-4 py-2 w-full border border-gray-300 rounded-lg focus:outline-none focus:border-blue-500">
                                        <option value="" disabled selected hidden>Select Car Status</option>
                                        <option value="Available">Available</option>
                                        <option value="Unavailable">Unavailable</option>
                                        <option value="Unavailable">Booked</option>
                                    </select>
                                    <div class="absolute left-2 top-2 text-gray-400">
                                        <img src="img/car_status.png" alt="Car Icon" class="w-6 h-6">
                                    </div>
                                    <div id="car_status_error" class="absolute right-2 top-2 text-red-500 hidden">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M12 4.75a7.25 7.25 0 100 14.5 7.25 7.25 0 000-14.5z" />
                                        </svg>
                                    </div>
                                </div>

                                <!-- Car Brand -->
                                <div class="mb-4 relative">
                                <input type="text" id="car_brand" name="car_brand" placeholder="Type or Select Car Brand" class="pl-10 pr-4 py-2 w-full border border-gray-300 rounded-lg focus:outline-none focus:border-blue-500" list="car_brands">
                                    <datalist id="car_brands">
                                    <option value="Ford">Ford</option>
                                    <option value="Honda">Honda</option>
                                    <option value="Hyundai">Hyundai</option>
                                    <option value="Kia">Kia</option>
                                    <option value="Mitsubishi">Mitsubishi</option>
                                    <option value="Nissan">Nissan</option>
                                    <option value="Suzuki">Suzuki</option>
                                    <option value="Toyota">Toyota</option>
                                    </datalist>
                                    <div class="absolute left-2 top-2 text-gray-400">
                                        <img src="img/car.png" alt="Car Icon" class="w-6 h-6">
                                    </div>
                                    <div id="car_brand_error" class="absolute right-2 top-2 text-red-500 hidden">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M12 4.75a7.25 7.25 0 100 14.5 7.25 7.25 0 000-14.5z" />
                                        </svg>
                                    </div>
                                </div>

                                <!-- Car Description -->
                                <div class="mb-4 relative">
                                    <input type="text" id="car_description" name="car_description" placeholder="Car Description" class="pl-10 pr-4 py-2 w-full border border-gray-300 rounded-lg focus:outline-none focus:border-blue-500">
                                    <div class="absolute left-2 top-2 text-gray-400">
                                        <img src="img/car_desc.png" alt="Car Icon" class="w-6 h-6">
                                    </div>
                                    <div id="car_description_error" class="absolute right-2 top-2 text-red-500 hidden">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M12 4.75a7.25 7.25 0 100 14.5 7.25 7.25 0 000-14.5z" />
                                        </svg>
                                    </div>
                                </div>

                                <!-- Rent Price -->
                                <div class="mb-4 relative">
                                    <input type="number" id="rent_price" name="rent_price" placeholder="Rent Price" class="pl-10 pr-4 py-2 w-full border border-gray-300 rounded-lg focus:outline-none focus:border-blue-500">
                                    <div class="absolute left-2 top-2 text-gray-400">
                                        <img src="img/car_price.png" alt="Car Icon" class="w-6 h-6">
                                    </div>
                                    <div id="rent_price_error" class="absolute right-2 top-2 text-red-500 hidden">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M12 4.75a7.25 7.25 0 100 14.5 7.25 7.25 0 000-14.5z" />
                                        </svg>
                                    </div>
                                </div>

                                <!-- Body Type -->
                                <div class="mb-4 relative">
                                    <select id="body_type" name="body_type" class="pl-10 pr-4 py-2 w-full border border-gray-300 rounded-lg focus:outline-none focus:border-blue-500">
                                        <option value="" disabled selected hidden>Select Body Type</option> <!-- Default option -->
                                        <option value="Coupe">Coupe</option>
                                        <option value="Crossover">Crossover</option>
                                        <option value="Hatchback">Hatchback</option>
                                        <option value="Pickup">Pickup</option>
                                        <option value="Sedan">Sedan</option>
                                        <option value="SUV">SUV</option>
                                        <option value="Van">Van</option>
                                        <option value="Wagon">Wagon</option>
                                    </select>
                                    <div class="absolute left-2 top-2 text-gray-400">
                                        <img src="img/vehicle1.png" alt="Body Type Icon" class="w-6 h-6">
                                    </div>
                                    <div id="body_type_error" class="absolute right-2 top-2 text-red-500 hidden">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M12 4.75a7.25 7.25 0 100 14.5 7.25 7.25 0 000-14.5z" />
                                        </svg>
                                    </div>
                                </div>

                                <!-- Transmission (Dropdown) -->
                                <div class="mb-4 relative">
                                    <select id="transmission" name="transmission" class="pl-10 pr-4 py-2 w-full border border-gray-300 rounded-lg focus:outline-none focus:border-blue-500">
                                        <option value="" disabled selected hidden>Select Transmission Type</option>
                                        <option value="Manual">Manual</option>
                                        <option value="Automatic">Automatic</option>
                                    </select>
                                    <div class="absolute left-2 top-2 text-gray-400">
                                        <img src="img/transmission1.png" alt="Transmission Icon" class="w-6 h-6">
                                    </div>
                                    <div id="transmission_error" class="absolute right-2 top-2 text-red-500 hidden">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M12 4.75a7.25 7.25 0 100 14.5 7.25 7.25 0 000-14.5z" />
                                        </svg>
                                    </div>
                                </div>

                                <!-- Fuel Type (Dropdown) -->
                                <div class="mb-4 relative">
                                    <select id="fuel_type" name="fuel_type" class="pl-10 pr-4 py-2 w-full border border-gray-300 rounded-lg focus:outline-none focus:border-blue-500">
                                        <option value="" disabled selected hidden>Select Fuel Type</option>
                                        <option value="Gasoline">Gasoline</option>
                                        <option value="Diesel">Diesel</option>
                                        <option value="Electric">Electric</option>
                                    </select>
                                    <div class="absolute left-2 top-2 text-gray-400">
                                        <img src="img/fuel.png" alt="Fuel Icon" class="w-6 h-6">
                                    </div>
                                    <div id="fuel_type_error" class="absolute right-2 top-2 text-red-500 hidden">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M12 4.75a7.25 7.25 0 100 14.5 7.25 7.25 0 000-14.5z" />
                                        </svg>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <div class="flex justify-end mt-6">
                        <button type="submit" class="bg-blue-500 text-white font-semibold py-2 px-8 rounded-lg hover:bg-blue-600">Save</button>
                    </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
    
<?php
// Initialize query
$sql = "SELECT id, car_status, car_brand, car_description, rent_price, body_type, transmission, fuel_type, car_image FROM cars WHERE 1=1";

// Check for search query
if (!empty($_GET['search_query'])) {
    $search = mysqli_real_escape_string($link, $_GET['search_query']);
    $sql .= " AND (car_brand LIKE '%$search%' OR body_type LIKE '%$search%' OR car_description LIKE '%$search%')";
}

// Check for brand filter
if (!empty($_GET['brand']) && is_array($_GET['brand'])) {
    $brands = array_map(function($brand) use ($link) {
        return mysqli_real_escape_string($link, $brand);
    }, $_GET['brand']);
    if (count($brands) > 0) {
        $sql .= " AND car_brand IN ('" . implode("','", $brands) . "')";
    }
}

// Check for body type filter
if (!empty($_GET['body_type']) && is_array($_GET['body_type'])) {
    $body_types = array_map(function($body_type) use ($link) {
        return mysqli_real_escape_string($link, $body_type);
    }, $_GET['body_type']);
    if (count($body_types) > 0) {
        $sql .= " AND body_type IN ('" . implode("','", $body_types) . "')";
    }
}

// Check for transmission filter
if (!empty($_GET['transmission']) && is_array($_GET['transmission'])) {
    $body_types = array_map(function($body_type) use ($link) {
        return mysqli_real_escape_string($link, $body_type);
    }, $_GET['transmission']);
    if (count($body_types) > 0) {
        $sql .= " AND transmission IN ('" . implode("','", $body_types) . "')";
    }
}
// Check for fuel type filter
if (!empty($_GET['fuel_type']) && is_array($_GET['fuel_type'])) {
    $body_types = array_map(function($body_type) use ($link) {
        return mysqli_real_escape_string($link, $body_type);
    }, $_GET['fuel_type']);
    if (count($body_types) > 0) {
        $sql .= " AND fuel_type IN ('" . implode("','", $body_types) . "')";
    }
}

// Query the car data from the database
$result = $link->query($sql);

if ($result && $result->num_rows > 0) {  
    // Start the grid
    echo '<div class="grid grid-cols-3 gap-6 p-6">';
    
    // Loop through the data
    while($row = $result->fetch_assoc()) {
        $carData = json_encode([
            'id' => $row['id'],
            'status' => $row['car_status'],
            'car_image' => $row['car_image'],
            'brand' => $row['car_brand'],
            'description' => $row['car_description'],
            'price' => $row['rent_price'],
            'body_type' => $row['body_type'],
            'transmission' => $row['transmission'],
            'fuel_type' => $row['fuel_type']
        ], JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT);

        echo '
        <div class="bg-white rounded-lg shadow-lg p-4">
            <!-- Status, Delete, and Edit Buttons -->
            <div class="flex items-center justify-between mb-4">
                <span class="bg-' . ($row["car_status"] == "Available" ? 'green-100 text-green-600' : 
                ($row["car_status"] == "Booked" ? 'blue-100 text-blue-600' : 'red-100 text-red-600')) . 
                ' text-xs px-2 py-1 rounded-full shadow-lg">' . 
                htmlspecialchars($row["car_status"]) . '</span>
                
                <div class="flex space-x-2">
                    <!-- Edit Button -->
                    <a href="#" class="text-blue-600 hover:text-blue-800 transition duration-200" 
                       onclick=\'toggleModalEdit(' . $carData . ')\' >
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.121 4.379a3 3 0 00-4.243 0l-8.293 8.293a1 1 0 00-.293.707V20a1 1 0 001 1h5.586a1 1 0 00.707-.293l8.293-8.293a3 3 0 000-4.243l-3.414-3.414zm-1.414 1.414l3.414 3.414-1.414 1.414-3.414-3.414 1.414-1.414zM6 17h6v2H6v-2z" />
                        </svg>
                    </a>
                    <!-- Delete Button -->
                    <a href="#" class="text-red-600 hover:text-red-800 transition duration-200" 
                        onclick="showDeleteModal(' . $row['id'] . '); return false;">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </a>
                </div>
            </div>
          
            <!-- Car Image with fixed size -->
            <img src="' . htmlspecialchars($row["car_image"]) . '" alt="' . htmlspecialchars($row["car_brand"]) . '" class="w-full max-h-48 object-cover rounded-lg">

            <!-- Car Details -->
            <div>
                <h2 class="text-xl font-bold text-gray-900">' . htmlspecialchars($row["car_brand"]) . '</h2>
                <div class="flex items-center">
                    <span class="text-gray-500 text-sm">' . htmlspecialchars($row["car_description"]) . '</span>
                    <div class="ml-auto text-lg text-gray-900"><b>₱' . htmlspecialchars($row["rent_price"]) . '</b>/Day</div>
                </div>
            </div>
            <div class="my-2 border-t border-gray-500"></div>

            <!-- Features -->
            <div class="flex justify-between text-gray-600 text-xs mt-4">
                <div class="flex items-center">
                    <img src="img/vehicle1.png" alt="body_type" class="h-5 w-5 text-gray-500">
                    <span class="ml-1">' . htmlspecialchars($row["body_type"]) . '</span>
                </div>
                <div class="flex items-center">
                    <img src="img/transmission1.png" alt="transmission" class="h-5 w-5 text-gray-500">
                    <span class="ml-1">' . htmlspecialchars($row["transmission"]) . '</span>
                </div>
                <div class="flex items-center">
                    <img src="img/fuel.png" alt="fuel_type" class="h-5 w-5 text-gray-500">
                    <span class="ml-1">' . htmlspecialchars($row["fuel_type"]) . '</span>
                </div>
            </div>
        </div>';
    }

    echo '</div>';
} else {
    echo '<div class="p-6">No cars available</div>';
}

// Delete Confirmation Modal
echo '
<div id="deleteModal" class="fixed inset-0 bg-gray-500 bg-opacity-75 flex items-center justify-center z-50 hidden">
    <div class="bg-white rounded-lg p-4 max-w-sm mx-auto">
        <h2 class="text-lg font-bold mb-4">Confirm Deletion</h2>
        <p>Are you sure you want to delete this car?</p>
        <div class="flex justify-end mt-4 space-x-2"> <!-- Align buttons to the right -->
            <button id="cancelDelete" class="bg-gray-300 text-gray-800 px-4 py-2 rounded hover:bg-gray-400 transition duration-200">Cancel</button>
            <button id="confirmDelete" class="bg-red-600 text-white px-4 py-2 rounded hover:bg-red-700 transition duration-200">Delete</button>
        </div>
    </div>
</div>';

// Close the connection
$link->close();
?>

<!-- Modal Backdrop -->
<div id="modal-backdrop-edit" class="hidden fixed inset-0 bg-gray-900 bg-opacity-50 z-40"></div>

<!-- Update Modal Content -->
<div id="modal_edit" class="hidden fixed inset-0 z-50 flex items-center justify-center">
    <div class="fixed inset-0 flex items-center justify-center bg-black bg-opacity-50">
        <!-- Modal Box -->
        <div class="bg-white rounded-lg shadow-lg p-8 w-full max-w-4xl relative">
            <button id="closeButtonEdit" class="absolute top-4 right-4 text-gray-400 hover:text-gray-600">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
                        <!-- Modal Header -->
                        <h2 class="text-xl font-bold mb-6">EDIT CAR INFO</h2>

                        <div class="flex flex-wrap md:flex-nowrap space-x-0 md:space-x-6">
                            <!-- Form Section -->
                            <div class="container">
                                <form id="carForm_edit" method="post" enctype="multipart/form-data">
                                    <input type="hidden" id="edit_car_id" name="id" value="">
                                    <div class="flex flex-wrap md:flex-nowrap space-x-0 md:space-x-6">
                                        <!-- Image Upload Section -->
                                        <div class="flex flex-col items-center w-full md:w-1/3 mb-6 md:mb-0">
                                            <div id="imagePreview_edit" class="border-2 border-gray-300 bg-gray-100 rounded-lg flex items-center justify-center h-48 w-full mb-4">
                                                <span class="text-gray-500">Add Photo</span>
                                            </div>
                                            <input type="file" name="car_image_edit" id="car_image_edit" class="hidden" accept="image/*">
                                            <button type="button" class="text-blue-500" onclick="document.getElementById('car_image_edit').click();">Upload Image</button>
                                        </div>

                                        <div class="w-full md:w-2/3">
                                            <!-- Car Status (Dropdown) -->
                                            <div class="mb-4 relative">
                                                <select id="car_status_edit" name="car_status_edit" class="pl-10 pr-4 py-2 w-full border border-gray-300 rounded-lg focus:outline-none focus:border-blue-500" required>
                                                    <option value="" disabled selected hidden>Select Car Status</option>
                                                    <option value="Available">Available</option>
                                                    <option value="Unavailable">Unavailable</option>
                                                    <option value="Booked">Booked</option>
                                                </select>
                                                <div class="absolute left-2 top-2 text-gray-400">
                                                    <img src="img/car_status.png" alt="Fuel Icon" class="w-6 h-6">
                                                </div>
                                            </div>

                                            <!-- Car Brand -->
                                            <div class="mb-4 relative">
                                            <input type="text" id="car_brand_edit" name="car_brand_edit" placeholder="Type or Select Car Brand" class="pl-10 pr-4 py-2 w-full border border-gray-300 rounded-lg focus:outline-none focus:border-blue-500" list="car_brands_edit" required>
                                                <datalist id="car_brands_edit">
                                                    <option value="Ford">Ford</option>
                                                    <option value="Honda">Honda</option>
                                                    <option value="Hyundai">Hyundai</option>
                                                    <option value="Kia">Kia</option>
                                                    <option value="Mitsubishi">Mitsubishi</option>
                                                    <option value="Nissan">Nissan</option>
                                                    <option value="Suzuki">Suzuki</option>
                                                    <option value="Toyota">Toyota</option>
                                                </datalist>
                                                <div class="absolute left-2 top-2 text-gray-400">
                                                    <img src="img/car.png" alt="Fuel Icon" class="w-6 h-6">
                                                </div>
                                            </div>

                                            <!-- Car Description -->
                                            <div class="mb-4 relative">
                                                <input type="text" id="car_description_edit" name="car_description_edit" placeholder="Car Description" class="pl-10 pr-4 py-2 w-full border border-gray-300 rounded-lg focus:outline-none focus:border-blue-500" required>
                                                <div class="absolute left-2 top-2 text-gray-400">
                                                    <img src="img/car_desc.png" alt="Fuel Icon" class="w-6 h-6">
                                                </div>
                                            </div>

                                            <!-- Rent Price -->
                                            <div class="mb-4 relative">
                                                <input type="number" id="rent_price_edit" name="rent_price_edit" placeholder="Rent Price" class="pl-10 pr-4 py-2 w-full border border-gray-300 rounded-lg focus:outline-none focus:border-blue-500" required>
                                                <div class="absolute left-2 top-2 text-gray-400">
                                                    <img src="img/car_price.png" alt="Fuel Icon" class="w-6 h-6">
                                                </div>
                                            </div>

                                            <!-- Body Type -->
                                            <div class="mb-4 relative">
                                            <select id="body_type_edit" name="body_type_edit" class="pl-10 pr-4 py-2 w-full border border-gray-300 rounded-lg focus:outline-none focus:border-blue-500" required>
                                                <option value="" disabled selected hidden>Select Body Type</option>
                                                <option value="Coupe">Coupe</option>
                                                <option value="Crossover">Crossover</option>
                                                <option value="Hatchback">Hatchback</option>
                                                <option value="Pickup">Pickup</option>
                                                <option value="Sedan">Sedan</option>
                                                <option value="SUV">SUV</option>
                                                <option value="Van">Van</option>
                                                <option value="Wagon">Wagon</option>
                                            </select>
                                                <div class="absolute left-2 top-2 text-gray-400">
                                                    <img src="img/vehicle1.png" alt="Fuel Icon" class="w-6 h-6">
                                                </div>
                                            </div>

                                            <!-- Transmission (Dropdown) -->
                                            <div class="mb-4 relative">
                                                <select id="transmission_edit" name="transmission_edit" class="pl-10 pr-4 py-2 w-full border border-gray-300 rounded-lg focus:outline-none focus:border-blue-500" required>
                                                    <option value="" disabled selected hidden>Select Transmission</option>
                                                    <option value="Manual">Manual</option>
                                                    <option value="Automatic">Automatic</option>
                                                </select>
                                                <div class="absolute left-2 top-2 text-gray-400">
                                                    <img src="img/transmission1.png" alt="Fuel Icon" class="w-6 h-6">
                                                </div>
                                            </div>

                                            <!-- Fuel Type (Dropdown) -->
                                            <div class="mb-4 relative">
                                                <select id="fuel_type_edit" name="fuel_type_edit" class="pl-10 pr-4 py-2 w-full border border-gray-300 rounded-lg focus:outline-none focus:border-blue-500" required>
                                                    <option value="" disabled selected hidden>Select Fuel Type</option>
                                                    <option value="Gasoline">Gasoline</option>
                                                    <option value="Diesel">Diesel</option>
                                                    <option value="Electric">Electric</option>
                                                </select>
                                                <div class="absolute left-2 top-2 text-gray-400">
                                                    <img src="img/fuel.png" alt="Fuel Icon" class="w-6 h-6">
                                                </div>
                                            </div>

                                            <div class="flex justify-end mt-6">
                                                <button type="submit" class="bg-blue-500 text-white font-semibold py-2 px-8 rounded-lg hover:bg-blue-600">Update</button>
                                            </div>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
<script>
    // Function to submit the search form
    function submitSearch(event) {
        event.preventDefault();
        document.getElementById('searchForm').submit();
    }

    // Add event listener for the search input to listen for keydown events
    document.getElementById('searchInput').addEventListener('keydown', function(event) {
        if (event.key === 'Enter') {
            submitSearch(event);
        }
    });

    // Add keydown event listener for the entire filter panel
    document.getElementById('filterPanel').addEventListener('keydown', function(event) {
        if (event.target.tagName === 'INPUT' && event.target.type === 'checkbox' && event.key === 'Enter') {
            submitSearch(event);
        }
    });

    // Function to reset filters and refresh the dashboard
    function resetFilters() {
        // Clear the search input
        document.getElementById('searchInput').value = '';

        // Uncheck all checkboxes
        const checkboxes = document.querySelectorAll('input[type="checkbox"]');
        checkboxes.forEach(checkbox => {
            checkbox.checked = false;
        });

        // Refresh the admin_dashboard.php
        window.location.href = 'admin_dashboard.php';
    }
    document.getElementById('carForm').addEventListener('submit', function(event) {
    resetErrorStates();

    // Get form values
    const carImage = document.getElementById('car_image').files[0];
    const carBrand = document.getElementById('car_brand').value.trim();
    const rentPrice = document.getElementById('rent_price').value.trim();
    const bodyType = document.getElementById('body_type').value.trim();
    const transmission = document.getElementById('transmission').value.trim();
    const fuelType = document.getElementById('fuel_type').value.trim();
    const carStatus = document.getElementById('car_status').value.trim();
    const carDescription = document.getElementById('car_description').value.trim();

    // Initialize an array to hold error messages
    let errorMessages = [];

    // Validate image upload
    if (!carImage) {
        errorMessages.push('Please upload an image.');
        setErrorState('image_error_icon');
    }

    // Validate text inputs
    if (!carBrand) {
        errorMessages.push('Car brand cannot be blank.');
        setErrorState('car_brand_error');
        setInputErrorState('car_brand');
    }
    if (!rentPrice) {
        errorMessages.push('Rent price cannot be blank.');
        setErrorState('rent_price_error');
        setInputErrorState('rent_price');
    }
    if (!bodyType) {
        errorMessages.push('Body type cannot be blank.');
        setErrorState('body_type_error');
        setInputErrorState('body_type');
    }
    if (!transmission) {
        errorMessages.push('Transmission type cannot be blank.');
        setErrorState('transmission_error');
        setInputErrorState('transmission');
    }
    if (!fuelType) {
        errorMessages.push('Fuel type cannot be blank.');
        setErrorState('fuel_type_error');
        setInputErrorState('fuel_type');
    }
    if (!carStatus) {
        errorMessages.push('Car status cannot be blank.');
        setErrorState('car_status_error');
        setInputErrorState('car_status');
    }
    if (!carDescription) {
        errorMessages.push('Car description cannot be blank.');
        setErrorState('car_description_error');
        setInputErrorState('car_description');
    }

    // If there are errors, prevent form submission
    if (errorMessages.length > 0) {
        event.preventDefault();
        }
    });

    // Function to reset error states
    function resetErrorStates() {
        const errorIcons = document.querySelectorAll('.text-red-500');
        const inputs = document.querySelectorAll('input, select');

        // Reset error icons and input styles
        errorIcons.forEach(icon => {
            icon.classList.add('hidden');
        });
        inputs.forEach(input => {
            input.classList.remove('border-red-500');
        });
    }

    // Function to set error state
    function setErrorState(errorIconId) {
        const errorIcon = document.getElementById(errorIconId);
        if (errorIcon) {
            errorIcon.classList.remove('hidden');
        }
    }

    // Function to set red border on input with error
    function setInputErrorState(inputId) {
        const inputElement = document.getElementById(inputId);
        if (inputElement) {
            inputElement.classList.add('border-red-500');
        }
    }

    // Event listener for file input change
    document.getElementById('car_image').addEventListener('change', function (event) {
        const file = event.target.files[0];
        const reader = new FileReader();

        reader.onload = function (e) {
            const imagePreview = document.getElementById('imagePreview');
            imagePreview.innerHTML = '<img src="' + e.target.result + '" alt="Image Preview" class="object-cover h-full w-full rounded-lg">';
        };

        // Check if a file is selected
        if (file) {
            reader.readAsDataURL(file);
        } else {
            // If no file is selected, reset the preview area
            document.getElementById('imagePreview').innerHTML = '<span class="text-gray-500">Add Photo</span>';
        }
    });

    let deleteId = null;

    function showDeleteModal(id) {
        deleteId = id;
        document.getElementById('deleteModal').classList.remove('hidden');
    }

    document.getElementById('confirmDelete').addEventListener('click', function() {
        if (deleteId) {
            window.location.href = 'delete_car.php?id=' + deleteId;
        }
    });

    document.getElementById('cancelDelete').addEventListener('click', function() {
        document.getElementById('deleteModal').classList.add('hidden');
    });
    // Toggle sidebar visibility
    function toggleSidebar() {
        document.getElementById('sidebar').classList.toggle('-translate-x-full');
        document.getElementById('overlay').classList.toggle('hidden');
    }

    // Toggle filter visibility
    function toggleFilters() {
        const filterSection = document.getElementById('filter-section');
        filterSection.classList.toggle('hidden');
    }

    // Show car information form
    function showCarForm() {
        document.getElementById('car-form-modal').classList.remove('hidden');
    }

    // Hide car information form
    function hideCarForm() {
        document.getElementById('car-form-modal').classList.add('hidden');
    }

    // Show chat interface
    function showChatInterface() {
        document.getElementById('chat-interface-modal').classList.remove('hidden');
    }

    // Hide chat interface
    function hideChatInterface() {
        document.getElementById('chat-interface-modal').classList.add('hidden');
    }

    function handleClick() {
        alert('');
    }

    function toggleRentalTypeButtons() {
        const rentalTypeButtons = document.getElementById('rental-type-buttons');
        rentalTypeButtons.classList.toggle('hidden');
    }

    function toggleCarBrandButtons() {
        const carBrandButtons = document.getElementById('car-brand-buttons');
        carBrandButtons.classList.toggle('hidden');
    }

    function toggleTransmissionButtons() {
        const TransmissionButtons = document.getElementById('transmission-buttons');
        TransmissionButtons.classList.toggle('hidden');
    }
    function toggleFuelTypeButtons() {
        const FuelTypeButtons = document.getElementById('fuel-type-buttons');
        FuelTypeButtons.classList.toggle('hidden');
    }

    // Show and hide modal
    function toggleModal() {
        document.getElementById('modal').classList.toggle('hidden');
        document.getElementById('modal-backdrop').classList.toggle('hidden');
    }
        document.addEventListener('DOMContentLoaded', function () {
            const closeButton = document.getElementById('closeButton');
            const modal = document.getElementById('modal');

    // Close the modal when the "X" button is clicked
    closeButton.addEventListener('click', function () {
            modal.classList.add('hidden');
            document.getElementById('modal-backdrop').classList.add('hidden');
        });
    });
    // Toggle modal visibility
    function toggleModalEdit(car) {
        document.getElementById('modal_edit').classList.toggle('hidden');
        document.getElementById('modal-backdrop-edit').classList.toggle('hidden');

        // Populate form fields with car data
        document.getElementById('carForm_edit').id.value = car.id;
        document.getElementById('car_brand_edit').value = car.brand;
        document.getElementById('car_description_edit').value = car.description;
        document.getElementById('rent_price_edit').value = car.price;
        document.getElementById('body_type_edit').value = car.body_type;
        document.getElementById('transmission_edit').value = car.transmission;
        document.getElementById('fuel_type_edit').value = car.fuel_type;
    }
    // Wait until the DOM content is fully loaded
    document.addEventListener('DOMContentLoaded', function() {
        const closeButton = document.getElementById('closeButtonEdit');
        const modal = document.getElementById('modal_edit');

    // Close the modal when the "X" button is clicked
    closeButton.addEventListener('click', function() {
        modal.classList.add('hidden');
        document.getElementById('modal-backdrop-edit').classList.add('hidden');
        });
    });
    
    function openModal(message) {
        document.getElementById('modalMessage').textContent = message;
        document.getElementById('successModal').classList.remove('hidden');
    }

    function closeModal() {
        document.getElementById('successModal').classList.add('hidden');
    }

    // Check for success and error messages from PHP session variables
    window.onload = function() {
        const successMessage = "<?php echo isset($_SESSION['success_message']) ? $_SESSION['success_message'] : ''; ?>";
        if (successMessage) {
            openModal(successMessage, 'success');
            <?php unset($_SESSION['success_message']); ?>
        }

        // Check for error message
        const errorMessage = "<?php echo isset($_SESSION['error_message']) ? $_SESSION['error_message'] : ''; ?>";
        if (errorMessage) {
            openModal(errorMessage, 'error');
            <?php unset($_SESSION['error_message']); ?>
        }
    };

    function toggleFilterPanel() {
        const filterPanel = document.getElementById('filterPanel');
        filterPanel.classList.toggle('hidden');
    }

    function toggleButton(button) {
        // Toggle button class for active state (blue when selected)
        if (button.classList.contains('bg-blue-500')) {
            button.classList.remove('bg-blue-500', 'text-white');
            button.classList.add('bg-gray-200', 'text-black');
        } else {
            // Reset all sibling buttons to inactive
            const siblings = button.parentNode.querySelectorAll('button');
            siblings.forEach(sibling => {
                sibling.classList.remove('bg-blue-500', 'text-white');
                sibling.classList.add('bg-gray-200', 'text-black');
            });
            // Set clicked button to active
            button.classList.remove('bg-gray-200', 'text-black');
            button.classList.add('bg-blue-500', 'text-white');
        }
    }
    // JavaScript function to populate the modal fields with car data
    function toggleModalEdit(carData) {
    // Show the modal
    document.getElementById('modal_edit').classList.remove('hidden');
    document.getElementById('modal-backdrop-edit').classList.remove('hidden');

    // Populate form fields with car data
    document.getElementById('edit_car_id').value = carData.id;
    document.getElementById('car_status_edit').value = carData.status;
    document.getElementById('car_brand_edit').value = carData.brand;
    document.getElementById('car_description_edit').value = carData.description;
    document.getElementById('rent_price_edit').value = carData.price;
    document.getElementById('body_type_edit').value = carData.body_type;
    document.getElementById('transmission_edit').value = carData.transmission;
    document.getElementById('fuel_type_edit').value = carData.fuel_type;

    // Set the car image preview
    document.getElementById('imagePreview_edit').innerHTML = '<img src="' + carData.car_image + '" alt="Car Image" class="h-48 object-cover w-full rounded-lg">';
    }

    // Close button functionality
    document.getElementById('closeButtonEdit').addEventListener('click', function() {
        document.getElementById('modal_edit').classList.add('hidden');
        document.getElementById('modal-backdrop-edit').classList.add('hidden');
    });

    // Event listener for file input change
        document.getElementById('car_image_edit').addEventListener('change', function (event) {
            const file = event.target.files[0];
            const reader = new FileReader();

            reader.onload = function (e) {
                const imagePreview = document.getElementById('imagePreview_edit');
                imagePreview.innerHTML = '<img src="' + e.target.result + '" alt="Image Preview" class="object-cover h-full w-full rounded-lg">';
            };

            // Check if a file is selected
            if (file) {
                reader.readAsDataURL(file);
            } else {
                // If no file is selected, reset the preview area
                document.getElementById('imagePreview_edit').innerHTML = '<span class="text-gray-500">Add Photo</span>';
            }
        });
</script>
</main>
</div>
</div>

</body>
</html>
