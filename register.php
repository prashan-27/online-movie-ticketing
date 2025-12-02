<?php
include 'db.php';

$password_error = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name     = trim($_POST["name"]);
    $email    = trim($_POST["email"]);
    $password = $_POST["password"];

    $name_error = "";
    if (!preg_match('/^[A-Za-z]/', $name)) {
        $name_error = "Name must start with an alphabet";
    }
    
    // Server-side email validation
    $email_error = "";
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $email_error = "Invalid email format";
    } else if (strpos($email, '.com') === false) {
        $email_error = "Email must contain '.com'";
    }
    // Server-side password validation
    $password_valid = true;
    
    // Check password length
    if (strlen($password) < 8) {
        $password_error = "Password must be at least 8 characters long";
        $password_valid = false;
    }
    // Check for uppercase letter
    else if (!preg_match('/[A-Z]/', $password)) {
        $password_error = "Password must contain at least one uppercase letter";
        $password_valid = false;
    }
    // Check for lowercase letter
    else if (!preg_match('/[a-z]/', $password)) {
        $password_error = "Password must contain at least one lowercase letter";
        $password_valid = false;
    }
    // Check for number
    else if (!preg_match('/[0-9]/', $password)) {
        $password_error = "Password must contain at least one number";
        $password_valid = false;
    }
    // Check for special character
    else if (!preg_match('/[^A-Za-z0-9]/', $password)) {
        $password_error = "Password must contain at least one special character";
        $password_valid = false;
    }
   
    if ($password_valid && empty($email_error)) {
        $hashed_password = password_hash($password, PASSWORD_DEFAULT); // Secure password hashing

        $sql  = "INSERT INTO users (name, email, password) VALUES (?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sss", $name, $email, $hashed_password);

        if ($stmt->execute()) {
            echo "<script>alert('Registration successful!'); window.location='login.php';</script>";
        } else {
            echo "<script>alert('Error: Email already in use!');</script>";
        }
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Register</title>
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
    }
    
    /* Registration form container */
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
    input[type="text"],
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
    
    /* Error message styling */
    .error-message {
      color: #e74c3c;
      font-size: 13px;
      margin-top: -10px;
      margin-bottom: 10px;
      display: block;
    }
    
    /* Password requirements styling */
    .password-requirements {
      background-color: #f8f9fa;
      border: 1px solid #e9ecef;
      border-radius: 4px;
      padding: 10px;
      margin-bottom: 15px;
      font-size: 13px;
    }
    
    .password-requirements p {
      margin-bottom: 5px;
      color: #6c757d;
    }
    
    .requirement {
      display: flex;
      align-items: center;
      margin-bottom: 3px;
    }
    
    .requirement.valid {
      color: #28a745;
    }
    
    .requirement.invalid {
      color: #6c757d;
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
  <form method="POST" action="" id="registerForm">
    <h2>Register</h2>
    <label for="name">Name:</label>
    <input type="text" id="name" name="name" required>
    <?php if (!empty($name_error)): ?>
      <span class="error-message"><?php echo $name_error; ?></span>
    <?php endif; ?>
    
    <label for="email">Email:</label>
    <input type="email" id="email" name="email" required>
    <?php if (!empty($email_error)): ?>
      <span class="error-message"><?php echo $email_error; ?></span>
    <?php endif; ?>
    
    <label for="password">Password:</label>
    <input type="password" id="password" name="password" required>
    <?php if (!empty($password_error)): ?>
      <span class="error-message"><?php echo $password_error; ?></span>
    <?php endif; ?>
    
    <div class="password-requirements">
      <p>Password must contain:</p>
      <div class="requirement" id="length">• At least 8 characters</div>
      <div class="requirement" id="uppercase">• At least one uppercase letter</div>
      <div class="requirement" id="lowercase">• At least one lowercase letter</div>
      <div class="requirement" id="number">• At least one number</div>
      <div class="requirement" id="special">• At least one special character</div>
    </div>
    
    <button type="submit" id="submitBtn">Register</button>
  </form>
  
  <script>
    // Get DOM elements
    const passwordInput = document.getElementById('password');
    const lengthReq = document.getElementById('length');
    const uppercaseReq = document.getElementById('uppercase');
    const lowercaseReq = document.getElementById('lowercase');
    const numberReq = document.getElementById('number');
    const specialReq = document.getElementById('special');
    const submitBtn = document.getElementById('submitBtn');
    const registerForm = document.getElementById('registerForm');
    const nameInput = document.getElementById('name');
    
    // Add event listener for name input
    nameInput.addEventListener('input', validateName);
    
    function validateName() {
      const name = nameInput.value;
      const nameReq = /^[A-Za-z]/.test(name);
    
      if (nameReq) {
        nameInput.classList.add('valid');
        nameInput.classList.remove('invalid');
      } else {
        nameInput.classList.add('invalid');
        nameInput.classList.remove('valid');
      }
    }
    
    // Validate name on form submission
    registerForm.addEventListener('submit', function(event) {
      if (!/^[A-Za-z]/.test(nameInput.value)) {
        event.preventDefault();
        alert('Name must start with an alphabet.');
      }
    });
    
    // Add event listener for password input
    passwordInput.addEventListener('input', validatePassword);
    
    // Validate password on form submission
    registerForm.addEventListener('submit', function(event) {
      if (!isPasswordValid()) {
        event.preventDefault();
        alert('Please ensure your password meets all requirements.');
      }
    });
    
    function validatePassword() {
      const password = passwordInput.value;
      
      // Check length
      if (password.length >= 8) {
        lengthReq.classList.add('valid');
        lengthReq.classList.remove('invalid');
      } else {
        lengthReq.classList.add('invalid');
        lengthReq.classList.remove('valid');
      }
      
      // Check uppercase
      if (/[A-Z]/.test(password)) {
        uppercaseReq.classList.add('valid');
        uppercaseReq.classList.remove('invalid');
      } else {
        uppercaseReq.classList.add('invalid');
        uppercaseReq.classList.remove('valid');
      }
      
      // Check lowercase
      if (/[a-z]/.test(password)) {
        lowercaseReq.classList.add('valid');
        lowercaseReq.classList.remove('invalid');
      } else {
        lowercaseReq.classList.add('invalid');
        lowercaseReq.classList.remove('valid');
      }
      
      // Check number
      if (/[0-9]/.test(password)) {
        numberReq.classList.add('valid');
        numberReq.classList.remove('invalid');
      } else {
        numberReq.classList.add('invalid');
        numberReq.classList.remove('valid');
      }
      
      // Check special character
      if (/[^A-Za-z0-9]/.test(password)) {
        specialReq.classList.add('valid');
        specialReq.classList.remove('invalid');
      } else {
        specialReq.classList.add('invalid');
        specialReq.classList.remove('valid');
      }
    }
    
    function isPasswordValid() {
      const password = passwordInput.value;
      return (
        password.length >= 8 &&
        /[A-Z]/.test(password) &&
        /[a-z]/.test(password) &&
        /[0-9]/.test(password) &&
        /[^A-Za-z0-9]/.test(password)
      );
    }
  </script>
</body>
</html>
