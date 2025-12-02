<?php
include 'db.php'; // Database connection
// -------------------
// HANDLE DELETIONS
// -------------------
// Delete movie
if (isset($_GET['delete_movie'])) {
    $id = $_GET['delete_movie'];
    // Delete the movie poster from the filesystem
    $sql = "SELECT photo FROM movies WHERE id = $id";
    $result = $conn->query($sql);
    if ($result && $result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $imagePath = $row['photo'];
        if (file_exists($imagePath)) {
            unlink($imagePath);
        }
    }
    $sql = "DELETE FROM movies WHERE id = $id";
    if ($conn->query($sql)) {
        echo "<script>alert('Movie deleted successfully!'); window.location='admin.php';</script>";
    } else {
        echo "<script>alert('Error deleting movie!');</script>";
    }
}
// Delete booking
if (isset($_GET['delete_booking'])) {
    $booking_id = $_GET['delete_booking'];
    $conn->begin_transaction();
    try {
        // Delete associated seat bookings
        $delete_seats_sql = "DELETE FROM seat_bookings WHERE booking_id = ? OR slot_id = ?";
        $stmt = $conn->prepare($delete_seats_sql);
        $stmt->bind_param("ii", $booking_id, $slot_id);
        $stmt->execute();

        // Delete the booking
        $delete_booking_sql = "DELETE FROM bookings WHERE id = ?";
        $stmt = $conn->prepare($delete_booking_sql);
        $stmt->bind_param("i", $booking_id);
        $stmt->execute();

        $conn->commit();
        echo "<script>alert('Booking deleted successfully!'); window.location='admin.php';</script>";
    } catch (Exception $e) {
        $conn->rollback();
        echo "<script>alert('Error deleting booking!');</script>";
    }
}
// Delete movie slot
if (isset($_GET['delete_slot'])) {
    $slot_id = $_GET['delete_slot'];
    // First delete associated seat bookings
    $delete_seats_sql = "DELETE FROM seat_bookings WHERE slot_id = ?";
    $stmt = $conn->prepare($delete_seats_sql);
    $stmt->bind_param("i", $slot_id);
    $stmt->execute();
    
    // Now delete the movie slot
    $sql = "DELETE FROM movie_slots WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $slot_id);
    if ($stmt->execute()) {
        echo "<script>alert('Slot deleted successfully!'); window.location='admin.php';</script>";
    } else {
        echo "<script>alert('Error deleting slot!');</script>";
    }
}
// -------------------
// HANDLE ADDITIONS
// -------------------
// Add movie
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['add_movie'])) {
    $name = $_POST['name'];
    $description = $_POST['description'];
    $release_date = $_POST['release_date'];
    
    $upload_dir = "uploads/";
    if (!is_dir($upload_dir)) {
        mkdir($upload_dir, 0777, true);
    }
    $target_file = $upload_dir . basename($_FILES["photo"]["name"]);
    $uploadOk = 1;
    $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));

    // Validate image
    $check = getimagesize($_FILES["photo"]["tmp_name"]);
    if ($check === false) {
        echo "<script>alert('File is not an image.');</script>";
        $uploadOk = 0;
    }
    if ($_FILES["photo"]["size"] > 2000000) {
        echo "<script>alert('Sorry, your file is too large.');</script>";
        $uploadOk = 0;
    }
    if (!in_array($imageFileType, ['jpg','jpeg','png','gif'])) {
        echo "<script>alert('Only JPG, JPEG, PNG & GIF files are allowed.');</script>";
        $uploadOk = 0;
    }
    if ($uploadOk == 1) {
        // Create uploads directory if it doesn't exist
        if (!file_exists($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }

        // Generate unique filename to prevent overwriting
        $file_extension = strtolower(pathinfo($_FILES["photo"]["name"], PATHINFO_EXTENSION));
        $unique_filename = uniqid() . '.' . $file_extension;
        $target_file = $upload_dir . $unique_filename;

        if (move_uploaded_file($_FILES["photo"]["tmp_name"], $target_file)) {
            $genre = $_POST['genre'];
    $sql = "INSERT INTO movies (name, description, release_date, photo, genre) VALUES (?, ?, ?, ?, ?)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("sssss", $name, $description, $release_date, $target_file, $genre);

            if ($stmt->execute()) {
                echo "<script>alert('Movie added successfully!'); window.location='admin.php';</script>";
            } else {
                unlink($target_file); // Delete uploaded file if database insert fails
                echo "<script>alert('Error adding movie to database!');</script>";
            }
            $stmt->close();
        } else {
            echo "<script>alert('Error uploading file. Please try again.');</script>";
        }
    }
}
// Handle movie editing
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['edit_movie'])) {
    $movie_id = $_POST['edit_movie_id'];
    $name = $_POST['edit_name'];
    $description = $_POST['edit_description'];
    $release_date = $_POST['edit_release_date'];
    $genre = $_POST['edit_genre'];
    
    // Check if a new image was uploaded
    if (!empty($_FILES['edit_photo']['name'])) {
        $upload_dir = "uploads/";
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }
        
        // Get current photo path to delete later
        $sql = "SELECT photo FROM movies WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $movie_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $old_photo = $result->fetch_assoc()['photo'];
        
        // Process new photo upload
        $file_extension = strtolower(pathinfo($_FILES["edit_photo"]["name"], PATHINFO_EXTENSION));
        $unique_filename = uniqid() . '.' . $file_extension;
        $target_file = $upload_dir . $unique_filename;
        
        if (move_uploaded_file($_FILES["edit_photo"]["tmp_name"], $target_file)) {
            // Update movie with new photo
            $sql = "UPDATE movies SET name = ?, description = ?, release_date = ?, photo = ?, genre = ? WHERE id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("sssssi", $name, $description, $release_date, $target_file, $genre, $movie_id);
            
            if ($stmt->execute()) {
                // Delete old photo if exists
                if (file_exists($old_photo)) {
                    unlink($old_photo);
                }
                echo "<script>alert('Movie updated successfully!'); window.location='admin.php';</script>";
            } else {
                unlink($target_file); // Delete new upload if update fails
                echo "<script>alert('Error updating movie!');</script>";
            }
        } else {
            echo "<script>alert('Error uploading new photo!');</script>";
        }
    } else {
        // Update movie without changing photo
        $sql = "UPDATE movies SET name = ?, description = ?, release_date = ?, genre = ? WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssssi", $name, $description, $release_date, $genre, $movie_id);
        
        if ($stmt->execute()) {
            echo "<script>alert('Movie updated successfully!'); window.location='admin.php';</script>";
        } else {
            echo "<script>alert('Error updating movie!');</script>";
        }
    }
}
// Add movie slot
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['add_slot'])) {
    $movie_id = $_POST['movie_id'];
    $slot_date = $_POST['slot_date'];
    $slot_time = $_POST['slot_time'];
    
    // Validate that slot date is not in the past
    if (strtotime($slot_date) < strtotime(date('Y-m-d'))) {
        echo "<script>alert('Cannot create slots for past dates!');</script>";
        exit;
    }
    
    // Check for overlapping slots
    $sql = "SELECT * FROM movie_slots WHERE slot_date = ? AND (
        TIME(slot_time) BETWEEN TIME(?) - INTERVAL 3 HOUR AND TIME(?) + INTERVAL 3 HOUR
    ) AND movie_id != ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sssi", $slot_date, $slot_time, $slot_time, $movie_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        echo "<script>alert('Cannot create overlapping slots!'); window.location='admin.php' </script>";
        exit;
    }
    $stmt->close();
    
    // By default, mark new slot as available (1)
    $available = 1;
    
    $sql = "INSERT INTO movie_slots (movie_id, slot_date, slot_time, available) VALUES (?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("issi", $movie_id, $slot_date, $slot_time, $available);
    if ($stmt->execute()) {
        echo "<script>alert('Slot added successfully!'); window.location='admin.php';</script>";
    } else {
        echo "<script>alert('Error adding slot!');</script>";
    }
    $stmt->close();
}
// -------------------
// FETCH DATA
// -------------------
// Fetch movies
$sql = "SELECT * FROM movies ORDER BY release_date DESC";
$movies_result = $conn->query($sql);

