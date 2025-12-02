<?php
require('fpdf/fpdf.php');
include 'db.php';

if (isset($_GET['booking_id'])) {
    $booking_id = intval($_GET['booking_id']);

    // Fetch booking details
    $sql = "SELECT b.id as booking_id, b.quantity, b.booking_date, m.name as movie_name, m.genre, ms.slot_date, ms.slot_time, b.user_id, (SELECT GROUP_CONCAT(seat_id) FROM seat_bookings WHERE booking_id = b.id) as seat_numbers FROM bookings b JOIN movies m ON b.movie_id = m.id JOIN movie_slots ms ON b.slot_id = ms.id WHERE b.id = ?";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $booking_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $booking = $result->fetch_assoc();

        // Check if the booking belongs to the logged-in user
        if ($booking['user_id'] !== $_SESSION['user_id']) {
            echo 'Booking not found';
            exit();
        }

        // Create PDF
        $pdf = new FPDF();
        $pdf->AddPage();
        $pdf->SetFont('Arial', 'B', 16);
        $pdf->Cell(40, 10, 'Movie Booking Details', 0, 1, 'C', true);
        $pdf->Ln();
        $pdf->SetFont('Arial', '', 12);
        $pdf->Cell(40, 10, 'Movie: ' . $booking['movie_name'], 0, 1, 'L', true);
        $pdf->Ln();
        $pdf->Cell(40, 10, 'Genre: ' . $booking['genre'], 0, 1, 'L', true);
        $pdf->Ln();
        $pdf->Cell(40, 10, 'Date: ' . $booking['slot_date'], 0, 1, 'L', true);
        $pdf->Ln();
        $pdf->Cell(40, 10, 'Time: ' . $booking['slot_time'], 0, 1, 'L', true);
        $pdf->Ln();
        $pdf->Cell(40, 10, 'Seats: ' . $booking['seat_numbers'], 0, 1, 'L', true);
        $pdf->Ln();
        $pdf->Cell(40, 10, 'Quantity: ' . $booking['quantity'], 0, 1, 'L', true);
        $pdf->Ln();
        $pdf->Cell(40, 10, 'Booking Date: ' . $booking['booking_date'], 0, 1, 'L', true);

        $pdf->Output();
    } else {
        echo 'No booking found';
    }

    $stmt->close();
} else {
    echo 'Invalid request';
}

$conn->close();

$pdf->SetFillColor(255, 255, 255);
$pdf->SetTextColor(0, 0, 0);
$pdf->Output('D', 'ticket.pdf');
?>