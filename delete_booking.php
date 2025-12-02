<?php
session_start();
include 'db.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Check if booking_id is provided
if (isset($_POST['booking_id'])) {
    $booking_id = $_POST['booking_id'];
    $user_id = $_SESSION['user_id'];

    // Start transaction
    $conn->begin_transaction();

    try {
        // First, verify that the booking exists and belongs to the user
        $check_booking_sql = "SELECT id FROM bookings WHERE id = ? AND user_id = ?";
        $stmt = $conn->prepare($check_booking_sql);
        $stmt->bind_param("ii", $booking_id, $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 0) {
            throw new Exception("Booking not found or does not belong to you.");
        }
        
        // Delete associated comments
        $delete_comments_sql = "DELETE FROM movie_comments WHERE booking_id = ?";
        $stmt = $conn->prepare($delete_comments_sql);
        $stmt->bind_param("i", $booking_id);
        $stmt->execute();

        // Delete associated seat bookings
        $delete_seats_sql = "DELETE FROM seat_bookings WHERE booking_id = ?";
        $stmt = $conn->prepare($delete_seats_sql);
        $stmt->bind_param("i", $booking_id);
        $stmt->execute();

        // Delete the booking
        $delete_booking_sql = "DELETE FROM bookings WHERE id = ? AND user_id = ?";
        $stmt = $conn->prepare($delete_booking_sql);
        $stmt->bind_param("ii", $booking_id, $user_id);
        $stmt->execute();

        // Commit transaction
        $conn->commit();

        // Redirect back to my bookings with success message
        $_SESSION['success_message'] = "Booking deleted successfully.";
        header("Location: my_bookings.php");
        exit();
    } catch (Exception $e) {
        // Rollback transaction on error
        $conn->rollback();
        $_SESSION['error_message'] = "Failed to delete booking: " . $e->getMessage();
        header("Location: my_bookings.php");
        exit();
    }
} else {
    // If no booking_id provided, redirect back
    header("Location: my_bookings.php");
    exit();
}
?>