// Fetch bookings (joined with movies)
$sql = "SELECT b.id AS booking_id, b.user_id, b.movie_id, b.slot_id, b.quantity, b.booking_date, m.name AS movie_name 
        FROM bookings b 
        LEFT JOIN movies m ON b.movie_id = m.id 
        ORDER BY b.booking_date DESC";
$bookings_result = $conn->query($sql);

// Fetch slots (joined with movies to show movie name)
$sql = "SELECT s.id AS slot_id, s.movie_id, s.slot_date, s.slot_time, s.available, m.name AS movie_name 
        FROM movie_slots s 
        LEFT JOIN movies m ON s.movie_id = m.id 
        ORDER BY s.slot_date DESC, s.slot_time ASC";
$slots_result = $conn->query($sql);
// -------------------
// FETCH DATA
// -------------------
// Fetch daily ticket sales
$sql = "SELECT DATE(booking_date) as sale_date, COUNT(*) as tickets_sold, SUM(price) as total_revenue FROM bookings GROUP BY sale_date ORDER BY sale_date DESC";
$sales_result = $conn->query($sql);

?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Admin Panel</title>
  <style>
    /* Reset & Global Styles */
    * { margin: 0; padding: 0; box-sizing: border-box; }
    :root {
      --primary-color: #2c3e50;
      --secondary-color: #3498db;
      --accent-color: #e74c3c;
      --background-color: #ecf0f1;
      --text-color: #2c3e50;
      --card-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
      --transition: all 0.3s ease;
    }
    body { 
      font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
      background-color: var(--background-color);
      color: var(--text-color);
      line-height: 1.6;
    }
    header, footer { 
      background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
      color: #fff;
      text-align: center;
      padding: 20px;
      box-shadow: var(--card-shadow);
    }
    .container { 
      display: flex;
      gap: 20px;
      padding: 20px;
      max-width: 1400px;
      margin: 0 auto;
    }
    .sidebar {
      width: 250px;
      background: var(--primary-color);
      color: #fff;
      height: calc(100vh - 40px);
      padding: 30px;
      border-radius: 12px;
      box-shadow: var(--card-shadow);
      position: sticky;
      top: 20px;
      overflow-y: auto;
      scrollbar-width: thin;
      scrollbar-color: var(--secondary-color) transparent;
    }
    .sidebar::-webkit-scrollbar {
      width: 6px;
    }
    .sidebar::-webkit-scrollbar-track {
      background: transparent;
    }
    .sidebar::-webkit-scrollbar-thumb {
      background-color: var(--secondary-color);
      border-radius: 3px;
    }
    .sidebar a {
      display: block;
      color: #fff;
      text-decoration: none;
      margin-bottom: 20px;
      padding: 15px;
      border-radius: 8px;
      transition: var(--transition);
      font-weight: 500;
    }
    .sidebar a:hover { 
      background: var(--secondary-color);
      transform: translateX(10px);
    }
    .main-content { 
      flex: 1;
      max-width: 1100px;
    }
    .section {
      background: #fff;
      padding: 30px;
      margin-bottom: 30px;
      border-radius: 12px;
      box-shadow: var(--card-shadow);
      transition: var(--transition);
    }
    .section:hover {
      transform: translateY(-5px);
      box-shadow: 0 6px 12px rgba(0, 0, 0, 0.15);
    }
    h2, h3 { 
      text-align: center;
      margin-bottom: 30px;
      color: var(--primary-color);
      font-weight: 600;
    }
    .form-group { 
      margin-bottom: 25px;
    }
    label { 
      font-weight: 500;
      display: block;
      margin-bottom: 8px;
      color: var(--primary-color);
    }
    input, textarea, select {
      width: 100%;
      padding: 12px;
      margin-top: 8px;
      border: 2px solid #e1e1e1;
      border-radius: 8px;
      transition: var(--transition);
      font-size: 16px;
    }
    input:focus, textarea:focus, select:focus {
      border-color: var(--secondary-color);
      outline: none;
      box-shadow: 0 0 0 3px rgba(52, 152, 219, 0.2);
    }
    .btn, .delete-btn {
      background-color: var(--secondary-color);
      color: white;
      padding: 12px 20px;
      border: none;
      border-radius: 8px;
      cursor: pointer;
      text-decoration: none;
      font-weight: 500;
      transition: var(--transition);
      display: inline-block;
    }
    .btn:hover { 
      background-color: #2980b9;
      transform: translateY(-2px);
    }
    .delete-btn {
      background-color: var(--accent-color);
      padding: 8px 16px;
      font-size: 14px;
    }
    .delete-btn:hover {
      background-color: #c0392b;
    }
    table { 
      width: 100%;
      border-collapse: separate;
      border-spacing: 0;
      margin-top: 20px;
      background: #fff;
      border-radius: 12px;
      overflow: hidden;
      box-shadow: var(--card-shadow);
    }
    th, td { 
      padding: 15px;
      text-align: center;
      border: 1px solid #e1e1e1;
    }
    th { 
      background-color: var(--primary-color);
      color: white;
      font-weight: 500;
      text-transform: uppercase;
      font-size: 14px;
      letter-spacing: 1px;
    }
    tr:nth-child(even) { 
      background-color: #f8f9fa;
    }
    tr:hover {
      background-color: #f1f4f6;
    }
    .logout {
      position: absolute;
      right: 30px;
      top: 30px;
      color: white;
      text-decoration: none;
      font-weight: 500;
      padding: 10px 20px;
      border-radius: 8px;
      background: rgba(255, 255, 255, 0.1);
      transition: var(--transition);
    }
    .logout:hover {
      background: rgba(255, 255, 255, 0.2);
    }
    .movie-card {
      display: flex;
      align-items: center;
      margin-bottom: 20px;
      padding: 20px;
      border-radius: 12px;
      background: #fff;
      box-shadow: var(--card-shadow);
      transition: var(--transition);
    }
    .movie-card:hover {
      transform: translateY(-5px);
      box-shadow: 0 6px 12px rgba(0, 0, 0, 0.15);
    }
    .movie-poster {
      width: 120px;
      height: 180px;
      object-fit: cover;
      border-radius: 8px;
      margin-right: 20px;
    }
    .movie-info {
      flex: 1;
    }
    .movie-info h3 {
      text-align: left;
      margin-bottom: 10px;
      color: var(--primary-color);
    }
    .movie-info p {
      margin-bottom: 8px;
      color: #666;
    }
    @media (max-width: 768px) {
      .container {
        flex-direction: column;
      }
      .sidebar {
        width: 100%;
        min-height: auto;
      }
      .movie-card {
        flex-direction: column;
        text-align: center;
      }
      .movie-poster {
        margin: 0 0 20px 0;
      }
      .movie-info h3 {
        text-align: center;
      }
    }
      /* Modal styles */
    .modal {
        display: none;
        position: fixed;
        z-index: 1000;
        left: 0;
        top: 0;
        width: 100%;
        height: 100%;
        background-color: rgba(0,0,0,0.5);
    }
    .modal-content {
        background-color: #fff;
        margin: 5% auto;
        padding: 20px;
        border-radius: 12px;
        width: 80%;
        max-width: 600px;
        position: relative;
        box-shadow: 0 4px 6px rgba(0,0,0,0.1);
    }
    .close-modal {
        position: absolute;
        right: 20px;
        top: 20px;
        font-size: 24px;
        cursor: pointer;
        color: var(--text-color);
    }
    .close-modal:hover {
        color: var(--accent-color);
    }
    .layout-controls {
            display: flex;
            gap: 20px;
            align-items: center;
            margin-bottom: 20px;
          }
          .seat-layout-container {
            max-width: 800px;
            margin: 30px auto;
            text-align: center;
          }
          .seat.selected {
            border: 2px solid #4CAF50;
            box-shadow: 0 0 5px rgba(76, 175, 80, 0.5);
          }
          .seat-grid {
            display: flex;
            flex-direction: column;
            gap: 10px;
            align-items: center;
            margin-top: 30px;
          }
          .seat-row {
            display: flex;
            align-items: center;
            gap: 10px;
          }
          .seat {
            width: 40px;
            height: 40px;
            border: 2px solid #ddd;
            border-radius: 6px;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: all 0.3s ease;
          }
          .seat.regular {
            background-color: #e3f2fd;
            border-color: #90caf9;
          }
          .seat.vip {
            background-color: #fff3e0;
            border-color: #ffb74d;
          }
          .seat.maintenance {
            background-color: #ffebee;
            border-color: #ef9a9a;
            cursor: not-allowed;
          }
          .row-label {
            width: 30px;
            font-weight: bold;
            margin-right: 10px;
          }
