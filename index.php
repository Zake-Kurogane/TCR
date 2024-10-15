<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Car Rental</title>
  <link rel="icon" href="img/logo_web.png" type="image/png">
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-white">

  <!-- Header -->
  <header class="flex justify-between items-center p-4 bg-white shadow-md">
    <div class="flex items-center space-x-2">
      <!-- Logo -->
      <div class="flex justify-between items-center">
        <img src="img/logo-2x.png" alt="TC Car Rental" class="w-24">
      </div>
    </div>

    <!-- Buttons -->
    <div>
      <a href="customer_signup.php" class="text-gray-500 mr-4 font-bold px-4 py-4 rounded-lg shadow-lg">Sign Up</a>
      <a href="login.php" class="bg-blue-500 text-white px-4 py-4 rounded-lg font-bold shadow-lg">Sign In</a>
    </div>
  </header>

  <!-- Main Section -->
  <section class="flex flex-col lg:flex-row items-center justify-between py-16 px-8 max-w-7xl mx-auto">
    <!-- Text Content -->
    <div class="lg:w-1/2">
      <h2 class="text-4xl font-bold text-gray-800 mb-4">Looking for a car to rent?</h2>
      <p class="text-gray-500 text-lg mb-8">Rent the most affordable and reliable cars in our shop.</p>
      <a href="login.php" class="bg-blue-500 text-white px-6 py-3 rounded-lg text-lg font-semibold hover:bg-blue-600">Rent Now</a>
    </div>

    <!-- Car Image -->
    <div class="relative lg:w-1/2 ml-10 mt-10 lg:mt-0">
      <!-- Blue Background Rectangle -->
      <div class="w-[500px] h-[300px] lg:h-[400px] bg-blue-600 rounded-lg"></div>
      
      <!-- Car Image positioned absolutely inside the div -->
      <img src="img/vios-removebg-preview.png" alt="Car Image" class="absolute top-1/2 right-0 transform -translate-y-1/2 -translate-x-1/4 w-[450px] lg:w-auto lg:max-w-full h-auto lg:mr-10">
    </div>
    
    
  </section>

</body>
</html>
