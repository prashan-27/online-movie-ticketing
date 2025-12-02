<?php
include 'db.php';

if (isset($_GET['slot_id'])) {
    $slot_id = intval($_GET['slot_id']);
    
    // Get all theater seats with their booking status for the given slot
    $sql = "SELECT ts.id, ts.row_name, ts.seat_number, ts.status, st.name as type_name, st.price,
            CASE WHEN sb.id IS NOT NULL THEN 'booked' ELSE 'available' END as booking_status
            FROM theater_seats ts
            LEFT JOIN seat_types st ON ts.seat_type_id = st.id
            LEFT JOIN seat_bookings sb ON ts.id = sb.seat_id AND sb.slot_id = ?
            ORDER BY ts.row_name, ts.seat_number";
            
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $slot_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $seats = [];
    while ($row = $result->fetch_assoc()) {
        $seats[] = $row;
    }
    
    echo json_encode($seats);
    $stmt->close();
} else {
    echo json_encode(['error' => 'No slot ID provided']);
}

$conn->close();
?>