</style>
<script>
          function toggleSeatType(seatElement) {
  const seatId = seatElement.dataset.seatId;
  const currentType = parseInt(seatElement.dataset.type);
  const newType = document.getElementById('seat-type').value;
  
  if (seatElement.classList.contains('maintenance')) return;
  
  fetch('update_seat_type.php', {
    method: 'POST',
    headers: {
      'Content-Type': 'application/x-www-form-urlencoded',
    },
    body: `seat_id=${seatId}&type_id=${newType}`
  })
  .then(response => response.json())
  .then(data => {
    if (data.success) {
      seatElement.dataset.type = newType;
      seatElement.className = `seat ${newType == 2 ? 'vip' : 'regular'}`;
    }
  });
}
          
          let selectedSeats = [];

          function updateSeatType() {
            const selectedType = document.getElementById('seat-type').value;
            document.querySelectorAll('.seat:not(.maintenance)').forEach(seat => {
              seat.style.cursor = 'pointer';
            });
          }

          function toggleSeatSelection(seatElement) {
            if (seatElement.classList.contains('maintenance')) return;
            seatElement.classList.toggle('selected');
            const row = seatElement.dataset.row;
            const number = seatElement.dataset.number;
            const seatKey = `${row}-${number}`;
            
            if (seatElement.classList.contains('selected')) {
              selectedSeats.push({row: row, number: parseInt(number)});
            } else {
              selectedSeats = selectedSeats.filter(s => `${s.row}-${s.number}` !== seatKey);
            }
          }

          function updateLayout() {
            const rows = parseInt(document.getElementById('rows').value);
            const seatsPerRow = parseInt(document.getElementById('seats-per-row').value);
            
            if (rows < 1 || rows > 26 || seatsPerRow < 1 || seatsPerRow > 20) {
              alert('Invalid layout configuration');
              return;
            }
            
            fetch('manage_layout.php', {
              method: 'POST',
              headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
              },
              body: `action=update_layout&rows=${rows}&seats_per_row=${seatsPerRow}`
            })
            .then(response => response.json())
            .then(data => {
              if (data.success) {
                location.reload();
              } else {
                alert('Error updating layout: ' + (data.error || 'Unknown error'));
              }
            });
          }

          function applyToSelected() {
            if (selectedSeats.length === 0) {
              alert('Please select seats first');
              return;
            }
            
            const newType = document.getElementById('seat-type').value;
            
            fetch('manage_layout.php', {
              method: 'POST',
              headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
              },
              body: `action=update_multiple_seats&seats=${JSON.stringify(selectedSeats)}&type_id=${newType}`
            })
            .then(response => response.json())
            .then(data => {
              if (data.success) {
                location.reload();
              } else {
                alert('Error updating seats: ' + (data.error || 'Unknown error'));
              }
            });
          }

          // Add click event listeners to seats for multi-select
          document.querySelectorAll('.seat').forEach(seat => {
            seat.addEventListener('click', () => toggleSeatSelection(seat));
          });
        </script>
