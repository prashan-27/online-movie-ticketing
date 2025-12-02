<?php
include 'db.php'; // Include database connection

if (isset($_GET['movie_id']) && isset($_GET['date'])) {
    $movie_id = $_GET['movie_id'];
    $date = $_GET['date'];

    // Fetch available time slots for the selected movie and date
    $sql = "SELECT id, slot_time FROM movie_slots WHERE movie_id = ? AND slot_date = ? AND available = 1 ORDER BY slot_time";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("is", $movie_id, $date);
    $stmt->execute();
    $result = $stmt->get_result();

    $slots = [];
    while ($row = $result->fetch_assoc()) {
        $slots[] = $row;
    }

    // Return as JSON
    echo json_encode($slots);
    $stmt->close();
} else {
    echo json_encode([]);
}

$conn->close();
?>
