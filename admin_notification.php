<?php
session_start();

// Check if the user is logged in and is an admin
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true || $_SESSION["role"] !== 'admin') {
  header("location: login.php");
  exit;
}

require_once 'config.php';

//check if the car is booked and update car status
function updateCarStatus($link, $car_id) {
  $current_date = date('Y-m-d');

  // Check if the car is booked on or before the current date and the drop-off date is on or after the current date
  $query = "SELECT COUNT(*) as count 
            FROM bookings 
            WHERE car_id = ? 
            AND booking_status = 'Approved' 
            AND DATE(pickup_date) <= ? 
            AND DATE(dropoff_date) >= ?";
  $stmt = $link->prepare($query);
  $stmt->bind_param("iss", $car_id, $current_date, $current_date);
  $stmt->execute();
  $result = $stmt->get_result();
  $row = $result->fetch_assoc();
  $is_booked = $row['count'] > 0;

  // Update car status
  $new_status = $is_booked ? 'Booked' : 'Available';
  $update_query = "UPDATE cars SET car_status = ? WHERE id = ?";
  $update_stmt = $link->prepare($update_query);
  $update_stmt->bind_param("si", $new_status, $car_id);
  if (!$update_stmt->execute()) {
      echo 'Update failed: ' . $link->error;
  }
  $update_stmt->close();

  // Return the updated status
  return $new_status;
}

// Check if the approve button is clicked
if (isset($_POST['approve_booking'])) {
    $booking_id = $_POST['booking_id'];

    // Update the booking status to 'Approved'
    $update_booking_query = "UPDATE bookings SET booking_status = 'Approved' WHERE id = ?";
    $stmt = $link->prepare($update_booking_query);
    $stmt->bind_param("i", $booking_id);

    if ($stmt->execute()) {
        $_SESSION['success_message'] = "Booking approved!";

        // Fetch the car ID from the booking
        $car_query = "SELECT car_id FROM bookings WHERE id = ?";
        $car_stmt = $link->prepare($car_query);
        $car_stmt->bind_param("i", $booking_id);
        $car_stmt->execute();
        $car_stmt->bind_result($car_id);
        $car_stmt->fetch();
        $car_stmt->close();

        // Update the car status
        $updated_status = updateCarStatus($link, $car_id);
    } else {
        $_SESSION['error_message'] = "Error approving booking.";
    }
    $stmt->close();
    header("Location: admin_notification.php");
    exit();
}

// Check if the decline button is clicked
if (isset($_POST['decline_booking'])) {
    $booking_id = $_POST['booking_id'];

    // Update the booking status to 'Declined'
    $update_booking_query = "UPDATE bookings SET booking_status = 'Declined' WHERE id = ?";
    $stmt = $link->prepare($update_booking_query);
    $stmt->bind_param("i", $booking_id);

    if ($stmt->execute()) {
        $_SESSION['success_message'] = "Booking declined!";
    } else {
        $_SESSION['error_message'] = "Error declining booking.";
    }
    $stmt->close();
    header("Location: admin_notification.php");
    exit();
}

// Fetch all booking requests from the database
$booking_requests = [];
$query = "SELECT * FROM bookings WHERE booking_status IN ('Pending', 'Approved', 'Declined')";
$result = $link->query($query);

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $booking_requests[] = $row;
        $booking_status = $row['booking_status'];
    }
}

