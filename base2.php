<?php
session_start();
include 'db.php';
// Modify the SQL query to include more movie details
$sql = "SELECT *, 
        (SELECT AVG(rating) FROM ratings WHERE movie_id = movies.id) as avg_rating, 
        (SELECT GROUP_CONCAT(DISTINCT slot_time ORDER BY slot_time) FROM movie_slots WHERE movie_id = movies.id) as slot_times 
        FROM movies 
        ORDER BY avg_rating DESC";
$result = $conn->query($sql);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Movie Ticketing System</title>
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

    .logo {
      font-size: 1.5rem;
      font-weight: bold;
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

    .container {
      max-width: 1200px;
      margin: 2rem auto;
      padding: 0 2rem;
    }

    .dashboard {
      text-align: center;
      margin-bottom: 2rem;
    }

    .dashboard h2 {
      margin-bottom: 1rem;
    }

    .search-filter {
      margin-bottom: 3rem;
      display: flex;
      gap: 1.5rem;
      justify-content: center;
      flex-wrap: wrap;
      background: rgba(255, 255, 255, 0.95);
      padding: 2rem;
      border-radius: 12px;
      box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
      backdrop-filter: blur(10px);
      border: 1px solid rgba(255, 255, 255, 0.1);
    }

    .search-filter input,
    .search-filter select {
      padding: 0.8rem;
      border: 1px solid #ddd;
      border-radius: 4px;
      min-width: 200px;
      transition: all 0.3s ease;
    }

    .search-filter input:focus,
    .search-filter select:focus {
      outline: none;
      border-color: #5563DE;
      box-shadow: 0 0 0 2px rgba(85, 99, 222, 0.2);
    }

    .movie {
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

    .movie:hover {
      transform: translateY(-8px);
      box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
      background-color: rgba(255, 255, 255, 1);
    }

    .movie img {
      width: 200px;
      height: 300px;
      object-fit: cover;
      border-radius: 8px;
      margin-right: 2rem;
      box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
      transition: transform 0.3s ease;
    }

    .movie-info {
      flex: 1;
      display: flex;
      flex-direction: column;
      gap: 0.5rem;
    }

    .movie-title {
      font-size: 1.8rem;
      color: #333;
      margin: 0 0 0.5rem 0;
    }

    .movie-description {
      color: #666;
      line-height: 1.6;
      margin: 0.5rem 0;
    }

    .movie-meta {
      display: flex;
      gap: 1.5rem;
      color: #666;
      font-size: 0.9rem;
      margin: 0.5rem 0;
    }

    .movie-meta span {
      display: flex;
      align-items: center;
      gap: 0.5rem;
    }

    .rating {
      display: flex;
      align-items: center;
      gap: 0.5rem;
      margin: 0.5rem 0;
    }

    .star {
      color: #ffd700;
      font-size: 1.2rem;
    }

    .release-date {
      color: #666;
      margin: 0.5rem 0;
    }

    .book-now {
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

    .book-now:hover {
      background: linear-gradient(135deg, #0056b3, #004494);
      transform: translateY(-2px);
      box-shadow: 0 6px 20px rgba(0, 123, 255, 0.3);
    }

    .recommendation-btn {
      display: inline-flex;
      align-items: center;
      justify-content: center;
      height: 100%;
      padding: 0.8rem 1.5rem;
      background: linear-gradient(135deg, #007BFF, #0056b3);
      color: white;
      border-radius: 4px;
      text-decoration: none;
      font-weight: 600;
      transition: all 0.3s ease;
      box-shadow: 0 4px 15px rgba(0, 123, 255, 0.2);
    }

    .recommendation-btn:hover {
      background: linear-gradient(135deg, #0056b3, #004494);
      transform: translateY(-2px);
      box-shadow: 0 6px 20px rgba(0, 123, 255, 0.3);
    }

    @media (max-width: 768px) {
      .container {
        padding: 0 1rem;
      }

      .movie {
        flex-direction: column;
        align-items: center;
        text-align: center;
        padding: 1.5rem;
      }

      .movie img {
        width: 160px;
        height: 240px;
        margin-right: 0;
        margin-bottom: 1.5rem;
      }

      .movie-info {
        width: 100%;
      }

      .movie-title {
        font-size: 1.5rem;
      }

      .movie-meta {
        justify-content: center;
        flex-wrap: wrap;
      }

      .book-now {
        align-self: center;
        width: 100%;
        text-align: center;
      }

      .hamburger {
        display: flex;
        z-index: 100;
      }

      nav {
        display: none;
      }

      nav.active {
        display: flex;
        flex-direction: column;
        position: absolute;
        top: 100%;
        left: 0;
        right: 0;
        background: linear-gradient(135deg, #74ABE2, #5563DE);
        padding: 1.5rem;
        gap: 1.5rem;
        animation: slideDown 0.3s ease-out;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
      }

      @keyframes slideDown {
        from {
          opacity: 0;
          transform: translateY(-10px);
        }
        to {
          opacity: 1;
          transform: translateY(0);
        }
      }

      .search-filter {
        padding: 1.5rem;
      }

      .search-filter input,
      .search-filter select {
        width: 100%;
        min-width: unset;
      }
    }

    .skeleton {
      animation: loading 1.5s infinite;
      background: linear-gradient(90deg, #f0f0f0 25%, #e0e0e0 50%, #f0f0f0 75%);
      background-size: 200% 100%;
    }

    @keyframes fadeIn {
      from { opacity: 0; transform: translateY(10px); }
      to { opacity: 1; transform: translateY(0); }
    }

    @keyframes loading {
      0% { background-position: 200% 0; }
      100% { background-position: -200% 0; }
    }

    /* Enhance responsive design */
    @media (max-width: 768px) {
      .movie {
        flex-direction: column;
        text-align: center;
      }

      .movie-info {
        margin: 1rem 0;
      }

      .search-filter {
        flex-direction: column;
        align-items: stretch;
      }
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

    .social-icons {
      display: flex;
      gap: 1rem;
      margin-top: 1rem;
    }

    .social-icons a {
      color: #fff;
      font-size: 1.5rem;
      transition: transform 0.3s ease;
    }

    .social-icons a:hover {
      transform: translateY(-3px);
      color: #3498db;
    }

    @media (max-width: 768px) {
      .footer-content {
        grid-template-columns: 1fr;
        text-align: center;
      }

      .footer-section h3::after {
        left: 50%;
        transform: translateX(-50%);
      }

      .social-icons {
        justify-content: center;
      }
    }

    .newsletter-form {
      margin-top: 1rem;
    }

    .newsletter-form input {
      padding: 0.8rem;
      border: none;
      border-radius: 4px;
      margin-right: 0.5rem;
      width: 100%;
      max-width: 200px;
      margin-bottom: 1rem;
    }

    .newsletter-form button {
      padding: 0.8rem 1.5rem;
      background-color: #3498db;
      border: none;
      border-radius: 4px;
      color: white;
      cursor: pointer;
      transition: background-color 0.3s ease;
    }

    .newsletter-form button:hover {
      background-color: #2980b9;
    }

    .social-icons {
      display: flex;
      gap: 1rem;
      margin-top: 1rem;
    }

    .social-icons a {
      color: #fff;
      font-size: 1.5rem;
      transition: transform 0.3s ease;
    }

    .social-icons a:hover {
      transform: translateY(-3px);
      color: #3498db;
    }

    @media (max-width: 768px) {
      .footer-content {
        grid-template-columns: 1fr;
        text-align: center;
      }

      .footer-section h3::after {
        left: 50%;
        transform: translateX(-50%);
      }

      .social-icons {
        justify-content: center;
      }

      .newsletter-form {
        display: flex;
        flex-direction: column;
        align-items: center;
      }
    }
    .slot-time {
  display: inline-block;
  padding: 0.5rem 1rem;
  margin: 0.5rem 0;
  background: linear-gradient(135deg, #ff7e5f, #feb47b);
  color: white;
  border-radius: 8px;
  text-decoration: none;
  font-weight: 600;
  transition: all 0.3s ease;
  box-shadow: 0 4px 15px rgba(255, 126, 95, 0.2);
}

.slot-time:hover {
  background: linear-gradient(135deg, #feb47b, #ff7e5f);
  transform: translateY(-2px);
  box-shadow: 0 6px 20px rgba(255, 126, 95, 0.3);
}

@media (max-width: 768px) {
  .slot-time {
    width: 100%;
    text-align: center;
  }
}
  </style>

</head>
<body>
  <header>
    <div class="logo">Movie Ticketing</div>
    <nav id="menu">
      <a href="#contact">Contact</a> <!-- Link to the contact section in footer -->
      <?php if (isset($_SESSION['user_id'])): ?>
        <span style="color: white; margin-right: 10px;">Hello, <?php echo htmlspecialchars($_SESSION['user_email']); ?></span>
        <a href="my_bookings.php">My Bookings</a>
        <a href="logout.php">Logout</a>
      <?php else: ?>
        <a href="login.php">Login</a>
      <?php endif; ?>
    </nav>
  </header>

  <div class="container">
    <div class="dashboard" id="dashboard">
      <h2>Welcome to the Movie Ticketing System</h2>
      <p>Select your favorite movie and book your tickets now!</p>
      
      <div class="search-filter">
        <input type="text" id="searchMovie" placeholder="Search movies...">
        <select id="filterGenre">
          <option value="">All Genres</option>
          <option value="action">Action</option>
          <option value="comedy">Comedy</option>
          <option value="romance">Romance</option>
          <option value="drama">Drama</option>
        </select>
        <select id="filterRating">
          <option value="">All Ratings</option>
          <option value="4">4+ Stars</option>
          <option value="3">3+ Stars</option>
          <option value="2">2+ Stars</option>
          <option value="1">1+ Star</option>
        </select>
        <a href="movie_similarity.php" class="recommendation-btn" style="margin-left: 1.5rem;">Recommendations</a>
      </div>
    </div>

    <div class="movies" id="movies">
      <?php
      if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
          $rating = number_format($row['avg_rating'] ?? 0, 1);
          $genre = $row['genre'] ?? 'Uncategorized';
          $duration = $row['duration'] ?? 'N/A';
          
          echo "<div class='movie' data-genre='" . htmlspecialchars($genre) . "'>";
          $photo_path = $row['photo'];
          if (!file_exists($photo_path)) {
              $photo_path = 'uploads/default-movie.jpg';
          }
          echo "<img src='" . $row['photo'] . "' alt='Movie Poster'>";
          echo "<div class='movie-info'>";
          echo "<h2 class='movie-title'>" . htmlspecialchars($row['name']) . "</h2>";
          echo "<div class='rating'>";
          echo "<div class='star'>" . str_repeat("★", round($rating)) . str_repeat("☆", 5 - round($rating)) . "</div>";
          echo "<span>$rating/5</span>";
          echo "</div>";
          echo "<div class='movie-meta'>";
          echo "<span><i class='fas fa-clock'></i> " . htmlspecialchars($duration) . " mins</span>";
          echo "<span><i class='fas fa-film'></i> " . htmlspecialchars($genre) . "</span>";
          echo "</div>";
          echo "<p class='movie-description'>" . htmlspecialchars($row['description']) . "</p>";
          echo "<p class='release-date'>Release Date: " . htmlspecialchars($row['release_date']) . "</p>";
          echo "<div class='slot-times'>";
          $slot_times = explode(',', $row['slot_times']);
          foreach ($slot_times as $slot_time) {
              $slot_date = date('Y-m-d'); // Define $slot_date with the current date
              echo "<a href='book.php?movie_id=" . $row['id'] . "&slot_date=" . $slot_date . "&slot_time=" . urlencode($slot_time) . "' class='slot-time'>" . htmlspecialchars($slot_time) . "</a>";
          }
          echo "</div>";
          echo "</div>";
          $slot_date = date('Y-m-d'); // Define $slot_date with the current date
          echo "<a class='recommendation-btn' href='book.php?movie_id=" . $row['id'] . "&slot_date=" . $slot_date . "&slot_time=" . $slot_time . "'>Book Now</a>";
          echo "</div>";
        }
      } else {
        echo "<p>No movies available.</p>";
      }
      $conn->close();
      ?>
    </div>
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

  <script>
    // Lazy loading implementation
    document.addEventListener('DOMContentLoaded', function() {
      let lazyImages = [].slice.call(document.querySelectorAll('img.lazy'));

      if ('IntersectionObserver' in window) {
        let lazyImageObserver = new IntersectionObserver(function(entries, observer) {
          entries.forEach(function(entry) {
            if (entry.isIntersecting) {
              let lazyImage = entry.target;
              lazyImage.src = lazyImage.dataset.src;
              lazyImage.classList.add('loaded');
              lazyImageObserver.unobserve(lazyImage);
            }
          });
        });

        lazyImages.forEach(function(lazyImage) {
          lazyImageObserver.observe(lazyImage);
        });
      }
    });

    function toggleMenu() {
      const menu = document.getElementById('menu');
      menu.classList.toggle('active');
    }

    // Add search and filter functionality
    const searchInput = document.getElementById('searchMovie');
    const genreFilter = document.getElementById('filterGenre');
    const ratingFilter = document.getElementById('filterRating');

    // Add event listeners to all filter inputs
    [searchInput, genreFilter, ratingFilter].forEach(element => {
      element.addEventListener('input', filterMovies);
      element.addEventListener('change', filterMovies);
    });

    function filterMovies() {
      const searchTerm = searchInput.value.toLowerCase().trim();
      const selectedGenre = genreFilter.value.toLowerCase();
      const selectedRating = parseFloat(ratingFilter.value) || 0;
      const movies = document.querySelectorAll('.movie');

      movies.forEach(movie => {
        const title = movie.querySelector('.movie-title').textContent.toLowerCase();
        const genre = movie.getAttribute('data-genre').toLowerCase();
        const ratingText = movie.querySelector('.rating span').textContent;
        const rating = parseFloat(ratingText) || 0;
        
        const matchesSearch = searchTerm === '' || title.includes(searchTerm);
        const matchesGenre = selectedGenre === '' || genre === selectedGenre;
        const matchesRating = selectedRating === 0 || rating >= selectedRating;
        
        if (matchesSearch && matchesGenre && matchesRating) {
          movie.style.display = 'flex';
          movie.style.animation = 'fadeIn 0.5s ease-in';
        } else {
          movie.style.display = 'none';
        }
      });
    }
    document.addEventListener('DOMContentLoaded', function() {
      const timeSlots = document.querySelectorAll('.time-slot');
      timeSlots.forEach(slot => {
        slot.addEventListener('click', function() {
          const selectedDate = this.getAttribute('data-date');
          const selectedTime = this.getAttribute('data-time');
          document.getElementById('date').value = selectedDate;
          document.getElementById('slot_id').value = selectedTime;
        });
      });
    });
  </script>
</body>
</html>