</head>
<body>
    <!-- Edit Movie Modal -->
    <div id="editMovieModal" class="modal">
        <div class="modal-content">
            <span class="close-modal" onclick="closeEditModal()">&times;</span>
            <h2>Edit Movie</h2>
            <form id="editMovieForm" method="POST" enctype="multipart/form-data">
                <input type="hidden" name="edit_movie_id" id="edit_movie_id">
                <div class="form-group">
                    <label>Movie Name:</label>
                    <input type="text" name="edit_name" id="edit_name" required>
                </div>
                <div class="form-group">
                    <label>Description:</label>
                    <textarea name="edit_description" id="edit_description" required></textarea>
                </div>
                <div class="form-group">
                    <label>Release Date:</label>
                    <input type="date" name="edit_release_date" id="edit_release_date" required>
                </div>
                <div class="form-group">
                    <label>Movie Poster:</label>
                    <input type="file" name="edit_photo" accept="image/*">
                    <small>Leave empty to keep current poster</small>
                </div>
                <div class="form-group">
                    <label>Genre:</label>
                    <select name="edit_genre" id="edit_genre" required>
                        <option value="action">Action</option>
                        <option value="comedy">Comedy</option>
                        <option value="drama">Drama</option>
                        <option value="horror">Horror</option>
                        <option value="romance">Romance</option>
                        <option value="sci-fi">Sci-Fi</option>
                        <option value="thriller">Thriller</option>
                        <option value="documentary">Documentary</option>
                    </select>
                </div>
                <button type="submit" class="btn" name="edit_movie">Update Movie</button>
            </form>
        </div>
    </div>
    <script>
        function openEditModal(id, name, description, releaseDate, genre) {
            document.getElementById('edit_movie_id').value = id;
            document.getElementById('edit_name').value = name;
            document.getElementById('edit_description').value = description;
            document.getElementById('edit_release_date').value = releaseDate;
            document.getElementById('edit_genre').value = genre;
            document.getElementById('editMovieModal').style.display = 'block';
        }

        function closeEditModal() {
            document.getElementById('editMovieModal').style.display = 'none';
        }

        window.onclick = function(event) {
            if (event.target == document.getElementById('editMovieModal')) {
                closeEditModal();
            }
        }
    </script>
  <header>
    <h1>Admin Panel</h1>
    <a href="logout.php" class="logout">Logout</a>
  </header>
  <div class="container">
    <!-- Sidebar Navigation -->
    <nav class="sidebar">
      <a href="#add-movie">Add Movie</a>
      <a href="#view-movies">View Movies</a>
      <a href="#manage-slots">Manage Slots</a>
      <a href="#manage-seats">Manage Seats</a>
      <a href="#view-bookings">View Bookings</a>
    </nav>
    <!-- Main Content Area -->
    <div class="main-content">
      <!-- Add Movie Section -->
      <section id="add-movie" class="section">
        <h2>Add Movie</h2>
        <form method="POST" enctype="multipart/form-data">
          <div class="form-group">
            <label>Movie Name:</label>
            <input type="text" name="name" required>
          </div>
          <div class="form-group">
            <label>Description:</label>
            <textarea name="description" required></textarea>
          </div>
          <div class="form-group">
            <label>Release Date:</label>
            <input type="date" name="release_date" required>
          </div>
          <div class="form-group">
            <label>Movie Poster:</label>
            <input type="file" name="photo" accept="image/*" required>
          </div>
          <div class="form-group">
            <label>Genre:</label>
            <select name="genre" required>
              <option value="">Select a genre</option>
              <option value="action">Action</option>
              <option value="comedy">Comedy</option>
              <option value="drama">Drama</option>
              <option value="horror">Horror</option>
              <option value="romance">Romance</option>
              <option value="sci-fi">Sci-Fi</option>
              <option value="thriller">Thriller</option>
              <option value="documentary">Documentary</option>
            </select>
          </div>
          <button type="submit" class="btn" name="add_movie">Add Movie</button>
        </form>
      </section>
      <!-- View Movies Section -->
      <section id="view-movies" class="section">
        <h2>Movie Listings</h2>
        <?php
          if ($movies_result && $movies_result->num_rows > 0) {
              while ($row = $movies_result->fetch_assoc()) {
                  echo "<div class='movie-card' data-movie-id='" . $row['id'] . "' style='display:flex; align-items:center; margin-bottom:15px; padding:10px; border:1px solid #ddd; border-radius:4px;'>";
                  echo "<img src='" . $row['photo'] . "' alt='Movie Poster' style='width:80px; height:120px; object-fit:cover; border-radius:4px;'>";
                  echo "<div style='margin-left:15px; flex:1;'>";
                  echo "<h3 style='margin-bottom:5px;'>" . $row['name'] . "</h3>";
                  echo "<p style='margin-bottom:5px;'>" . $row['description'] . "</p>";
                  echo "<p style='margin-bottom:5px;'>Release Date: " . $row['release_date'] . "</p>";
                  echo "<p style='margin-bottom:5px;'>Genre: " . $row['genre'] . "</p>";
                  echo "</div>";
                  echo "<div style='display:flex; gap:10px;'>";                  echo "<button onclick='openEditModal(" . $row['id'] . ", \"" . addslashes($row['name']) . "\", \"" . addslashes($row['description']) . "\", \"" . $row['release_date'] . "\", \"" . $row['genre'] . "\")' class='btn' style='padding:5px 10px;'>Edit</button>";                  echo "<a href='admin.php?delete_movie=" . $row['id'] . "' class='delete-btn' style='padding:5px 10px;'>Delete</a>";                  echo "</div>";
                  echo "</div>";
              }
          } else {
              echo "<p>No movies available.</p>";
          }
        ?>
      </section>
      <!-- Manage Slots Section -->
      <section id="manage-slots" class="section">
        <h2>Manage Movie Slots</h2>
        <!-- Form to add a new slot -->
        <form method="POST">
          <div class="form-group">
            <label>Select Movie:</label>
            <select name="movie_id" required>
              <option value="">Select a movie</option>
              <?php
                // Use movies_result again by querying or storing movies in an array
                $sql = "SELECT id, name FROM movies ORDER BY name ASC";
                $movies_for_slots = $conn->query($sql);
                if ($movies_for_slots->num_rows > 0) {
                    while ($movie = $movies_for_slots->fetch_assoc()) {
                        echo "<option value='" . $movie['id'] . "'>" . $movie['name'] . "</option>";
                    }
                }
              ?>
            </select>
          </div>
          <div class="form-group">
            <label>Slot Date:</label>
            <input type="date" name="slot_date" required min="<?php echo date('Y-m-d'); ?>">
          </div>
          <div class="form-group">
            <label>Slot Time:</label>
            <input type="time" name="slot_time" required>
          </div>
          <button type="submit" class="btn" name="add_slot">Add Slot</button>
        </form>
        <!-- Display existing slots -->
        <h3 style="margin-top:20px;">Current Slots</h3>
        <?php
          if ($slots_result && $slots_result->num_rows > 0) {
              echo "<table>";
              echo "<tr>
                      <th>Slot ID</th>
                      <th>Movie</th>
                      <th>Date</th>
                      <th>Time</th>
                      <th>Available</th>
                      <th>Action</th>
                    </tr>";
              while ($slot = $slots_result->fetch_assoc()) {
                  echo "<tr>";
                  echo "<td>" . $slot['slot_id'] . "</td>";
                  echo "<td>" . ($slot['movie_name'] ?? 'Unknown') . "</td>";
                  echo "<td>" . $slot['slot_date'] . "</td>";
                  echo "<td>" . $slot['slot_time'] . "</td>";
                  echo "<td>" . ($slot['available'] ? 'Yes' : 'No') . "</td>";
                  echo "<td><a href='admin.php?delete_slot=" . $slot['slot_id'] . "' class='delete-btn'>Delete</a></td>";
                  echo "</tr>";
              }
              echo "</table>";
          } else {
              echo "<p>No slots available.</p>";
          }
        ?>
      </section>
      <!-- View Bookings Section -->
      <section id="view-bookings" class="section">
        <h2>Booking Listings</h2>
        <?php
          if ($bookings_result && $bookings_result->num_rows > 0) {
              echo "<table>";
              echo "<tr>
                      <th>Booking ID</th>
                      <th>User ID</th>
                      <th>Movie</th>
                      <th>Slot ID</th>
                      <th>Quantity</th>
                      <th>Booking Date</th>
                      <th>Action</th>
                    </tr>";
              while ($booking = $bookings_result->fetch_assoc()) {
                  echo "<tr>";
                  echo "<td>" . $booking['booking_id'] . "</td>";
                  echo "<td>" . $booking['user_id'] . "</td>";
                  echo "<td>" . ($booking['movie_name'] ?? 'Unknown') . "</td>";
                  echo "<td>" . $booking['slot_id'] . "</td>";
                  echo "<td>" . $booking['quantity'] . "</td>";
                  echo "<td>" . $booking['booking_date'] . "</td>";
                  echo "<td><a href='admin.php?delete_booking=" . $booking['booking_id'] . "' class='delete-btn'>Delete</a></td>";
                  echo "</tr>";
              }
              echo "</table>";
          } else {
              echo "<p>No bookings available.</p>";
          }
        ?>
      </section>
      <!-- Manage Seats Section -->
      <section id="manage-seats" class="section">
        <h2>Manage Seat Layout</h2>
        <div class="layout-controls">
          <div class="form-group">
            <label>Number of Rows:</label>
            <input type="number" id="rows" value="5" min="1" max="26">
          </div>
          <div class="form-group">
            <label>Seats per Row:</label>
            <input type="number" id="seats-per-row" value="8" min="1" max="20">
          </div>
          <button onclick="updateLayout()" class="btn btn-primary">Update Layout</button>
        </div>
        <div class="form-group">
          <label>Seat Type:</label>
          <select id="seat-type" onchange="updateSeatType()">
            <option value="1">Regular ($10)</option>
            <option value="2">VIP ($15)</option>
          </select>
          <button onclick="applyToSelected()" class="btn btn-secondary">Apply to Selected</button>
        </div>
        <div class="seat-layout-container">
          <div class="screen">SCREEN</div>
          <div class="seat-grid">
            <?php
              $sql = "SELECT ts.*, st.name as type_name, st.price 
                      FROM theater_seats ts 
                      LEFT JOIN seat_types st ON ts.seat_type_id = st.id 
                      ORDER BY ts.row_name, ts.seat_number";
              $seats_result = $conn->query($sql);
              
              $current_row = '';
              while ($seat = $seats_result->fetch_assoc()) {
                if ($current_row != $seat['row_name']) {
                  if ($current_row != '') echo '</div>';
                  $current_row = $seat['row_name'];
                  echo '<div class="seat-row"><span class="row-label">' . $current_row . '</span>';
                }
                $seat_class = $seat['status'] == 'maintenance' ? 'maintenance' : 
                             ($seat['seat_type_id'] == 2 ? 'vip' : 'regular');
                echo '<div class="seat ' . $seat_class . '" 
                           data-seat-id="' . $seat['id'] . '" 
                           data-type="' . $seat['seat_type_id'] . '" 
                           onclick="toggleSeatType(this)">
                      <span>' . $seat['seat_number'] . '</span>
                    </div>';
              }
              if ($current_row != '') echo '</div>';
            ?>
          </div>
        </div>
        
      </section>
      <section id="daily-sales" class="section">
    <h2>Daily Ticket Sales</h2>
    <table>
        <thead>
            <tr>
                <th>Date</th>
                <th>Tickets Sold</th>
                <th>Total Revenue</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($row = $sales_result->fetch_assoc()): ?>
                <tr>
                    <td><?php echo $row['sale_date']; ?></td>
                    <td><?php echo $row['tickets_sold']; ?></td>
                    <td><?php echo $row['total_revenue']; ?></td>
                </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
</section>
    </div>
  </div>
  <footer>
    <p>&copy; <?php echo date("Y"); ?> Movie ticketing and review. All rights reserved.</p>
  </footer>
</body>
</html>


