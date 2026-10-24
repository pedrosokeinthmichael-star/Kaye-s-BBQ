<?php
// ==========================================
// Activity 1: Display a Message
// ==========================================
echo "<h2>1. Display a Message</h2>";

// a. Display message
echo "Annie Rose LUmacang - Activity 1<br>";

// b. Store variables
$name = "Annie Rose LUmacang";
$age = 25;
$favoriteColor = "Blue";

// c. Display sentence
echo "My name is $name, I am $age years old, and my favorite color is $favoriteColor.<br><br>";

// ==========================================
// Activity 2: Basic Arithmetic
// ==========================================
echo "<h2>2. Basic Arithmetic</h2>";

// a. Create two variables
$num1 = 20;
$num2 = 8;

// b. Perform operations
echo "Numbers: $num1 and $num2<br>";
echo "Addition: " . ($num1 + $num2) . "<br>";
echo "Subtraction: " . ($num1 - $num2) . "<br>";
echo "Multiplication: " . ($num1 * $num2) . "<br>";
echo "Division: " . ($num1 / $num2) . "<br>";
echo "Modulus: " . ($num1 % $num2) . "<br><br>";

// ==========================================
// Activity 3: Concatenating Strings
// ==========================================
echo "<h2>3. Concatenating Strings</h2>";

// a. Create two string variables
$firstName = "Juan";
$lastName = "Dela Cruz";

// b. Concatenate
$fullName = $firstName . " " . $lastName;

// c. Display
echo "Full Name: $fullName<br><br>";

// ==========================================
// Activity 4: Convert to Uppercase
// ==========================================
echo "<h2>4. Convert to Uppercase</h2>";

// a. Only one variable
$text = "I love php";

// b. Use built-in function
$upperText = strtoupper($text);

// c. Display
echo $upperText . "<br><br>";

// ==========================================
// Activity 5: Calculate String Length
// ==========================================
echo "<h2>5. Calculate String Length</h2>";

// a. Create string variable
$sentence = "Learning PHP is fun!";

// b. Use strlen()
$length = strlen($sentence);
echo "String: \"$sentence\"<br>";
echo "Length: $length characters<br><br>";

// ==========================================
// Activity 6: Replace Substrings
// ==========================================
echo "<h2>6. Replace Substrings</h2>";

// a. Create string variable
$originalText = "I love PHP!";

// b. Use str_replace()
$newText = str_replace("PHP", "programming", $originalText);
echo "Original: $originalText<br>";
echo "New: $newText<br><br>";

// ==========================================
// Activity 7: Get Last Three Characters
// ==========================================
echo "<h2>7. Get Last Three Characters</h2>";

// Sample string
$email = "juandelacruz@gmail.com";

// Use substr() to get last 3 characters
$lastThree = substr($email, -3);
echo "Email: $email<br>";
echo "Last three characters: $lastThree<br>";   


?>