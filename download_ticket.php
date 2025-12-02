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

    // Fetch booking details
    $sql = "SELECT m.name as movie_name, ms.slot_date, ms.slot_time, 
                   (SELECT GROUP_CONCAT(seat_id) FROM seat_bookings WHERE booking_id = b.id) as seat_numbers,
                   b.price
            FROM bookings b
            JOIN movies m ON b.movie_id = m.id
            JOIN movie_slots ms ON b.slot_id = ms.id
            WHERE b.id = ? AND b.user_id = ?";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $booking_id, $_SESSION['user_id']);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $booking_details = $result->fetch_assoc();
        $pdf = generateTicketPDF($booking_details);

        // Set headers to download the PDF
        $pdf->Output('F', 'ticket.pdf');

        header('Content-Type: application/pdf');
        header('Content-Disposition: attachment; filename="ticket.pdf"');
        header('Content-Length: ' . filesize('ticket.pdf'));
        readfile('ticket.pdf');
    } else {
        echo "Booking not found.";
    }
    $stmt->close();
} else {
    echo "No booking selected.";
}


function generateTicketPDF($booking_details) {
    require('fpdf/fpdf.php');
    $pdf = new FPDF();
    $pdf->AddPage();
    $pdf->SetFont('Arial', 'B', 16);
    $pdf->Cell(40, 10, 'Movie Booking Details');
    $pdf->Ln();
    $pdf->SetFont('Arial', '', 12);
    $pdf->Cell(40, 10, 'Movie: ' . $booking_details['movie_name']);
    $pdf->Ln();
    $pdf->Cell(40, 10, 'Date: ' . $booking_details['slot_date']);
    $pdf->Ln();
    $pdf->Cell(40, 10, 'Time: ' . $booking_details['slot_time']);
    $pdf->Ln();
    $pdf->Cell(40, 10, 'Seats: ' . $booking_details['seat_numbers']);
    $pdf->Ln();
    $pdf->Cell(40, 10, 'Price: ' . $booking_details['price']);
    return $pdf;
}
?>