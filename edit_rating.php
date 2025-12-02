<?php
session_start();
include 'db.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Check if rating_id and new_rating are provided
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['rating_id']) && isset($_POST['rating'])) {
    $rating_id = $_POST['rating_id'];
    $new_rating = $_POST['rating'];
    $user_id = $_SESSION['user_id'];

    // Check if rating exists and belongs to the user
    $check_sql = "SELECT id FROM ratings WHERE id = ? AND user_id = ?";
    $check_stmt = $conn->prepare($check_sql);
    $check_stmt->bind_param("ii", $rating_id, $user_id);
    $check_stmt->execute();
    $check_result = $check_stmt->get_result();
    $check_stmt->close();

    if ($check_result->num_rows > 0) {
        // Update the rating
        $update_sql = "UPDATE ratings SET rating = ? WHERE id = ?";
        $update_stmt = $conn->prepare($update_sql);
        $update_stmt->bind_param("ii", $new_rating, $rating_id);
        $update_stmt->execute();
        $update_stmt->close();

        // Update average rating in movies table
        $get_movie_sql = "SELECT movie_id FROM ratings WHERE id = ?";
        $get_movie_stmt = $conn->prepare($get_movie_sql);
        $get_movie_stmt->bind_param("i", $rating_id);
        $get_movie_stmt->execute();
        $movie_result = $get_movie_stmt->get_result();
        $movie_data = $movie_result->fetch_assoc();
        $get_movie_stmt->close();

        if ($movie_data) {
            $update_avg_sql = "UPDATE movies SET avg_rating = (SELECT AVG(rating) FROM ratings WHERE movie_id = ?) WHERE id = ?";
            $update_avg_stmt = $conn->prepare($update_avg_sql);
            $update_avg_stmt->bind_param("ii", $movie_data['movie_id'], $movie_data['movie_id']);
            $update_avg_stmt->execute();
            $update_avg_stmt->close();
        }

        echo "<script>alert('Rating updated successfully!');window.location='my_bookings.php';</script>";
    } else {
        echo "<script>alert('Rating not found or unauthorized.');window.location='my_bookings.php';</script>";
    }
} else {
    echo "<script>alert('Invalid request.');window.location='my_bookings.php';</script>";
}
?>