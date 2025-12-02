<?php
session_start();
include 'db.php';

// Redirect to login if user is not logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Fetch user's booking history with movie genres
$sql = "SELECT m.genre FROM bookings b
        JOIN movies m ON b.movie_id = m.id
        WHERE b.user_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

// Count genre occurrences
$genre_counts = [];
while ($row = $result->fetch_assoc()) {
    $genres = explode(',', $row['genre']);
    foreach ($genres as $genre) {
        $genre = trim($genre);
        if (!isset($genre_counts[$genre])) {
            $genre_counts[$genre] = 0;
        }
        $genre_counts[$genre]++;
    }
}

// Sort genres by frequency
arsort($genre_counts);
$top_genres = array_keys(array_slice($genre_counts, 0, 3, true));

// Fetch recommended movies based on top genres
$recommendations = [];
if (!empty($top_genres)) {
    $genre_list = implode(",", array_map(function($genre) use ($conn) {
        return "'" . $conn->real_escape_string($genre) . "'";
    }, $top_genres));

    $sql = "SELECT * FROM movies WHERE genre IN ($genre_list) ORDER BY release_date DESC LIMIT 10";
    $stmt = $conn->prepare($sql);
    $stmt->execute();
    $stmt->execute();
    $result = $stmt->get_result();

    while ($row = $result->fetch_assoc()) {
        $recommendations[] = $row;
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Movie Recommendations</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
  <style>
    body {
      font-family: 'Segoe UI', Arial, sans-serif;
      margin: 0;
      padding: 0;
      background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
      min-height: 100vh;
    }

    header {
      background: linear-gradient(135deg, #74ABE2, #5563DE);
      color: #fff;
      padding: 1rem 2rem;
      display: flex;
      justify-content: space-between;
      align-items: center;
    }

    .logo a {
      text-decoration: none;
      color: #fff;
      font-size: 28px;
      font-weight: bold;
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

    .booking-container {
      max-width: 1200px;
      margin: 2rem auto;
      padding: 0 2rem;
    }

    .movie-details {
      display: flex;
      align-items: flex-start;
      background-color: rgba(255, 255, 255, 0.95);
      margin-bottom: 2rem;
      padding: 2rem;
      border-radius: 12px;
      box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
      transition: all 0.4s cubic-bezier(0.165, 0.84, 0.44, 1);
      border: 1px solid rgba(255, 255, 255, 0.1);
    }

    .movie-details:hover {
      transform: translateY(-8px);
      box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
      background-color: rgba(255, 255, 255, 1);
    }

    .movie-details img {
      width: 200px;
      height: 300px;
      object-fit: cover;
      border-radius: 8px;
      margin-right: 2rem;
      box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
      transition: transform 0.3s ease;
    }

    .movie-details h3 {
      font-size: 1.8rem;
      color: #333;
      margin: 0 0 0.5rem 0;
    }

    .movie-details p {
      color: #666;
      line-height: 1.6;
      margin: 0.5rem 0;
    }

    .btn {
      align-self: flex-start;
      background: linear-gradient(135deg, #007BFF, #0056b3);
      color: white;
      padding: 1rem 2rem;
      border-radius: 6px;
      text-decoration: none;
      transition: all 0.3s ease;
      margin-top: 1.5rem;
      font-weight: 600;
      letter-spacing: 0.5px;
      box-shadow: 0 4px 15px rgba(0, 123, 255, 0.2);
    }

    .btn:hover {
      transform: scale(1.05);
      box-shadow: 0 6px 20px rgba(0, 123, 255, 0.3);
    }

    footer {
      background: linear-gradient(135deg, #74ABE2, #5563DE);
      color: #fff;
      padding: 1rem 2rem;
      text-align: center;
    }

    .contact-info {
      max-width: 800px;
      margin: 0 auto;
    }

    .contact-info a {
      color: #fff;
      text-decoration: none;
    }
  </style>
</head>
<body>
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

    <div class="booking-container">
        <h2>Movie Recommendations</h2>
        <?php if (!empty($recommendations)): ?>
            <?php foreach ($recommendations as $movie): ?>
                <div class="movie-details">
                    <img src="<?php echo $movie['photo']; ?>" alt="Movie Poster">
                    <h3><?php echo $movie['name']; ?></h3>
                    <p><?php echo $movie['description']; ?></p>
                    <p>Release Date: <?php echo $movie['release_date']; ?></p>
                    <a href="book.php?movie_id=<?php echo $movie['id']; ?>" class="btn">Book Now</a>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <p>No recommendations available based on your booking history.</p>
        <?php endif; ?>
    </div>

    <footer>
        <div class="contact-info" id="contact">
            <p>&copy; 2025 Movie Ticketing System. For inquiries, contact us at:</p>
            <p>Email: <a href="mailto:support@movieticketing.com">support@movieticketing.com</a></p>
            <p>Phone: +1 (123) 456-7890</p>
        </div>
    </footer>
</body>
</html>