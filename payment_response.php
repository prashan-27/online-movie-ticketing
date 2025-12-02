<?php
session_start();
include 'db.php';

// Verify payment success
if (isset($_SESSION['pending_booking'])) {
    $booking = $_SESSION['pending_booking'];
    $user_id = $booking['user_id'];
    $movie_id = $booking['movie_id'];
    $slot_id = $booking['slot_id'];
    $quantity = $booking['quantity'];
    $booking_date = $booking['booking_date'];
    $selected_seats = $booking['selected_seats'];
    $booking_id = isset($_SESSION['booking_id']) ? $_SESSION['booking_id'] : null;

    // Start transaction
    $conn->begin_transaction();
    try {
        // Check if booking already exists
        if (!$booking_id) {
            // Check if user already has a booking for this movie and slot
            $check_sql = "SELECT id FROM bookings WHERE user_id = ? AND movie_id = ? AND slot_id = ?";
            $check_stmt = $conn->prepare($check_sql);
            $check_stmt->bind_param("iii", $user_id, $movie_id, $slot_id);
            $check_stmt->execute();
            $check_result = $check_stmt->get_result();
            
            if ($check_result->num_rows > 0) {
                // Use existing booking
                $row = $check_result->fetch_assoc();
                $booking_id = $row['id'];
                $check_stmt->close();
            } else {
                // Insert booking
                $sql = "INSERT INTO bookings (user_id, movie_id, slot_id, quantity, booking_date) VALUES (?, ?, ?, ?, ?)";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("iiiis", $user_id, $movie_id, $slot_id, $quantity, $booking_date);
                $stmt->execute();
                $booking_id = $conn->insert_id;
                $stmt->close();
            }
        }

        // Insert seat bookings
        $seat_sql = "INSERT INTO seat_bookings (booking_id, seat_id, slot_id) VALUES (?, ?, ?)";
        $seat_stmt = $conn->prepare($seat_sql);
        foreach ($selected_seats as $seat_id) {
            $seat_stmt->bind_param("iii", $booking_id, $seat_id, $slot_id);
            $seat_stmt->execute();
        }

        $conn->commit();
        unset($_SESSION['pending_booking']);
        echo "<script>alert('Booking confirmed!'); window.location='my_bookings.php';</script>";
        exit();
    } catch (Exception $e) {
        $conn->rollback();
        echo "<script>alert('Booking confirmation failed. Please contact support.'); window.location='my_bookings.php';</script>";
        exit();
    }
} else {
    echo "<script>alert('Invalid booking request.'); window.location='index.php';</script>";
    exit();
}
?>