// Check if the delete button was clicked
if (isset($_POST['delete_booking'])) {
  $delete_booking_id = $_POST['delete_booking_id'];

  // First, find the car_id associated with this booking
  $car_query = "SELECT car_id FROM bookings WHERE id = ?";
  $car_stmt = $link->prepare($car_query);
  $car_stmt->bind_param("i", $delete_booking_id);
  $car_stmt->execute();
  $car_result = $car_stmt->get_result();
  
  if ($car_result->num_rows > 0) {
      $car_row = $car_result->fetch_assoc();
      $car_id = $car_row['car_id'];

      // Set the booking status to NULL for the selected booking
      $update_booking_query = "UPDATE bookings SET booking_status = NULL WHERE id = ?";
      $update_booking_stmt = $link->prepare($update_booking_query);
      $update_booking_stmt->bind_param("i", $delete_booking_id);

      if ($update_booking_stmt->execute()) {
          $update_car_query = "UPDATE cars SET car_status = 'Available' WHERE id = ?";
          $update_car_stmt = $link->prepare($update_car_query);
          $update_car_stmt->bind_param("i", $car_id);

          if ($update_car_stmt->execute()) {
              $_SESSION['success_message'] = "Booking status updated and vehicle is now available.";
              header("Location: admin_notification.php");
              exit;
          } else {
              $_SESSION['error_message'] = "Failed to update car status.";
          }
          $update_car_stmt->close();
      } else {
          $_SESSION['error_message'] = "Failed to update booking status.";
      }
      $update_booking_stmt->close();
  } else {
      $_SESSION['error_message'] = "Booking not found.";
  }

  $car_stmt->close();
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="img/logo_web.png" type="image/png">
    <title>Notification Page</title>
    <script src="https://cdn.tailwindcss.com"></script>
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
    

    

    <div class="flex min-h-screen">
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
          <a href="admin_notification.php" class="flex justify-center items-center space-x-2"><img src="img/mail (1).png" alt="Mail Icon" class="w-6 h-6"></a>
          <a href="admin_settings.php" class="flex justify-center items-center space-x-2"><img src="img/setting (2).png" alt="Settings Icon" class="w-6 h-6"></a>
          <a href="admin_chat.php" class="flex justify-center items-center space-x-2 text-blue-500"><img src="img/bubble-chat.png" alt="Chat Icon" class="w-7 h-7"></a>
          <div class="px-6 py-44">
            <a href="login.php" class="fixed mt-14 flex justify-center items-center space-x-2"><img src="img/logout.png" alt="Logout Icon" class="w-6 h-6"></a>
          </div>
        </nav>
        </div>
      </nav>

<!-- Main content -->
<div class="flex-grow pl-6 pr-6 ml-[130px]">
    <div class="text-xl font-bold mb-4 ml-1">BOOKING REQUEST:</div>
    
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
        <!-- Check if there are booking requests -->
        <?php if (!empty($booking_requests)): ?>
            <?php foreach ($booking_requests as $request): ?>
                <?php
                // Fetch the user details based on user_id
                $user_id = $request['user_id'];
                $user_query = "SELECT fullname, email, number, profile_image, birthdate, license_front_image, license_back_image FROM users WHERE id = ?";
                $user_stmt = $link->prepare($user_query);
                $user_stmt->bind_param("i", $user_id);
                $user_stmt->execute();
                $user_stmt->bind_result($fullname, $email, $number, $profile_image, $birthdate, $license_front_image, $license_back_image);
                $user_stmt->fetch();
                $user_stmt->close();

                // Fetch the car details based on car_id
                $car_id = $request['car_id'];
                $car_query = "SELECT car_image FROM cars WHERE id = ?";
                $car_stmt = $link->prepare($car_query);
                $car_stmt->bind_param("i", $car_id);
                $car_stmt->execute();
                $car_stmt->bind_result($car_image);
                $car_stmt->fetch();
                $car_stmt->close();

                // Update car status based on bookings
                $car_status = updateCarStatus($link, $car_id);
                

                // Fetch the logged-in user's full name based on session ID
                $logged_in_user_id = $_SESSION['id'];
                $session_user_query = "SELECT fullname FROM users WHERE id = ?";
                $session_user_stmt = $link->prepare($session_user_query);
                $session_user_stmt->bind_param("i", $logged_in_user_id);
                $session_user_stmt->execute();
                $session_user_stmt->bind_result($session_fullname);
                $session_user_stmt->fetch();
                $session_user_stmt->close();
                ?>

                <!-- Booking request card -->
                <div class="bg-white p-4 rounded-lg border border-gray-300 shadow-lg flex flex-col justify-between relative">
                    <form method="POST" action="" class="absolute top-1 right-2">
                        <input type="hidden" name="delete_booking_id" value="<?php echo htmlspecialchars($request['id']); ?>">
                        <button type="button" class="text-red-500 hover:text-red-700 text-2xl font-bold" onclick="openDeleteModal(<?php echo htmlspecialchars($request['id']); ?>)">
                            &times;
                        </button>
                    </form>

                    <div>
                        <!-- Admin greeting -->
                        <div class="font-bold text-gray-800">HI <?php echo htmlspecialchars(strtoupper($session_fullname)); ?></div>

                 

                        <div class="text-gray-600">
                            <?php echo htmlspecialchars($fullname); ?> wants to rent this car from 
                            <?php echo htmlspecialchars($request['pickup_date']); ?> to 
                            <?php echo htmlspecialchars($request['dropoff_date']); ?>.
                        </div>
                        
                        <!-- Display car image if available -->
                        <?php if ($car_image): ?>
                            <img src="<?php echo htmlspecialchars($car_image); ?>" alt="Car Image" class="mt-4 rounded-lg w-full h-40 object-cover">
                        <?php else: ?>
                            <div class="text-gray-400 mt-4">No car image available.</div>
                        <?php endif; ?>

                        <!-- Display car status -->
                        <div class="font-bold mt-2 <?php echo $car_status === 'Booked' ? 'text-red-500' : 'text-green-500'; ?>">
                          Status: <?php echo $car_status; ?>
                        </div>

                        <!-- Display booking status -->
                        <div class="font-bold mt-2 <?php 
                          if ($request['booking_status'] === 'Approved') {
                              echo 'text-green-500'; 
                          } elseif ($request['booking_status'] === 'Declined') {
                              echo 'text-red-500'; 
                          } else {
                              echo 'text-blue-500'; 
                          } ?>">
                          Booking Status: <?php echo htmlspecialchars($request['booking_status']); ?>
                        </div>
                    
                        <!-- Approve/Decline buttons -->
                        <div class="flex justify-end space-x-2 mt-4">
                            <button type="button" class="bg-[#5A5A5A] text-white px-4 py-2 rounded-lg hover:bg-[#4A4A4A] transition" onclick="openUserDetailsModal(<?php echo htmlspecialchars($user_id); ?>, '<?php echo htmlspecialchars($fullname); ?>', '<?php echo htmlspecialchars($email); ?>', '<?php echo htmlspecialchars($number); ?>', '<?php echo htmlspecialchars($birthdate); ?>', '<?php echo htmlspecialchars($profile_image); ?>', '<?php echo htmlspecialchars($license_front_image); ?>', '<?php echo htmlspecialchars($license_back_image); ?>')">
                            View Details
                            </button>
                            <form method="POST" action="">
                                <input type="hidden" name="booking_id" value="<?php echo htmlspecialchars($request['id']); ?>">
                                <button type="submit" name="approve_booking" class="bg-[#00b3ff] text-white px-4 py-2 rounded-lg hover:bg-[#0099cc] transition">
                                APPROVE
                                </button>
                            </form>
                            <form method="POST" action="">
                                <input type="hidden" name="booking_id" value="<?php echo htmlspecialchars($request['id']); ?>">
                                <button type="submit" name="decline_booking" class="bg-[#F90d0d] text-white px-4 py-2 rounded-lg hover:bg-[#d00909] transition">
                                DECLINE
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="text-gray-600">No booking requests available.</div>
        <?php endif; ?>
    </div>
</div>
</div>
</div>



<!-- User Details Modal -->
<div id="userDetailsModal" class="fixed inset-0 flex items-center justify-center bg-black bg-opacity-50 hidden z-50">
    <div class="bg-gray-200 rounded-lg p-6 w-11/12 md:w-1/3 relative">
        <h2 class="text-xl font-bold mb-4" id="modalTitle">User Details</h2>
        <img id="profileImage" src="" alt="Profile Image" class="w-32 h-32 rounded-full mb-4 mx-auto">
        <div>
            <p><strong>Full Name:</strong> <span id="modalFullName"></span></p>
            <p><strong>Email:</strong> <span id="modalEmail"></span></p>
            <p><strong>Phone Number:</strong> <span id="modalNumber"></span></p>
            <p><strong>Birthdate:</strong> <span id="modalBirthdate"></span></p>
        </div>
        <div class="mt-4">
            <h3 class="font-bold">License Images:</h3>
            <div class="flex justify-center space-x-2">
                <img id="licenseFront" src="" alt="License Front" class="w-1/2 h-auto mt-2"> 
                <img id="licenseBack" src="" alt="License Back" class="w-1/2 h-auto mt-2">
            </div>
        </div>

        <button class="absolute top-1 right-2 text-gray-600 hover:text-gray-800 text-3xl font-bold" onclick="closeUserDetailsModal()">&times;</button>

    </div>
</div>



<!-- Delete Confirmation Modal -->
<div id="deleteModal" class="fixed inset-0 flex items-center justify-center bg-black bg-opacity-50 hidden z-50">
    <div class="bg-white p-6 rounded-lg shadow-lg w-96">
        <div class="text-lg font-bold">Confirm Delete</div>
        <p class="mt-2">Are you sure you want to delete this booking?</p>
        <div class="mt-4 flex justify-end space-x-2">
            <button onclick="closeDeleteModal()" class="px-4 py-2 bg-gray-300 rounded-lg hover:bg-gray-400">Cancel</button>
            <form method="POST" action="">
                <input type="hidden" id="deleteBookingId" name="delete_booking_id" value="">
                <button type="submit" name="delete_booking" class="px-4 py-2 bg-red-500 text-white rounded-lg hover:bg-red-600">Delete</button>
            </form>
        </div>
    </div>
</div>

<script>
      function openUserDetailsModal(userId, fullname, email, number, birthdate, profileImage, licenseFrontImage, licenseBackImage) {
        document.getElementById('modalTitle').textContent = 'User Details for ' + fullname;
        document.getElementById('modalFullName').textContent = fullname;
        document.getElementById('modalEmail').textContent = email;
        document.getElementById('modalNumber').textContent = number;
        document.getElementById('modalBirthdate').textContent = birthdate;

        // Set profile image and license images
        document.getElementById('profileImage').src = profileImage || 'path/to/default/profile-image.png';
        document.getElementById('licenseFront').src = licenseFrontImage || 'path/to/default/license-front.png';
        document.getElementById('licenseBack').src = licenseBackImage || 'path/to/default/license-back.png';

        document.getElementById('userDetailsModal').classList.remove('hidden');
    }

    function closeUserDetailsModal() {
        document.getElementById('userDetailsModal').classList.add('hidden');
    }
      
    function openModal(message) {
        document.getElementById('modalMessage').innerText = message;
        document.getElementById('successModal').classList.remove('hidden');
    }

    function closeModal() {
        document.getElementById('successModal').classList.add('hidden');
    }

    // Check for success or error messages
    <?php if (isset($_SESSION['success_message'])): ?>
        openModal("<?php echo $_SESSION['success_message']; unset($_SESSION['success_message']); ?>");
    <?php endif; ?>

    <?php if (isset($_SESSION['error_message'])): ?>
        openModal("<?php echo $_SESSION['error_message']; unset($_SESSION['error_message']); ?>");
    <?php endif; ?>


    function openDeleteModal(bookingId) {
        document.getElementById('deleteBookingId').value = bookingId;
        document.getElementById('deleteModal').classList.remove('hidden');
        
        document.body.classList.add('bg-gray-300');
    }

    function closeDeleteModal() {
        document.getElementById('deleteModal').classList.add('hidden');
        
        document.body.classList.remove('bg-gray-300');
    }
</script>
</body>
</html>
