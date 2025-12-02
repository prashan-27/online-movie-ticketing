<?php
include 'db.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    switch ($action) {
        case 'update_layout':
            $rows = intval($_POST['rows']);
            $seatsPerRow = intval($_POST['seats_per_row']);
            
            try {
                // Start transaction
                $conn->begin_transaction();
                
                // Clear existing seats
                $conn->query("DELETE FROM seat_bookings WHERE seat_id IN (SELECT id FROM theater_seats)");
                $conn->query("DELETE FROM theater_seats");
                
                // Create new seats
                for ($row = 0; $row < $rows; $row++) {
                    $rowName = chr(65 + $row); // Convert 0 to 'A', 1 to 'B', etc.
                    
                    for ($seatNum = 1; $seatNum <= $seatsPerRow; $seatNum++) {
                        $seatType = ($row < 2) ? 2 : 1; // First two rows VIP, rest Regular
                        
                        $stmt = $conn->prepare("INSERT INTO theater_seats (row_name, seat_number, seat_type_id) VALUES (?, ?, ?)");
                        $stmt->bind_param('sii', $rowName, $seatNum, $seatType);
                        $stmt->execute();
                    }
                }
                
                $conn->commit();
                echo json_encode(['success' => true]);
            } catch (Exception $e) {
                $conn->rollback();
                echo json_encode(['success' => false, 'error' => $e->getMessage()]);
            }
            break;
            
        case 'update_multiple_seats':
            $seats = json_decode($_POST['seats'], true);
            $newType = intval($_POST['type_id']);
            
            try {
                $conn->begin_transaction();
                
                $stmt = $conn->prepare("UPDATE theater_seats SET seat_type_id = ? WHERE row_name = ? AND seat_number = ?");
                
                foreach ($seats as $seat) {
                    $stmt->bind_param('isi', $newType, $seat['row'], $seat['number']);
                    $stmt->execute();
                }
                
                $conn->commit();
                echo json_encode(['success' => true]);
            } catch (Exception $e) {
                $conn->rollback();
                echo json_encode(['success' => false, 'error' => $e->getMessage()]);
            }
            break;
            
        default:
            echo json_encode(['success' => false, 'error' => 'Invalid action']);
    }
} else {
    echo json_encode(['success' => false, 'error' => 'Invalid request method']);
}