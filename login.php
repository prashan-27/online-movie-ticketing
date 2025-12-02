<?php
session_start();
include 'db.php'; // Include database connection

// Handle login
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["login"])) {
    $email = trim($_POST["email"]);
    $password = $_POST["password"];

    // Prepare and execute SQL statement
    $sql = "SELECT id, name, email, password, role FROM users WHERE email = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->store_result();
    $stmt->bind_result($id, $name, $user_email, $hashed_password, $role);
    $stmt->fetch();

    if ($stmt->num_rows > 0 && password_verify($password, $hashed_password)) {
        // Login successful, setting session variables
        $_SESSION["user_id"] = $id;
        $_SESSION["user_name"] = $name;
        $_SESSION["user_email"] = $user_email;
        $_SESSION["user_role"] = $role; // Store role in session

        // Redirect based on user role
        if ($role === 'admin') {
            echo "<script>window.location='admin.php';</script>"; // Redirect to admin page if admin
        } else {
            echo "<script>window.location='base2.php';</script>"; // Redirect to the regular user page
        }
        exit();
    } else {

        echo "<script>alert('Invalid email or password!', 'error');</script>";
    }

    $stmt->close();
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Login</title>
  <link rel="stylesheet" href="css/notifications.css">
  <script src="js/notifications.js"></script>
  <style>
    /* Reset styles */
    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
    }
    
    /* Body style with background gradient and centering */
    body {
      font-family: Arial, sans-serif;
      background: linear-gradient(135deg, #74ABE2, #5563DE);
      height: 100vh;
      display: flex;
      justify-content: center;
      align-items: center;
      flex-direction: column;
    }
    
    /* Login form container */
    form {
      background-color: #ffffff;
      padding: 30px 40px;
      border-radius: 8px;
      box-shadow: 0 8px 16px rgba(0, 0, 0, 0.15);
      width: 100%;
      max-width: 400px;
    }
    
    /* Form heading */
    h2 {
      text-align: center;
      margin-bottom: 25px;
      color: #333;
    }
    
    /* Label styling */
    label {
      display: block;
      margin-bottom: 8px;
      color: #555;
      font-size: 14px;
    }
    
    /* Input styling */
    input[type="email"],
    input[type="password"] {
      width: 100%;
      padding: 10px 12px;
      margin-bottom: 15px;
      border: 1px solid #ddd;
      border-radius: 4px;
      font-size: 15px;
    }
    
    /* Button styling */
    button {
      width: 100%;
      padding: 12px;
      background-color: #5563DE;
      border: none;
      border-radius: 4px;
      color: #fff;
      font-size: 16px;
      cursor: pointer;
      transition: background-color 0.3s ease;
    }
    
    button:hover {
      background-color: #3f4bb8;
    }
    
    /* Additional link styling */
    p {
      text-align: center;
      margin-top: 20px;
      font-size: 14px;
      color: #fff;
    }
    
    p a {
      color: #fff;
      text-decoration: underline;
    }
    
    /* Responsive design */
    @media (max-width: 480px) {
      form {
        padding: 20px;
      }
    }
  </style>
</head>
<body>
  <form method="POST" action="">
    <h2>Login</h2>
    <label for="email">Email:</label>
    <input type="email" id="email" name="email" required>
    
    <label for="password">Password:</label>
    <input type="password" id="password" name="password" required>
    
    <button type="submit" name="login">Login</button>
  </form>
  <p>Don't have an account? <a href="register.php">Register</a></p>
</body>
</html>
