<?php
// Initialize the session
session_start();

include('config.php');

// Check if the user is logged in, if not then redirect him to login page
if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true){
  header("location: login.php");
  exit;
}

// Fetch user information once at the start
$resultf = mysqli_query($link, "SELECT * FROM users WHERE id='" . $_SESSION['id'] . "'");
$rowf = mysqli_fetch_array($resultf);

$userId = $_SESSION['id'];
$sqlUser = "SELECT license_front_image, license_back_image FROM users WHERE id = ?";
$stmtUser = $link->prepare($sqlUser);
$stmtUser->bind_param("i", $userId);
$stmtUser->execute();
$resultUser = $stmtUser->get_result();
$userData = $resultUser->fetch_assoc();

$licenseFrontImage = $userData['license_front_image'] ?? null;
$licenseBackImage = $userData['license_back_image'] ?? null;

// Check if the request is a POST request
if ($_SERVER["REQUEST_METHOD"] === "POST") {
  // Get the posted data
  $carId = $_POST['carId'];
  $driverName = $_POST['driverName'];
  $pickupDate = $_POST['pickupDate'];
  $dropoffDate = $_POST['dropoffDate'];
  $licenseNo = $_POST['licenseNo'];
  $validID = $_POST['validID'];
  $destination = $_POST['destination'];
  $purpose = $_POST['purpose'];

  // Get the current user's ID from the session
  $userId = $_SESSION['id'];

  // Prepare and bind the SQL statement
  $stmt = $link->prepare("INSERT INTO bookings (car_id, user_id, driver_name, pickup_date, dropoff_date, license_no, valid_id, destination, purpose, booking_status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 'Pending')");
  $stmt->bind_param("issssssss", $carId, $userId, $driverName, $pickupDate, $dropoffDate, $licenseNo, $validID, $destination, $purpose);

  // Execute the statement
  if ($stmt->execute()) {
      echo json_encode(['success' => true]);
  } else {
      echo json_encode(['success' => false, 'message' => 'Database error: ' . $link->error]);
  }

  $stmt->close();

  exit;
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
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
  <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">



  <style>
      .nav-icon {
            transition: all 0.3s ease;
        }
        .nav-icon:hover {
            color: #00B3FF;
            transform: scale(1.1);
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
            <a href="customer_dashboard.php" class="flex justify-center items-center space-x-2"><img src="img/home (1).png" alt="Home Icon" class="w-7 h-7"></a>
            <a href="customer_calendar.php" class="flex justify-center items-center space-x-2"><img src="img/calendar.png" alt="Mail Icon" class="w-6 h-6"></a>
            <a href="customer_favourites.php" class="flex justify-center items-center space-x-2"><img src="img/heart.png" alt="Settings Icon" class="w-6 h-6"></a>
            <a href="customer_recents.php" class="flex justify-center items-center space-x-2 text-blue-500"><img src="img/watch.png" alt="Chat Icon" class="w-7 h-7"></a>
            <a href="customer_profile.php" class="flex justify-center items-center space-x-2 text-blue-500"><img src="img/profile.png" alt="Chat Icon" class="w-7 h-7"></a>
            <a href="customer_chat.php" class="flex justify-center items-center space-x-2 text-blue-500"><img src="img/bubble-chat.png" alt="Chat Icon" class="w-6 h-6"></a>
            <div class="px-6 py-2">
              <a href="logout.php" class="hover:text-blue-500 fixed mt-28 flex justify-center items-center space-x-2"><img src="img/logout.png" alt="Logout Icon" class="w-6 h-6"></a>
            </div>
          </nav>
        </div>
      </nav>
<!-- Overlay for mobile sidebar -->
<div id="overlay" class="fixed inset-0 bg-black bg-opacity-50 z-20 lg:hidden hidden" onclick="toggleSidebar()"></div>

<!-- Main content -->
<main class="flex-grow p-6 ml-[130px]">
    <div class="flex justify-between items-center mx-6">
        <h2 class="text-2xl font-semibold">Cars Available:</h2>

        <!-- Profile Section -->
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



 

    <?php
// Prepare your SQL query
$sql = "
    SELECT c.*, b.booking_status 
    FROM cars c
    LEFT JOIN (
        SELECT car_id, booking_status 
        FROM bookings 
        WHERE booking_status IS NOT NULL AND user_id = ?
        ORDER BY booking_status DESC
    ) b ON c.id = b.car_id
    WHERE 1=1"; // Base query

// Prepare an array to hold the parameters
$params = [$userId];
$paramTypes = 'i';

// Check for search query
if (!empty($_GET['search_query'])) {
    $search = mysqli_real_escape_string($link, $_GET['search_query']);
    $sql .= " AND (c.car_brand LIKE ? OR c.body_type LIKE ? OR c.car_description LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
    $params[] = "%$search%";
    $paramTypes .= 'sss';
}

// Check for brand filter
if (!empty($_GET['brand']) && is_array($_GET['brand'])) {
    $brands = array_map(function($brand) use ($link) {
        return mysqli_real_escape_string($link, $brand);
    }, $_GET['brand']);
    if (count($brands) > 0) {
        $sql .= " AND c.car_brand IN (" . str_repeat('?,', count($brands) - 1) . "?)";
        $params = array_merge($params, $brands);
        $paramTypes .= str_repeat('s', count($brands));
    }
}

// Check for body type filter
if (!empty($_GET['body_type']) && is_array($_GET['body_type'])) {
    $body_types = array_map(function($body_type) use ($link) {
        return mysqli_real_escape_string($link, $body_type);
    }, $_GET['body_type']);
    if (count($body_types) > 0) {
        $sql .= " AND c.body_type IN (" . str_repeat('?,', count($body_types) - 1) . "?)";
        $params = array_merge($params, $body_types);
        $paramTypes .= str_repeat('s', count($body_types));
    }
}

// Check for transmission filter
if (!empty($_GET['transmission']) && is_array($_GET['transmission'])) {
    $transmissions = array_map(function($transmission) use ($link) {
        return mysqli_real_escape_string($link, $transmission);
    }, $_GET['transmission']);
    if (count($transmissions) > 0) {
        $sql .= " AND c.transmission IN (" . str_repeat('?,', count($transmissions) - 1) . "?)";
        $params = array_merge($params, $transmissions);
        $paramTypes .= str_repeat('s', count($transmissions));
    }
}

// Check for fuel type filter
if (!empty($_GET['fuel_type']) && is_array($_GET['fuel_type'])) {
    $fuel_types = array_map(function($fuel_type) use ($link) {
        return mysqli_real_escape_string($link, $fuel_type);
    }, $_GET['fuel_type']);
    if (count($fuel_types) > 0) {
        $sql .= " AND c.fuel_type IN (" . str_repeat('?,', count($fuel_types) - 1) . "?)";
        $params = array_merge($params, $fuel_types);
        $paramTypes .= str_repeat('s', count($fuel_types));
    }
}

// Prepare the SQL statement
$stmt = $link->prepare($sql);
$stmt->bind_param($paramTypes, ...$params);

// Execute the statement
$stmt->execute();

// Get the result
$result = $stmt->get_result();

if ($result && $result->num_rows > 0) {
    echo '<div class="grid grid-cols-3 gap-6 p-6">';

    while ($row = $result->fetch_assoc()) {
        $bookingStatus = $row['booking_status'];
        $isBooked = !is_null($bookingStatus);
        
        // Determine if the car is booked
        $carStatus = $row["car_status"];
        $isCarBooked = ($carStatus == "Booked");

        // Output only the car if the booking_status is NULL or valid
        if ($isBooked || is_null($bookingStatus)) {
            $carData = json_encode([
                'id' => $row['id'],
                'status' => $carStatus,
                'car_image' => $row['car_image'],
                'brand' => $row['car_brand'],
                'description' => $row['car_description'],
                'price' => $row['rent_price'],
                'body_type' => $row['body_type'],
                'transmission' => $row['transmission'],
                'fuel_type' => $row['fuel_type']
            ], JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT);

            // Determine booking status class and text
            $bookingStatusClass = '';
            $bookingStatusText = 'No Booking';

            if ($isBooked) {
                switch ($bookingStatus) {
                    case 'Approved':
                        $bookingStatusClass = 'bg-green-100 text-green-600';
                        $bookingStatusText = htmlspecialchars($bookingStatus);
                        break;
                    case 'Pending':
                        $bookingStatusClass = 'bg-blue-100 text-blue-600';
                        $bookingStatusText = htmlspecialchars($bookingStatus);
                        break;
                    case 'Declined':
                        $bookingStatusClass = 'bg-red-100 text-red-600';
                        $bookingStatusText = htmlspecialchars($bookingStatus);
                        break;
                    default:
                        $bookingStatusClass = 'bg-gray-200 text-gray-600';
                        break;
                }
            } else {
                $bookingStatusClass = 'bg-gray-200 text-gray-600';
            }

            // Output the car details
            echo '
            <div class="car-card bg-white rounded-lg shadow-lg p-4 cursor-pointer ' . ($isCarBooked ? 'pointer-events-none' : '') . '" data-car=\'' . $carData . '\' ' . ($isCarBooked ? 'onclick="return false;"' : '') . '>
                <div class="flex items-center justify-between mb-4">
                    <span class="bg-' . ($carStatus == "Available" ? 'green-100 text-green-600' : 
                    ($carStatus == "Booked" ? 'blue-100 text-blue-600' : 'red-100 text-red-600')) . 
                    ' text-xs px-2 py-1 rounded-full shadow-lg">' . 
                    htmlspecialchars($carStatus) . '</span>
                    <span class="' . $bookingStatusClass . ' text-xs px-2 py-1 rounded-full shadow-lg ml-2">' . 
                    htmlspecialchars($bookingStatusText) . '</span>
                    <!-- Separate heart button container -->
                    <div class="ml-auto favorite-container">
                        <button class="flex items-center favorite-button" onclick="toggleFavorite(' . $row['id'] . ', this); event.stopPropagation();">
                            <img src="img/heart.png" alt="favourite" class="w-6 h-6 heart-icon" data-favorited="false">
                        </button>
                    </div>
                </div>
                <img src="' . htmlspecialchars($row["car_image"]) . '" alt="' . htmlspecialchars($row["car_brand"]) . '" class="w-full max-h-48 object-cover rounded-lg">
                <div>
                    <h2 class="text-xl font-bold text-gray-900">' . htmlspecialchars($row["car_brand"]) . '</h2>
                    <div class="flex items-center">
                        <span class="text-gray-500 text-sm">' . htmlspecialchars($row["car_description"]) . '</span>
                        <div class="ml-auto text-lg text-gray-900"><b>₱' . htmlspecialchars($row["rent_price"]) . '</b>/Day</div>
                    </div>
                </div>
                <div class="my-2 border-t border-gray-500"></div>
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
    }
    
    echo '</div>';
} else {
    echo 'No cars found';
}

$stmt->close();
?>







<div id="carModal" class="modal fixed z-10 inset-0 hidden bg-black bg-opacity-50 flex items-center justify-center">
    <div class="modal-content bg-white rounded-lg p-6 max-w-2xl w-full mx-auto relative h-auto my-4">
        <button class="close absolute top-1 right-2 text-gray-500" onclick="hideCarForm()">
            <i class="fas fa-times"></i>
        </button>
        <div class="w-full space-y-6">
            <img id="modalCarImage" src="" alt="" class="w-full h-60 object-cover rounded-lg">
            
            <form id="bookingForm" method="post" enctype="multipart/form-data" class="grid grid-cols-1 gap-6">
                <!-- Hidden input for car ID -->
                <input type="hidden" id="carId" name="carId" value="">

                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div class="flex flex-col">
                        <label for="driverName" class="text-sm font-medium">Driver's Name</label>
                        <input type="text" id="driverName" name="driverName" placeholder="Enter driver's name" class="block w-full p-2 border rounded-md border-gray-300 focus:ring-blue-500 focus:border-blue-500" required>
                    </div>

                    <div class="flex flex-col">
                        <label for="pickupDate" class="text-sm font-medium">Pick-up Date & Time</label>
                        <input type="datetime-local" id="pickupDate" name="pickupDate" class="block w-full p-2 border rounded-md border-gray-300 focus:ring-blue-500 focus:border-blue-500" required min="<?php echo date('Y-m-d\TH:i'); ?>">
                    </div>

                    <div class="flex flex-col">
                        <label for="dropoffDate" class="text-sm font-medium">Drop-off Date & Time</label>
                        <input type="datetime-local" id="dropoffDate" name="dropoffDate" class="block w-full p-2 border rounded-md border-gray-300 focus:ring-blue-500 focus:border-blue-500" required min="<?php echo date('Y-m-d\TH:i'); ?>">
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="flex flex-col">
                        <label for="licenseNo" class="text-sm font-medium">Driver License No.</label>
                        <input type="text" id="licenseNo" name="licenseNo" placeholder="Enter license number" class="block w-full p-2 border rounded-md border-gray-300 focus:ring-blue-500 focus:border-blue-500" required>
                    </div>

                    <div class="flex flex-col">
                        <label for="validID" class="text-sm font-medium">Other Valid ID</label>
                        <input type="text" id="validID" name="validID" placeholder="Enter ID number" class="block w-full p-2 border rounded-md border-gray-300 focus:ring-blue-500 focus:border-blue-500">
                    </div>
                </div>

                <div class="grid gap-6">
                    <div class="flex flex-col">
                        <label for="destination" class="text-sm font-medium">Destination</label>
                        <input type="text" id="destination" name="destination" placeholder="Enter destination" class="block w-full p-2 border rounded-md border-gray-300 focus:ring-blue-500 focus:border-blue-500" required>
                    </div>

                    <div class="flex flex-col">
                        <label for="purpose" class="text-sm font-medium">Purpose</label>
                        <input type="text" id="purpose" name="purpose" placeholder="Enter purpose" class="block w-full p-2 border rounded-md border-gray-300 focus:ring-blue-500 focus:border-blue-500" required>
                    </div>

                    <button type="submit" class="col-span-2 w-full bg-blue-500 hover:bg-blue-600 text-white font-bold py-2 px-4 rounded-md">Book Now</button>
                </div>
            </form>
        </div>
    </div>
</div>
    
  <!-- Filter Button -->
  <div class="filter-btn fixed" onclick="toggleFilterPanel()">
    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" class="h-4 w-4">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2a1 1 0 01-.293.707l-7.414 7.414A1 1 0 0112 14v5a1 1 0 01-.553.894l-2 1A1 1 0 018 20v-6.586a1 1 0 01.293-.707l7.414-7.414A1 1 0 0116 6V4H4v2a1 1 0 01-.293.707L1.293 4.293A1 1 0 013 4z"/>
    </svg>
  </div>

  <!-- Filter Panel -->
<div id="filterPanel" class="hidden fixed top-0 h-full w-70 bg-white shadow-lg p-4 overflow-y-auto" style="left: 8.5%;">
    <!-- Close Button -->
    <div class="flex justify-end">
        <button class="close-btn" onclick="toggleFilterPanel()" style="background:none; border:none;">&times;</button>
    </div>

    <div class="w-full flex justify-between pb-2 pt-2">
        <h2 class="text-lg font-semibold">Filter by:</h2>
        <button class="text-sm text-gray-500 underline" onclick="resetFilters()" style="background:none; border:none;">Reset</button>
    </div>

    <!-- Search -->
    <div class="mb-4">
        <form id="searchForm" action="customer_dashboard.php" method="GET">
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
<script>
function toggleFavorite(carId, button) {
    const heartIcon = button.querySelector('.heart-icon');
    const isFavorited = heartIcon.getAttribute('data-favorited') === 'true';

    if (isFavorited) {
        heartIcon.src = 'img/heart.png';
        heartIcon.setAttribute('data-favorited', 'false');
        console.log('Removed from favorites:', carId);
    } else {
        heartIcon.src = 'img/heart (1).png';
        heartIcon.setAttribute('data-favorited', 'true');
        console.log('Added to favorites:', carId);
    }
}
const licenseFrontImage = <?php echo json_encode($licenseFrontImage ? true : false); ?>;
const licenseBackImage = <?php echo json_encode($licenseBackImage ? true : false); ?>;

console.log("License Front Image Exists:", licenseFrontImage);
console.log("License Back Image Exists:", licenseBackImage);

document.getElementById("bookingForm").addEventListener("submit", function(event) {
    // Prevent the default form submission
    event.preventDefault();

    // Check if both license images are present
    if (!licenseFrontImage || !licenseBackImage) {
        alert('Please upload your license images before booking.');
        location.reload();
        hideCarForm();
        this.reset();
        
        return;
    }

    // Get form data
    const formData = new FormData(this);

    // Send data to the server using Fetch API
    fetch('', {
        method: 'POST',
        body: formData
    })
    .then(response => {
        if (!response.ok) {
            throw new Error('Network response was not ok: ' + response.statusText);
        }
        return response.json();
    })
    .then(data => {
        if (data.success) {
            alert("Booking request submitted successfully!");
            location.reload();
            hideCarForm();
            this.reset();
           
        } else {
            alert("Failed to submit booking request: " + data.message);
        }
    });
});



function hideCarForm() {
    document.getElementById('carModal').classList.add('hidden');
}

      document.addEventListener('DOMContentLoaded', function () {
        var modal = document.getElementById("carModal");
        var closeBtn = document.querySelector(".close");

        const carCards = document.querySelectorAll(".car-card");
        carCards.forEach(card => {
            card.onclick = function () {
                const carData = JSON.parse(card.getAttribute('data-car'));
                openCarModal(carData);
            };
        });

        function openCarModal(carData) {
            document.getElementById('modalCarImage').src = carData.car_image;
            document.getElementById('carId').value = carData.id;


            modal.classList.remove('hidden');
        }

        closeBtn.onclick = function () {
            modal.classList.add('hidden');
        };

        window.onclick = function (event) {
            if (event.target == modal) {
                modal.classList.add('hidden');
            }
        };
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
        document.getElementById('searchInput').value = '';

        // Uncheck all checkboxes
        const checkboxes = document.querySelectorAll('input[type="checkbox"]');
        checkboxes.forEach(checkbox => {
            checkbox.checked = false;
        });

        // Refresh the admin_dashboard.php
        window.location.href = 'customer_dashboard.php';
    }
</script>

</body>
</html>