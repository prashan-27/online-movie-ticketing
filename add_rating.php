<?php
session_start();
include 'db.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Check if required data is provided
if (isset($_POST['movie_id']) && isset($_POST['rating'])) {
    $user_id = $_SESSION['user_id'];
    $movie_id = $_POST['movie_id'];
    $rating = $_POST['rating'];

    // Check if user has booked this movie
    $check_booking_sql = "SELECT id FROM bookings WHERE user_id = ? AND movie_id = ?";
    $stmt = $conn->prepare($check_booking_sql);
    $stmt->bind_param("ii", $user_id, $movie_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        // Check if user has already rated this movie
        $check_rating_sql = "SELECT id FROM ratings WHERE user_id = ? AND movie_id = ?";
        $stmt = $conn->prepare($check_rating_sql);
        $stmt->bind_param("ii", $user_id, $movie_id);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows == 0) {
            // Insert new rating
            $insert_sql = "INSERT INTO ratings (user_id, movie_id, rating) VALUES (?, ?, ?)";
            $stmt = $conn->prepare($insert_sql);
            $stmt->bind_param("iii", $user_id, $movie_id, $rating);
            $stmt->execute();
            $update_avg_sql = "UPDATE movies SET avg_rating = (SELECT AVG(rating) FROM ratings WHERE movie_id = ?) WHERE id = ?";
            $stmt = $conn->prepare($update_avg_sql);
            $stmt->bind_param("ii", $movie_id, $movie_id);
            $stmt->execute();
            echo "<script>alert('Rating submitted successfully!');window.location='my_bookings.php';</script>";
        } else {
            echo "<script>alert('You have already rated this movie.');window.location='my_bookings.php';</script>";
        }
    } else {
        echo "<script>alert('You need to book this movie before rating.');window.location='my_bookings.php';</script>";
    }
} else {
    echo "<script>alert('Invalid request.');window.location='my_bookings.php';</script>";
}
?>