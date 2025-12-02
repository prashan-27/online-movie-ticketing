s<?php
session_start();
include 'db.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Check if form data is submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $booking_id = $_POST['booking_id'];
    $comment = trim($_POST['comment']);
    $user_id = $_SESSION['user_id'];

    // Validate comment
    if (empty($comment)) {
        $_SESSION['error'] = "Comment cannot be empty.";
        header("Location: my_bookings.php");
        exit();
    }

    // Check if comment already exists
    $check_sql = "SELECT id FROM movie_comments WHERE booking_id = ?";
    $stmt = $conn->prepare($check_sql);
    $stmt->bind_param("i", $booking_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        // Update existing comment
        $update_sql = "UPDATE movie_comments SET comment = ?, updated_at = NOW() WHERE booking_id = ?";
        $stmt = $conn->prepare($update_sql);
        $stmt->bind_param("si", $comment, $booking_id);
    } else {
        // Insert new comment
        $insert_sql = "INSERT INTO movie_comments (booking_id, user_id, comment) VALUES (?, ?, ?)";
        $stmt = $conn->prepare($insert_sql);
        $stmt->bind_param("iis", $booking_id, $user_id, $comment);
    }

    if ($stmt->execute()) {
        $_SESSION['success'] = "Review " . ($result->num_rows > 0 ? "updated" : "submitted") . " successfully.";
    } else {
        $_SESSION['error'] = "Error processing your review.";
    }
    $stmt->close();
}
header("Location: my_bookings.php");
?>