<?php
session_start();
include 'db.php'; // Include database connection

// Redirect to login if the user is not logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Check if movie_id is provided
if (isset($_GET['movie_id'])) {
    $movie_id = $_GET['movie_id'];
    $slot_time = isset($_GET['slot_time']) ? $_GET['slot_time'] : ''; // Retrieve slot_time from query

    // Fetch movie details
    $sql = "SELECT * FROM movies WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $movie_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $movie = $result->fetch_assoc();
    } else {
        echo "Movie not found.";
        exit();
    }
    $stmt->close();

    // Fetch available dates for the movie
    $dates_sql = "SELECT DISTINCT slot_date FROM movie_slots WHERE movie_id = ? AND available = 1 ORDER BY slot_date";
    $stmt = $conn->prepare($dates_sql);
    $stmt->bind_param("i", $movie_id);
    $stmt->execute();
    $dates_result = $stmt->get_result();
    $dates = [];
    while ($row = $dates_result->fetch_assoc()) {
        $dates[] = $row['slot_date'];
    }
    $stmt->close();
} else {
    echo "No movie selected.";
    exit();
}

// Handle booking submission
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["book"])) {
    $slot_id = intval($_POST["slot_id"]);
    $user_id = $_SESSION["user_id"];
    $booking_date = date("Y-m-d H:i:s");
    $selected_seats = json_decode($_POST["selected_seats"]);
    $_SESSION['movie_name'] = $movie['name'];
    // Calculate total price
    $seat_price = 300; // Price per seat
    $total_price = count($selected_seats) * $seat_price;
    $_SESSION['total_price'] = $total_price;

    if (empty($selected_seats)) {
        echo "<script>alert('Please select at least one seat.');</script>";
        exit();
    }
    
    // Check if user already has a booking for this movie and slot
    $check_sql = "SELECT id FROM bookings WHERE user_id = ? AND movie_id = ? AND slot_id = ?";
    $check_stmt = $conn->prepare($check_sql);
    $check_stmt->bind_param("iii", $user_id, $movie_id, $slot_id);
    $check_stmt->execute();
    $check_result = $check_stmt->get_result();
    
    if ($check_result->num_rows > 0) {
        echo "<script>alert('You already have a booking for this movie and time slot.');</script>";
        $check_stmt->close();
        exit();
    }
    $check_stmt->close();

    // Insert booking record and get the booking ID
    $insert_booking_sql = "INSERT INTO bookings (user_id, movie_id, slot_id, quantity, booking_date, price) VALUES (?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($insert_booking_sql);
    $quantity = count($selected_seats);
    $stmt->bind_param("iiiisd", $user_id, $movie_id, $slot_id, $quantity, $booking_date, $total_price);
    $stmt->execute();
    $booking_id = $conn->insert_id;
    $stmt->close();

    // Store booking details in session for payment processing
    $_SESSION['pending_booking'] = [
        'user_id' => $user_id,
        'movie_id' => $movie_id,
        'slot_id' => $slot_id,
        'quantity' => $quantity,
        'booking_date' => $booking_date,
        'selected_seats' => $selected_seats,
    ];
    $_SESSION['booking_id'] = $booking_id;

    // Redirect to payment page
    echo "<script>window.location='checkout.php';</script>";
    exit();
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Book Movie</title>
    <link rel="stylesheet" href="css/seat_selection.css">
  <script src="js/seat_selection.js"></script>
  <script>
    document.addEventListener('DOMContentLoaded', function() {
        // Extract query parameters from the URL
        const urlParams = new URLSearchParams(window.location.search);
        const slotTime = urlParams.get('slot_time');
        const slotDate = urlParams.get('slot_date');

        // Set the date field if slot_date is provided
        if (slotDate) {
            document.getElementById('date').value = slotDate;
            fetchTimes(slotDate); // Fetch available times for the selected date
        }

        // Set the time field if slot_time is provided
        if (slotTime) {
            const timeSelect = document.getElementById('slot_id');
            // Wait for the options to be populated
            setTimeout(() => {
                const options = timeSelect.options;
                for (let i = 0; i < options.length; i++) {
                    if (options[i].textContent === decodeURIComponent(slotTime)) {
                        options[i].selected = true;
                        loadSeats(options[i].value); // Load seats for the selected time
                        break;
                    }
                }
            }, 500); // Add a small delay to ensure options are loaded
        }
    });
    function fetchTimes(date) {
      const movieId = <?php echo $movie_id; ?>;
      fetch(`fetch_times.php?movie_id=${movieId}&date=${date}`)
        .then(response => response.json())
        .then(data => {
          const timeSelect = document.getElementById("slot_id");
          timeSelect.innerHTML = "<option value=''>Select a time</option>";
          if (data.length > 0) {
            data.forEach(slot => {
              const option = document.createElement("option");
              option.value = slot.id;
              option.textContent = slot.slot_time;
              timeSelect.appendChild(option);
            });
          } else {
            timeSelect.innerHTML = "<option value=''>No available slots</option>";
          }
        })
        .catch(error => console.error('Error fetching time slots:', error));
    }
  </script>
  <style>
    body {
      font-family: Arial, sans-serif;
      background-color: #f3f3f3;
      margin: 0;
      padding: 0;
    }

    /* Header Styles */
    header {
      background: linear-gradient(135deg, #74ABE2, #5563DE);
      color: #fff;
      padding: 1rem 2rem;
      display: flex;
      justify-content: space-between;
      align-items: center;
    }

    header .logo a {
      text-decoration: none;
      color: #fff;
      font-size: 25px;
      font-weight: bold;
    }

    header nav {
      margin-top: 10px;
    }

    header nav a {
      color: #fff;
      text-decoration: none;
      margin: 0 15px;
      font-size: 16px;
    }

    header nav a:hover {
      text-decoration: underline;
    }

    /* Booking Form Styles */
    .booking-container {
      max-width: 600px;
      margin: auto;
      background-color: #fff;
      padding: 20px;
      border-radius: 8px;
      box-shadow: 0 2px 4px rgba(0,0,0,0.1);
      margin-top: 30px;
    }

    h2 {
      text-align: center;
      font-size: 24px;
      margin-bottom: 20px;
    }

    .movie-details {
      text-align: center;
      margin-bottom: 20px;
    }

    .movie-details img {
      max-width: 100%;
      height: auto;
      border-radius: 4px;
    }

    form {
      display: flex;
      flex-direction: column;
    }

    label {
      margin-bottom: 8px;
      font-size: 14px;
      color: #555;
    }

    input, select {
      padding: 10px;
      margin-bottom: 20px;
      font-size: 16px;
      border: 1px solid #ddd;
      border-radius: 4px;
    }

    button {
      padding: 12px;
      background-color: #5563DE;
      color: #fff;
      border: none;
      border-radius: 4px;
      font-size: 16px;
      cursor: pointer;
    }

    button:hover {
      background-color: #3f4bb8;
    }

    /* Footer Styles */
    footer {
      background: linear-gradient(135deg, #74ABE2, #5563DE);
      color: #fff;
      text-align: center;
      padding: 1rem 0;
      margin-top: 2rem;
    }

    footer .contact-info {
      font-size: 1rem;
      margin-top: 1rem;
    }
  </style>
</head>
<body>

  <!-- Header -->
  <header>
    <div class="logo">
      <a href="base2.php">Movie Ticketing</a>
    </div>
    <nav>
      
      <a href="#contact">Contact</a>
      <?php if (isset($_SESSION['user_id'])): ?>
        <span>Hello, <?php echo htmlspecialchars($_SESSION['user_email']); ?></span>
        <a href="logout.php">Logout</a>
      <?php else: ?>
        <a href="login.php">Login</a>
      <?php endif; ?>
    </nav>
  </header>

  <!-- Booking Form -->
  <div class="booking-container">
    <h2>Book Movie Ticket</h2>
    <div class="movie-details">
      <img src="<?php echo $movie['photo']; ?>" alt="Movie Poster">
      <h3><?php echo $movie['name']; ?></h3>
      <p><?php echo $movie['description']; ?></p>
      <p>Release Date: <?php echo $movie['release_date']; ?></p>
    </div>
    <form method="POST" id="booking-form" onsubmit="return validateBooking()">
      <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
      <script src="https://code.jquery.com/ui/1.12.1/jquery-ui.min.js"></script>
      <link rel="stylesheet" href="https://code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">
      <label for="date">Select Date:</label>
      <input type="text" id="date" name="date" value="<?php echo htmlspecialchars($_GET['date'] ?? ''); ?>" required>
      
      <script>
        $(function() {
          const validDates = <?php echo json_encode($dates); ?>;
          $("#date").datepicker({
            dateFormat: "yy-mm-dd",
            beforeShowDay: function(date) {
              const dateString = $.datepicker.formatDate('yy-mm-dd', date);
              return [validDates.includes(dateString), "", validDates.includes(dateString) ? "Available" : "Unavailable"];
            },
            onSelect: function(dateText) {
              fetchTimes(dateText);
            }
          });
          });
      </script>
      <label for="slot_id">Select Time:</label>
      <select id="slot_id" name="slot_id" onchange="loadSeats(this.value)" required>
        <option value='<?php echo htmlspecialchars($slot_time); ?>' selected><?php echo htmlspecialchars($slot_time); ?></option>
      </select>
      <div id="seat-map" class="seat-map"></div>
      <div id="selected-seats-info"></div>
      <button type="submit" name="book">Book Now</button>
    </form>
    <script>
    function validateBooking() {
      if (selectedSeats.length === 0) {
        alert('Please select at least one seat.');
        return false;
      }
      return true;
    }
    </script>
  </div>

  <!-- Footer -->
  <footer>
    <div class="contact-info" id="contact">
      <p>&copy; 2025 Movie Ticketing System. For inquiries, contact us at:</p>
      <p>Email: <a href="mailto:support@movieticketing.com">support@movieticketing.com</a></p>
      <p>Phone: +1 (123) 456-7890</p>
    </div>
  </footer>

</body>
</html>
