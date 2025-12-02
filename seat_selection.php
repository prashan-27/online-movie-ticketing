<?php

// Constants for seat layout
const ROWS = ['A', 'B', 'C', 'D', 'E', 'F'];
const SEATS_PER_ROW = 10;

// Initialize seat selection
$selectedSeats = [];
$seatData = [];

// Function to generate seat map HTML
function generateSeatMap() {
    $html = '<div class="seat-map">';
    
    // Create legend
    $html .= '<div class="seat-legend">
                <div class="legend-item"><span class="seat-icon available"></span> Available</div>
                <div class="legend-item"><span class="seat-icon selected"></span> Selected</div>
                <div class="legend-item"><span class="seat-icon booked"></span> Booked</div>
                <div class="legend-item"><span class="seat-icon maintenance"></span> Maintenance</div>
              </div>';

    // Create screen indicator
    $html .= '<div class="screen">SCREEN</div>';

    // Create seat container
    $html .= '<div class="seat-container">';

    // Generate rows and seats
    foreach (ROWS as $row) {
        $html .= '<div class="seat-row">';
        $html .= '<div class="row-label">' . $row . '</div>';

        for ($i = 1; $i <= SEATS_PER_ROW; $i++) {
            $seatId = $row . $i;
            $status = getSeatStatus($row, $i);
            $html .= '<div class="seat ' . $status . '" data-row="' . $row . '" data-number="' . $i . '" onclick="toggleSeatSelection('' . $seatId . '')">' . $i . '</div>';
        }

        $html .= '</div>';
    }

    $html .= '</div></div>';
    return $html;
}

// Function to get seat status
function getSeatStatus($row, $number) {
    global $seatData;
    foreach ($seatData as $seat) {
        if ($seat['row_name'] == $row && $seat['seat_number'] == $number) {
            return $seat['booking_status'];
        }
    }
    return 'available';
}

// Function to handle seat selection
function toggleSeatSelection($seatId) {
    global $selectedSeats;
    $seatIndex = array_search($seatId, $selectedSeats);

    if ($seatIndex === false) {
        $selectedSeats[] = $seatId;
    } else {
        array_splice($selectedSeats, $seatIndex, 1);
    }

    updateSelectedSeatsInfo();
}

// Function to update selected seats info
function updateSelectedSeatsInfo() {
    global $selectedSeats, $seatData;
    $totalPrice = 0;
    $seatsList = [];

    foreach ($selectedSeats as $seatId) {
        $row = substr($seatId, 0, 1);
        $number = substr($seatId, 1);

        foreach ($seatData as $seat) {
            if ($seat['row_name'] == $row && $seat['seat_number'] == $number) {
                $totalPrice += $seat['price'];
                $seatsList[] = $seat['row_name'] . $seat['seat_number'] . ' (' . $seat['type_name'] . ')';
                break;
            }
        }
    }

    return '<div id="selected-seats-info">
            <p>Selected Seats: ' . implode(', ', $seatsList) . '</p>
            <p>Total Price: ' . number_format($totalPrice, 2) . '</p>
            <input type="hidden" name="selected_seats" value='' . json_encode($selectedSeats) . ''>
          </div>';
}

?>

<!DOCTYPE html>
<html>
<head>
    <title>Seat Selection</title>
    <link rel="stylesheet" href="css/seat_selection.css">
</head>
<body>
    <?php echo generateSeatMap(); ?>
    <div id="selected-seats-container">
        <?php echo updateSelectedSeatsInfo(); ?>
    </div>
</body>
</html>