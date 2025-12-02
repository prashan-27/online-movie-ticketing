<?php
session_start();
include 'db.php';

// Redirect to login if user is not logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Fetch user's bookings with movie and slot details
$sql = "SELECT b.id as booking_id, b.quantity, b.booking_date, 
               m.id as movie_id, m.name as movie_name, m.photo, m.genre,
               ms.slot_date, ms.slot_time,
               (SELECT GROUP_CONCAT(seat_id) FROM seat_bookings WHERE booking_id = b.id) as seat_numbers
        FROM bookings b
        JOIN movies m ON b.movie_id = m.id
        JOIN movie_slots ms ON b.slot_id = ms.id
        WHERE b.user_id = ?
        ORDER BY b.booking_date DESC";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Bookings</title>
    <style>
        header {
      background: linear-gradient(135deg, #74ABE2, #5563DE);
      color: #fff;
      padding: 1rem 2rem;
      display: flex;
      justify-content: space-between;
      align-items: center;
    }

    .hamburger {
      display: none;
      flex-direction: column;
      cursor: pointer;
    }

    .hamburger div {
      width: 25px;
      height: 3px;
      background-color: #fff;
      margin: 4px 0;
    }

    nav {
      display: flex;
      gap: 1rem;
    }

    nav a {
      color: white;
      text-decoration: none;
      font-size: 1rem;
    }
    .logo {
      font-size: 1.5rem;
      font-weight: bold;
    }
    footer {
      background: linear-gradient(135deg, #74ABE2, #5563DE);
      color: #fff;
      padding: 3rem 2rem;
      margin-top: 4rem;
    }

    .footer-content {
      max-width: 1200px;
      margin: 0 auto;
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
      gap: 2rem;
    }

    .footer-section {
      padding: 1rem;
    }

    .footer-section h3 {
      color: #fff;
      font-size: 1.2rem;
      margin-bottom: 1.5rem;
      position: relative;
    }

    .footer-section h3::after {
      content: '';
      position: absolute;
      left: 0;
      bottom: -0.5rem;
      width: 50px;
      height: 2px;
      background-color: #3498db;
    }

    .footer-section ul {
      list-style: none;
      padding: 0;
      margin: 0;
    }

    .footer-section ul li {
      margin-bottom: 0.8rem;
    }

    .footer-section ul li a {
      color: #ecf0f1;
      text-decoration: none;
      transition: color 0.3s ease;
      display: inline-block;
    }

    .footer-section ul li a:hover {
      color: #3498db;
      transform: translateX(5px);
    }

    .footer-section ul li i {
      margin-right: 0.5rem;
      color: #3498db;
    }

    .footer-content {
      max-width: 1200px;
      margin: 0 auto;
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
      gap: 2rem;
    }

    .footer-section {
      padding: 1rem;
    }

    .footer-section h3 {
      color: #fff;
      font-size: 1.2rem;
      margin-bottom: 1.5rem;
      position: relative;
    }

    .footer-section h3::after {
      content: '';
      position: absolute;
      left: 0;
      bottom: -0.5rem;
      width: 50px;
      height: 2px;
      background-color: #3498db;
    }

    .footer-section ul {
      list-style: none;
      padding: 0;
      margin: 0;
    }

    .footer-section ul li {
      margin-bottom: 0.8rem;
    }

    .footer-section ul li a {
      color: #ecf0f1;
      text-decoration: none;
      transition: color 0.3s ease;
      display: inline-block;
    }

    .footer-section ul li a:hover {
      color: #3498db;
      transform: translateX(5px);
    }

    .footer-section ul li i {
      margin-right: 0.5rem;
      color: #3498db;
    }
        body {
      font-family: 'Segoe UI', Arial, sans-serif;
      margin: 0;
      padding: 0;
      background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
      min-height: 100vh;
    }
        .container {
            max-width: 1200px;
            margin: 0 auto;
        }
        h1 {
            color: #333;
            text-align: center;
            margin-bottom: 30px;
        }
        .booking-card {
            background: white;
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 20px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            display: flex;
            align-items: center;
            gap: 20px;
        }
        .movie-poster {
            width: 100px;
            height: 150px;
            object-fit: cover;
            border-radius: 4px;
        }
        .booking-details {
            flex: 1;
        }
        .movie-title {
            font-size: 1.4em;
            color: #333;
            margin: 0 0 10px 0;
        }
        .booking-info {
            color: #666;
            margin: 5px 0;
        }
        .back-button {
            display: inline-block;
            padding: 10px 20px;
            background-color: #007bff;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            margin-bottom: 20px;
        }
        .back-button:hover {
            background-color: #0056b3;
        }
        .no-bookings {
            text-align: center;
            padding: 40px;
            background: white;
            border-radius: 8px;
            margin-top: 20px;
        }
        .edit-button {
            background-color: #ffc107;
            color: black;
            border: none;
            padding: 8px 16px;
            border-radius: 4px;
            cursor: pointer;
            margin-top: 10px;
         }
         .edit-button:hover {
            background-color: #e0a800;
        }
          .edit-form textarea {
              width: 100%;
              min-height: 100px;
              padding: 10px;
              border: 1px solid #ddd;
              border-radius: 4px;
              resize: vertical;
              margin-top: 10px;
                            }
             .save-button {
                  background-color: #28a745;
                   color: white;
                   border: none;
                   padding: 8px 16px;
                   border-radius: 4px;
                   cursor: pointer;
                   margin-right: 10px;
                  }
                  .save-button:hover {
                    background-color: #218838;
                  }
                  .cancel-button {
                    background-color: #dc3545;
                    color: white;
                    border: none;
                    padding: 8px 16px;
                    border-radius: 4px;
                    cursor: pointer;
                  }
                  .cancel-button:hover {
                    background-color: #c82333;
                  }
                  .comment-section {
                    margin-top: 15px;
                    padding: 15px;
                    background: #f8f9fa;
                    border-radius: 4px;
                  }
                  .comment-section textarea {
                    width: 100%;
                    min-height: 100px;
                    padding: 10px;
                    border: 1px solid #ddd;
                    border-radius: 4px;
                    resize: vertical;
                  }
                  .char-count {
                    text-align: right;
                    font-size: 0.8em;
                    color: #666;
                    margin-top: 5px;
                  }
                  .comment-button {
                    background-color: #28a745;
                    color: white;
                    border: none;
                    padding: 8px 16px;
                    border-radius: 4px;
                    cursor: pointer;
                    margin-top: 10px;
                  }
                  .comment-button:hover {
                    background-color: #218838;
                    }
</style>
<script>
function showEditForm(bookingId) {
            document.getElementById('editForm-' + bookingId).style.display = 'block';
      }
 function hideEditForm(bookingId) {
            document.getElementById('editForm-' + bookingId).style.display = 'none';
      }
 function updateCharCount(textarea) {
            const charCount = document.getElementById('charCount');
            charCount.textContent = textarea.value.length;
      }
</script>
</head>
<body>
<header>
    <a href="base2.php" class="logo" style="color: white; margin-right: 10px; text-decoration: none;">Movie Ticketing</a>
    <div class="hamburger" onclick="toggleMenu()">
      <div></div>
      <div></div>
      <div></div>
    </div>
    <nav id="menu">
      <a href="#contact">Contact</a> <!-- Link to the contact section in footer -->
      <?php if (isset($_SESSION['user_id'])): ?>
        <span style="color: white; margin-right: 10px;">Hello, <?php echo htmlspecialchars($_SESSION['user_email']); ?></span>
        
        <a href="logout.php">Logout</a>
      <?php else: ?>
        <a href="login.php">Login</a>
      <?php endif; ?>
    </nav>
  </header>
    <div class="container">
        <h1>My Bookings</h1>
        
        <?php if ($result->num_rows > 0): ?>
            <?php while ($booking = $result->fetch_assoc()): ?>
                <div class="booking-card">
                    <img src="<?php echo htmlspecialchars($booking['photo']); ?>" alt="<?php echo htmlspecialchars($booking['movie_name']); ?>" class="movie-poster">
                    <div class="booking-details">
                        <h2 class="movie-title"><?php echo htmlspecialchars($booking['movie_name']); ?></h2>
                        <p class="booking-info"><strong>Genre:</strong> <?php echo htmlspecialchars($booking['genre']); ?></p>
                        <p class="booking-info"><strong>Show Date:</strong> <?php echo date('F d, Y', strtotime($booking['slot_date'])); ?></p>
                        <p class="booking-info"><strong>Show Time:</strong> <?php echo date('h:i A', strtotime($booking['slot_time'])); ?></p>
                        <p class="booking-info"><strong>Number of Tickets:</strong> <?php echo $booking['quantity']; ?></p>
                        <?php if ($booking['seat_numbers']): ?>
                            <p class="booking-info"><strong>Seat Numbers:</strong> <?php echo htmlspecialchars($booking['seat_numbers']); ?></p>
                        <?php endif; ?>
                        <p class="booking-info"><strong>Booking Date:</strong> <?php echo date('F d, Y h:i A', strtotime($booking['booking_date'])); ?></p>
                        <div class="comment-section">
                            <?php
                            $check_comment_sql = "SELECT id, comment FROM movie_comments WHERE booking_id = ?";
                            $check_stmt = $conn->prepare($check_comment_sql);
                            $check_stmt->bind_param("i", $booking['booking_id']);
                            $check_stmt->execute();
                            $check_result = $check_stmt->get_result();
                            $comment_data = $check_result->fetch_assoc();
                            $existing_comment = $comment_data ? $comment_data['comment'] : null;
                            $check_stmt->close();

                            if (empty($existing_comment)): ?>
                                <form method="POST" action="add_comment.php">
                                    <input type="hidden" name="booking_id" value="<?php echo $booking['booking_id']; ?>">
                                    <textarea name="comment" placeholder="Write your review..." maxlength="500" oninput="updateCharCount(this)" required></textarea>
                                    <div class="char-count"><span id="charCount">0</span>/500 characters</div>
                                    <button type="submit" class="comment-button">Submit Review</button>
                                </form>
                            <?php else: ?>
                                <div class="existing-comment">
                                    <h4>Your Review:</h4>
                                    <p><?php echo htmlspecialchars($existing_comment); ?></p>
                                    <button type="button" class="edit-button" onclick="showEditForm(<?php echo $booking['booking_id']; ?>)">Edit Review</button>
                                    <div id="editForm-<?php echo $booking['booking_id']; ?>" class="edit-form" style="display: none;">
                                        <form method="POST" action="edit_comment.php">
                                            <input type="hidden" name="comment_id" value="<?php echo isset($comment_data['id']) ? $comment_data['id'] : ''; ?>" />
                                            <textarea name="comment"><?php echo htmlspecialchars($existing_comment); ?></textarea>
                                            <button type="submit" class="save-button">Save Changes</button>
                                            <button type="button" class="cancel-button" onclick="hideEditForm(<?php echo $booking['booking_id']; ?>)">Cancel</button>
                                        </form>
                                    </div>
                                </div>
                            <?php endif; ?>
                        </div>
                        <div class="rating-section">
                            <?php
                            $rating_sql = "SELECT id, rating FROM ratings WHERE user_id = ? AND movie_id = ?";
                            $rating_stmt = $conn->prepare($rating_sql);
                            $rating_stmt->bind_param("ii", $user_id, $booking['movie_id']);
                            $rating_stmt->execute();
                            $rating_result = $rating_stmt->get_result();
                            $existing_rating = $rating_result->fetch_assoc();
                            $rating_stmt->close();

                            if ($existing_rating) { ?>
                                <form method="POST" action="edit_rating.php" onsubmit="return confirm('Are you sure you want to update this rating?');">
                                    <input type="hidden" name="rating_id" value="<?php echo $existing_rating['id']; ?>">
                                    <label for="rating">Your rating:</label>
                                    <select name="rating" required>
                                        <?php for($i = 1; $i <= 5; $i++): ?>
                                            <option value="<?php echo $i; ?>" <?php echo ($existing_rating['rating'] == $i) ? 'selected' : ''; ?>>
                                                <?php echo $i; ?>
                                            </option>
                                        <?php endfor; ?>
                                    </select>
                                    <button type="submit" class="rating-button">Update Rating</button>
                                </form>
                            <?php } else { ?>
                                <form method="POST" action="add_rating.php" onsubmit="return confirm('Are you sure you want to submit this rating?');">
                                    <input type="hidden" name="booking_id" value="<?php echo $booking['booking_id']; ?>">
                                    <input type="hidden" name="movie_id" value="<?php echo $booking['movie_id']; ?>">
                                    <label for="rating">Rate this movie:</label>
                                    <select name="rating" required>
                                        <?php for($i = 1; $i <= 5; $i++): ?>
                                            <option value="<?php echo $i; ?>"><?php echo $i; ?></option>
                                        <?php endfor; ?>
                                    </select>
                                    <button type="submit" class="rating-button">Submit Rating</button>
                                </form>
                            <?php } ?>
                        </div>
                        <div class="booking-details">
                            <form method="POST" action="download_ticket.php">
                                <input type="hidden" name="booking_id" value="<?php echo $booking['booking_id']; ?>">
                                <button type="submit" class="download-button">Download Ticket</button>
                            </form>
                        </div>
                    </div>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <div class="no-bookings">
                <h2>No bookings found</h2>
                <p>You haven't made any bookings yet. <a href="base2.php">Browse movies</a> to make your first booking!</p>
            </div>
        <?php endif; ?>
    </div>
    <footer>
    <div class="footer-content">
      <div class="footer-section">
        <h3>Quick Links</h3>
        <ul>
          <li><a href="index.php"><i class="fas fa-home"></i> Home</a></li>
          <li><a href="#movies"><i class="fas fa-film"></i> Movies</a></li>
          <li><a href="my_bookings.php"><i class="fas fa-ticket-alt"></i> My Bookings</a></li>
          <li><a href="#contact"><i class="fas fa-envelope"></i> Contact Us</a></li>
        </ul>
      </div>
      
      <div class="footer-section">
        <h3>Movie Categories</h3>
        <ul>
          <li><a href="#" data-genre="action"><i class="fas fa-running"></i> Action</a></li>
          <li><a href="#" data-genre="comedy"><i class="fas fa-laugh"></i> Comedy</a></li>
          <li><a href="#" data-genre="drama"><i class="fas fa-theater-masks"></i> Drama</a></li>
          <li><a href="#" data-genre="horror"><i class="fas fa-ghost"></i> Horror</a></li>
        </ul>
      </div>
      
      <div class="footer-section" id="contact">
        <h3>Contact Info</h3>
        <ul>
          <li><i class="fas fa-envelope"></i> contact@movietickets.com</li>
          <li><i class="fas fa-phone"></i> +1 234 567 890</li>
          <li><i class="fas fa-map-marker-alt"></i> 123 Movie Street, Cinema City</li>
        </ul>
        <div class="social-icons">
          <a href="#" title="Facebook"><i class="fab fa-facebook"></i></a>
          <a href="#" title="Twitter"><i class="fab fa-twitter"></i></a>
          <a href="#" title="Instagram"><i class="fab fa-instagram"></i></a>
          <a href="#" title="YouTube"><i class="fab fa-youtube"></i></a>
        </div>
      </div>
    </div>   
  </div>
</footer>
</body>
</html>
