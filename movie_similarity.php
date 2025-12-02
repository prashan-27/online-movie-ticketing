<?php
session_start();
include 'db.php';

// Redirect to login if user is not logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

function calculateCosineSimilarity($vector1, $vector2) {
    // Calculate dot product
    $dotProduct = 0;
    foreach ($vector1 as $key => $value) {
        if (isset($vector2[$key])) {
            $dotProduct += $value * $vector2[$key];
        }
    }

    // Calculate magnitudes
    $magnitude1 = sqrt(array_sum(array_map(function($x) { return $x * $x; }, $vector1)));
    $magnitude2 = sqrt(array_sum(array_map(function($x) { return $x * $x; }, $vector2)));

    // Avoid division by zero
    if ($magnitude1 == 0 || $magnitude2 == 0) {
        return 0;
    }

    // Return cosine similarity
    return $dotProduct / ($magnitude1 * $magnitude2);
}

function getMovieFeatureVector($conn, $movie_id) {
    // Get movie genres and ratings
    $sql = "SELECT m.genre, COALESCE(AVG(r.rating), 0) as avg_rating 
            FROM movies m 
            LEFT JOIN ratings r ON m.id = r.movie_id 
            WHERE m.id = ? 
            GROUP BY m.id, m.genre";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('i', $movie_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $movie = $result->fetch_assoc();

    // Convert genre string to array
    $genres = explode(',', $movie['genre']);
    $vector = [];

    // Create feature vector with genre weights
    foreach ($genres as $genre) {
        $genre = trim($genre);
        $vector['genre_' . $genre] = 1;
    }

    // Add rating feature
    $vector['rating'] = $movie['avg_rating'];

    return $vector;
}

function getMovieRecommendations($conn, $user_id, $limit = 5) {
    // Get user's watched movies
    $watched_sql = "SELECT DISTINCT m.id, m.name 
                    FROM movies m 
                    JOIN bookings b ON m.id = b.movie_id 
                    WHERE b.user_id = ?";
    $stmt = $conn->prepare($watched_sql);
    $stmt->bind_param('i', $user_id);
    $stmt->execute();
    $watched_result = $stmt->get_result();
    
    $watched_movies = [];
    while ($movie = $watched_result->fetch_assoc()) {
        $watched_movies[] = $movie;
    }

    // Get all available movies with details
    $all_movies_sql = "SELECT m.id, m.name, m.description, m.photo, m.release_date, m.genre 
                       FROM movies m 
                       WHERE m.id NOT IN 
                       (SELECT DISTINCT movie_id FROM bookings WHERE user_id = ?)";
    $stmt = $conn->prepare($all_movies_sql);
    $stmt->bind_param('i', $user_id);
    $stmt->execute();
    $all_result = $stmt->get_result();

    $recommendations = [];
    $similarity_threshold = 0.3; // Only show movies with 30% or higher similarity

    // Calculate similarities
    while ($candidate = $all_result->fetch_assoc()) {
        $max_similarity = 0;
        
        // Compare with each watched movie
        foreach ($watched_movies as $watched) {
            $watched_vector = getMovieFeatureVector($conn, $watched['id']);
            $candidate_vector = getMovieFeatureVector($conn, $candidate['id']);
            
            $similarity = calculateCosineSimilarity($watched_vector, $candidate_vector);
            $max_similarity = max($max_similarity, $similarity);
        }

        // Only add movies that meet the similarity threshold
        if ($max_similarity >= $similarity_threshold) {
            $recommendations[] = [
                'id' => $candidate['id'],
                'name' => $candidate['name'],
                'description' => $candidate['description'],
                'photo' => $candidate['photo'],
                'release_date' => $candidate['release_date'],
                'genre' => $candidate['genre'],
                'similarity' => $max_similarity
            ];
        }
    }

    // Sort by similarity score
    usort($recommendations, function($a, $b) {
        return $b['similarity'] <=> $a['similarity'];
    });

    // Return top N recommendations
    return array_slice($recommendations, 0, $limit);
}

$user_id = $_SESSION['user_id'];
$recommendations = getMovieRecommendations($conn, $user_id);
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
            gap: 2rem;
            position: relative;
        }

        .movie-info {
            display: flex;
            flex-direction: column;
            flex-grow: 1;
            gap: 0.5rem;
        }

        .movie-info h3 {
            margin: 0;
            color: #333;
            font-size: 1.8rem;
        }

        .movie-info p {
            margin: 0;
            color: #666;
            line-height: 1.6;
        }

        .movie-info .btn {
            margin-top: 1rem;
            align-self: flex-start;
        }

        .similarity-score {
            position: absolute;
            top: 1rem;
            right: 1rem;
            background: rgba(85, 99, 222, 0.1);
            padding: 0.5rem 1rem;
            border-radius: 20px;
            font-weight: 600;
            color: #5563DE;
        }

        .btn {
            margin-top: 1rem;
            display: inline-block;
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
                    <div class="movie-info">
                        <h3><?php echo $movie['name']; ?></h3>
                        <p><?php echo $movie['description']; ?></p>
                        <p>Genres: <?php echo $movie['genre']; ?></p>
                        <p>Release Date: <?php echo $movie['release_date']; ?></p>
                        <p>Similarity Score: <?php echo number_format($movie['similarity'] * 100, 1); ?>%</p>
                        <a href="book.php?movie_id=<?php echo $movie['id']; ?>" class="btn">Book Now</a>
                    </div>
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