<?php
include 'config.php';

// Start session to access session variables
session_start();

// Check if the user is logged in, if not then redirect him to login page
if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true){
  header("location: login.php");
  exit;
}

// Check if the session variable for user ID is set
if (isset($_SESSION['id'])) {
    // Query to retrieve approved bookings for the logged-in user
    $query = "SELECT * FROM bookings WHERE booking_status = 'Approved' AND user_id = ?";
    $stmt = mysqli_prepare($link, $query);
    // Bind the user_id parameter
    mysqli_stmt_bind_param($stmt, 'i', $_SESSION['id']);
    mysqli_stmt_execute($stmt);
    
    // Fetch all approved bookings for the user
    $result = mysqli_stmt_get_result($stmt);
    $bookings = mysqli_fetch_all($result, MYSQLI_ASSOC);
    

    mysqli_free_result($result);

    mysqli_stmt_close($stmt);
} else {
    $bookings = [];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Calendar - TC Car Rental</title>
    <link rel="icon" href="img/logo_web.png" type="image/png">
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        let currentDate = new Date();
        
        // Get bookings data from PHP
        const bookings = <?php echo json_encode($bookings); ?>;

        function renderCalendar() {
            const monthYear = document.getElementById("monthYear");
            const calendarBody = document.getElementById("calendarBody");

            // Set the month and year in the header
            monthYear.innerText = `${currentDate.toLocaleString('default', { month: 'long' })} ${currentDate.getFullYear()}`;

            // Get the first day of the month and the total days in the month
            const firstDay = new Date(currentDate.getFullYear(), currentDate.getMonth(), 1);
            const lastDay = new Date(currentDate.getFullYear(), currentDate.getMonth() + 1, 0);
            const totalDays = lastDay.getDate();
            const startDay = firstDay.getDay(); // 0 (Sunday) to 6 (Saturday)

            // Clear previous calendar rows
            calendarBody.innerHTML = "";

            // Adjust startDay to be 0 (Monday) to 6 (Sunday)
            const adjustedStartDay = (startDay + 6) % 7;

            // Create a variable to track the current day being added
            let dayCounter = 1;

            // Loop through the weeks
            for (let week = 0; week < 6; week++) {
                const row = document.createElement("tr");
                row.className = "h-16";

                // Loop through the days of the week
                for (let day = 0; day < 7; day++) {
                    const cell = document.createElement("td");

                    // Check if it's the right position to add a day
                    if (week === 0 && day < adjustedStartDay) {
                        cell.innerHTML = "";
                    } else if (dayCounter <= totalDays) {
                        cell.innerHTML = dayCounter;

                        // Check for booked dates
                    bookings.forEach(booking => {
                        const pickupDate = new Date(booking.pickup_date);
                        const dropoffDate = new Date(booking.dropoff_date);
                        const bookingId = booking.id;

                        //Log the booking dates and current day being checked
                        console.log(`Checking booking from ${pickupDate.toDateString()} to ${dropoffDate.toDateString()} against ${currentDate.getFullYear()}-${currentDate.getMonth() + 1}-${dayCounter}`);

                        // Check if the current day is the pickup date
                        if (pickupDate.toDateString() === new Date(currentDate.getFullYear(), currentDate.getMonth(), dayCounter).toDateString()) {
                            cell.className = "bg-green-500 text-white rounded-full";
                            cell.onclick = () => openModal(bookingId);
                        }

                        // Check if the current day is the dropoff date
                        if (dropoffDate.toDateString() === new Date(currentDate.getFullYear(), currentDate.getMonth(), dayCounter).toDateString()) {
                            cell.className = "bg-red-500 text-white rounded-full";
                            cell.onclick = () => openModal(bookingId);
                        }

                        // Check if the current day is within the booking dates
                        if (pickupDate < new Date(currentDate.getFullYear(), currentDate.getMonth(), dayCounter) && 
                            dropoffDate > new Date(currentDate.getFullYear(), currentDate.getMonth(), dayCounter)) {
                            cell.className = "bg-yellow-500 text-white rounded-full";
                            cell.onclick = () => openModal(bookingId);
                        }
                    });

                        dayCounter++;
                    } else {
                        cell.innerHTML = "";
                    }

                    // Append the cell to the row
                    row.appendChild(cell);
                }

                // Append the row to the calendar body
                calendarBody.appendChild(row);

                // Break out of the loop if we've added all days of the month
                if (dayCounter > totalDays) {
                    break;
                }
            }
        }

        function changeMonth(direction) {
            currentDate.setMonth(currentDate.getMonth() + direction);
            renderCalendar();
        }

        document.addEventListener("DOMContentLoaded", () => {
            renderCalendar();
        });
    </script>
</head>

<body class="bg-gray-100">
    <!-- Booking Details Modal -->
    <div id="bookingModal" class="fixed inset-0 flex items-center justify-center bg-black bg-opacity-50 hidden">
        <div class="bg-white rounded-lg shadow-lg p-6 w-1/2">
            <h2 id="modalTitle" class="text-2xl font-bold mb-4"></h2>
            <img id="carImage" src="" alt="Car Image" class="w-full h-48 object-cover mb-4">
            <div id="modalContent" class="text-gray-700"></div>
            <button onclick="closeModal()" class="mt-4 bg-blue-500 text-white rounded px-4 py-2">Close</button>
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
            <a href="customer_calendar.php" class="flex justify-center items-center space-x-2"><img src="img/calendar (1).png" alt="Mail Icon" class="w-6 h-6"></a>
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

        <!-- Main content (Calendar) -->
        <div class="flex-1 p-8 bg-white shadow-md ml-[128px]">
            <!-- Calendar Header -->
            <div class="flex items-center justify-between mb-4">
                <button class="text-2xl font-bold" onclick="changeMonth(-1)">&lt;</button>
                <h2 id="monthYear" class="text-xl font-semibold"></h2>
                <button class="text-2xl font-bold" onclick="changeMonth(1)">&gt;</button>
            </div>

            <!-- Calendar Table -->
            <table class="w-full table-fixed text-center">
                <thead>
                    <tr class="text-gray-500">
                        <th class="w-1/7">Mon</th>
                        <th class="w-1/7">Tue</th>
                        <th class="w-1/7">Wed</th>
                        <th class="w-1/7">Thu</th>
                        <th class="w-1/7">Fri</th>
                        <th class="w-1/7">Sat</th>
                        <th class="w-1/7">Sun</th>
                    </tr>
                </thead>
                <tbody id="calendarBody">
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>
