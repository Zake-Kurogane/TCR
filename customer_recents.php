<?php
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

?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Recent - TC Car Rental</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <link rel="icon" href="img/logo_web.png" type="image/png">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
  <script>
    // Toggle sidebar visibility
    function toggleSidebar() {
      document.getElementById('sidebar').classList.toggle('-translate-x-full');
      document.getElementById('overlay').classList.toggle('hidden');
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

    document.addEventListener('DOMContentLoaded', function () {
  // Get the modal
  var modal = document.getElementById("carModal");

  // Get the car card that opens the modal
  var card = document.getElementById("carCard");

  // Get the <button> element that closes the modal
  var closeBtn = document.querySelector(".close");

  // When the user clicks on the card, open the modal
  card.onclick = function () {
    modal.classList.remove('hidden');
  };

  // When the user clicks on close button, close the modal
  closeBtn.onclick = function () {
    modal.classList.add('hidden');
  };

  // When the user clicks anywhere outside the modal, close it
  window.onclick = function (event) {
    if (event.target == modal) {
      modal.classList.add('hidden');
    }
  };
});

  </script>
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
            <a href="customer_dashboard.php" class="flex justify-center items-center space-x-2"><img src="img/home.png" alt="Home Icon" class="w-7 h-7"></a>
            <a href="customer_calendar.php" class="flex justify-center items-center space-x-2"><img src="img/calendar.png" alt="Mail Icon" class="w-6 h-6"></a>
            <a href="customer_favourites.php" class="flex justify-center items-center space-x-2"><img src="img/heart.png" alt="Settings Icon" class="w-6 h-6"></a>
            <a href="customer_recents.php" class="flex justify-center items-center space-x-2 text-blue-500"><img src="img/watch (1).png" alt="Chat Icon" class="w-7 h-7"></a>
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
              <!-- Title -->
              <h2 class="text-2xl font-semibold">Recent Cars:</h2>

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
// Initialize the query to select cars that the user has booked
$sql = "
    SELECT 
        c.id, 
        c.car_status, 
        c.car_image, 
        c.car_brand, 
        c.car_description, 
        c.rent_price, 
        c.body_type, 
        c.transmission, 
        c.fuel_type, 
        b.booking_status 
    FROM cars c 
    JOIN bookings b ON c.id = b.car_id 
    WHERE b.user_id = ?";

// Get the user ID from the session
$userId = $_SESSION['id'];

// Prepare and bind the parameters to prevent SQL injection
$stmt = $link->prepare($sql);
$stmt->bind_param("i", $userId);

// Execute the statement
$stmt->execute();

// Get the result
$result = $stmt->get_result();

// Check if there are any results
if ($result && $result->num_rows > 0) {
    echo '<div class="grid grid-cols-3 gap-6 p-6">';

    while ($row = $result->fetch_assoc()) {
        $bookingStatus = $row['booking_status'];
        $isBooked = !is_null($bookingStatus);

        // Output only the booked car
        if ($isBooked) {
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

            // Determine booking status class and text
            $bookingStatusClass = '';
            $bookingStatusText = htmlspecialchars($bookingStatus);

            switch ($bookingStatus) {
                case 'Approved':
                    $bookingStatusClass = 'bg-green-100 text-green-600';
                    break;
                case 'Pending':
                    $bookingStatusClass = 'bg-blue-100 text-blue-600';
                    break;
                case 'Declined':
                    $bookingStatusClass = 'bg-red-100 text-red-600';
                    break;
                default:
                    $bookingStatusClass = 'bg-gray-200 text-gray-600';
            }

            // Output the car details
            echo '
            <div class="car-card bg-white rounded-lg shadow-lg p-4 cursor-pointer" data-car=\'' . $carData . '\'>
                <div class="flex items-center justify-between mb-4">
                    <span class="bg-' . ($row["car_status"] == "Available" ? 'green-100 text-green-600' : 
                    ($row["car_status"] == "Booked" ? 'blue-100 text-blue-600' : 'red-100 text-red-600')) . 
                    ' text-xs px-2 py-1 rounded-full shadow-lg">' . 
                    htmlspecialchars($row["car_status"]) . '</span>
                    <span class="' . $bookingStatusClass . ' text-xs px-2 py-1 rounded-full shadow-lg ml-2">' . 
                    htmlspecialchars($bookingStatusText) . '</span>
                    <!-- Separate heart button container -->
                    <div class="ml-auto favorite-container">
                        <button class="flex items-center favorite-button" onclick="toggleFavorite(' . $row['id'] . ', this); event.stopPropagation();">
                            <img src="img/heart (1).png" alt="favorite" class="w-6 h-6 heart-icon" data-favorited="false">
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

</main>
</div>
</div>
</body>
</html>

