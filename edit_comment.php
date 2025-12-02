<?php
session_start();
include 'db.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Check if comment_id and new_comment are provided
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['comment_id']) && isset($_POST['comment'])) {
    $comment_id = $_POST['comment_id'];
    $new_comment = $_POST['comment'];
    $user_id = $_SESSION['user_id'];

    // Check if comment exists
    $check_sql = "SELECT id FROM movie_comments WHERE id = ?";
    $check_stmt = $conn->prepare($check_sql);
    $check_stmt->bind_param("i", $comment_id);
    $check_stmt->execute();
    $check_result = $check_stmt->get_result();
    $check_stmt->close();

    if ($check_result->num_rows > 0) {
        // Update the comment
        $update_sql = "UPDATE movie_comments SET comment = ? WHERE id = ?";
        $update_stmt = $conn->prepare($update_sql);
        $update_stmt->bind_param("si", $new_comment, $comment_id);
        $update_stmt->execute();
        $update_stmt->close();

        header("Location: my_bookings.php");
        exit();
    } else {
        // Comment doesn't exist
        header("Location: my_bookings.php?error=not_found");
        exit();
    }
} else {
    // Invalid request
    header("Location: my_bookings.php");
    exit();
}
?>