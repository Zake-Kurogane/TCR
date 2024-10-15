<?php
// Initialize the session (if needed)
session_start();

// Include the database connection file
include('config.php');

// Check if an ID is provided via GET
if (isset($_GET['id'])) {
    $id = $_GET['id'];

    // Check if the car has related bookings
    $check_sql = "SELECT COUNT(*) FROM bookings WHERE car_id = ?";
    if ($stmt = $link->prepare($check_sql)) {
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $stmt->bind_result($count);
        $stmt->fetch();
        $stmt->close();

        // If there are bookings, prevent deletion
        if ($count > 0) {
            $_SESSION['error_message'] = "Error: Cannot delete car with existing bookings.";
            header("Location: admin_dashboard.php");
            exit;
        } else {
            // If no bookings are found, proceed with the deletion of the car
            $sql = "DELETE FROM cars WHERE id = ?";
            if ($stmt = $link->prepare($sql)) {
                $stmt->bind_param("i", $id);

                // Execute the statement
                if ($stmt->execute()) {
                    // On successful deletion, set a session variable
                    $_SESSION['success_message'] = "Car deleted successfully!";
                } else {
                    // If there's an error, set a session variable for error
                    $_SESSION['error_message'] = "Error: Deleting car.";
                }

                // Close the statement
                $stmt->close();
            } else {
                // Error preparing the SQL statement
                $_SESSION['error_message'] = "Error: Preparing deletion!";
            }
        }
    } else {
        $_SESSION['error_message'] = "Error: Checking bookings!";
    }
} else {
    // Redirect back if no ID is provided
    $_SESSION['error_message'] = "Error: No car ID provided!";
}

// Close the database connection
$link->close();

// Redirect back to admin_dashboard.php
header("Location: admin_dashboard.php");
exit;
?>
