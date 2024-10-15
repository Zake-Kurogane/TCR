<?php
include 'config.php';

// Start session to access session variables
session_start();

// Check if the user is logged in, if not then redirect him to login page
if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true){
  header("location: login.php");
  exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Chat - TC Car Rental</title>

  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
  <link rel="icon" href="img/logo_web.png" type="image/png">
  <script src="https://cdn.tailwindcss.com"></script>
  <script>
    let isFirstMessage = true; // Flag to check if it's the user's first message

    // Toggle sidebar visibility
    function toggleSidebar() {
      document.getElementById('sidebar').classList.toggle('-translate-x-full');
      document.getElementById('overlay').classList.toggle('hidden');
    }

    // Function to send user messages
    function sendMessage() {
      const userInput = document.getElementById('userMessage').value;
      if (userInput.trim() === '') return; // Don't send empty messages
      
      // Add user message to the chat window
      addMessageToChat(userInput, 'user');

      // Clear input field after sending
      document.getElementById('userMessage').value = '';

      if (isFirstMessage) {
        // Show suggestions in the chat after the first user message
        showInChatSuggestions();
        isFirstMessage = false;
      } else {
        // Generate bot response after subsequent messages
        setTimeout(() => {
          const botResponse = generateBotResponse(userInput);
          addMessageToChat(botResponse, 'bot');
        }, 1000); // Delay bot response for realism
      }
    }

    //show suggestions in the chat
    function showInChatSuggestions() {
      const suggestions = [
        'What is the car availability?',
        'Can I change my reservation?',
        'What are your prices?',
        'How do I contact support?'
      ];

      const suggestionsDiv = document.createElement('div');
      suggestionsDiv.classList.add('flex', 'space-x-2', 'mt-2', 'mb-2');
      
      suggestions.forEach(suggestion => {
        const suggestionButton = document.createElement('button');
        suggestionButton.classList.add('bg-blue-500', 'text-white', 'px-4', 'py-2', 'rounded-lg', 'hover:bg-blue-600');
        suggestionButton.textContent = suggestion;
        suggestionButton.onclick = () => {
          addMessageToChat(suggestion, 'user');
          const botResponse = generateBotResponse(suggestion);
          addMessageToChat(botResponse, 'bot');
        };
        suggestionsDiv.appendChild(suggestionButton);
      });

      // Append the suggestions to the chat window
      document.getElementById('chatWindow').appendChild(suggestionsDiv);
      document.getElementById('chatWindow').scrollTop = document.getElementById('chatWindow').scrollHeight; // Auto-scroll to the bottom
    }

    // Function to generate bot response based on user input
    function generateBotResponse(userInput) {
      let lowerInput = userInput.toLowerCase();
      
      if (lowerInput.includes('reservation')) {
        return 'Please provide your reservation number so I can assist you.';
      } else if (lowerInput.includes('12345abc')) {
        return 'Thank you! Your reservation is for a Toyota Camry, scheduled for pickup tomorrow at 10 AM.';
      } else if (lowerInput.includes('car availability')) {
        return 'We have a wide range of vehicles available including sedans, SUVs, and trucks. What type of car are you interested in?';
      } else if (lowerInput.includes('price')) {
        return 'Our rental prices vary depending on the vehicle type and rental duration. Can you specify the car type you are interested in?';
      } else if (lowerInput.includes('location')) {
        return 'We have several pickup locations. Where would you like to pick up your vehicle?';
      } else if (lowerInput.includes('how to book')) {
        return 'You can book a car directly on our website or through our mobile app. Would you like a link to the booking page?';
      } else if (lowerInput.includes('cancel reservation')) {
        return 'To cancel your reservation, please provide your reservation number, and I will guide you through the process.';
      } else if (lowerInput.includes('contact support')) {
        return 'You can reach our customer support at 1-234-5678-1111 or email support@tccarrental.com.';
      } else if (lowerInput.includes('thank you') || lowerInput.includes('thanks')) {
        return 'You’re welcome! If you have any more questions, feel free to ask.';
      } else {
        return "I'm sorry, I didn't understand that. Could you please ask about reservations, availability, or prices?";
      }
    }

    //add messages to chat window
    function addMessageToChat(message, sender) {
      const chatWindow = document.getElementById('chatWindow');
      const messageDiv = document.createElement('div');
      messageDiv.classList.add('flex', 'mb-4');
      
      if (sender === 'user') {
        messageDiv.classList.add('justify-end');
        messageDiv.innerHTML = `
          <div class="bg-gray-200 text-gray-800 p-3 rounded-lg mr-2">${message}</div>
          <img src="img/profile (1).png" alt="User" class="w-10 h-10 rounded-full">
        `;
      } else {
        messageDiv.innerHTML = `
          <img src="img/profile (1).png" alt="Bot" class="w-10 h-10 rounded-full">
          <div class="bg-blue-500 text-white p-3 rounded-lg ml-2">${message}</div>
        `;
      }
      
      chatWindow.appendChild(messageDiv);
      chatWindow.scrollTop = chatWindow.scrollHeight;
    }
  </script>
</head>
<body class="bg-gray-100">
  <!-- Main container -->
  <div class="flex min-h-screen">
    <nav class="h-screen w-30 bg-white shadow-lg flex flex-col items-center pt-6 space-y-8 fixed">
      <div class="px-6">
        <!-- Logo and navigation links -->
        <div class="flex justify-between items-center">
          <img src="img/logo-2x.png" alt="TC Car Rental" class="w-20">
          <button onclick="toggleSidebar()" class="lg:hidden focus:outline-none text-gray-500" aria-label="Toggle Sidebar">
            <i class="fas fa-times fa-lg"></i>
          </button>
        </div>
        <nav class="flex flex-col space-y-8 text-gray-500 mt-14">
          <a href="customer_dashboard.php" class="flex justify-center items-center space-x-2">
            <img src="img/home.png" alt="Home Icon" class="w-7 h-7">
          </a>
          <a href="customer_calendar.php" class="flex justify-center items-center space-x-2">
            <img src="img/calendar.png" alt="Mail Icon" class="w-6 h-6">
          </a>
          <a href="customer_favourites.php" class="flex justify-center items-center space-x-2">
            <img src="img/heart.png" alt="Settings Icon" class="w-6 h-6">
          </a>
          <a href="customer_recents.php" class="flex justify-center items-center space-x-2 text-blue-500">
            <img src="img/watch.png" alt="Chat Icon" class="w-7 h-7">
          </a>
          <a href="customer_profile.php" class="flex justify-center items-center space-x-2 text-blue-500">
            <img src="img/profile.png" alt="Profile Icon" class="w-7 h-7">
          </a>
          <a href="customer_chat.php" class="flex justify-center items-center space-x-2 text-blue-500">
            <img src="img/bubble-chat (1).png" alt="Chat Icon" class="w-6 h-6">
          </a>
          <div class="px-6 py-2">
              <a href="logout.php" class="hover:text-blue-500 fixed mt-28 flex justify-center items-center space-x-2"><img src="img/logout.png" alt="Logout Icon" class="w-6 h-6"></a>
            </div>
        </nav>
      </div>
    </nav>

    <!-- Chat Section -->
    <main class="flex-1 flex ml-[129px]">

      <!-- Active Chat Window -->
      <div class="flex-1 py-2 bg-white shadow-lg">
        <div class="w-full h-20 bg-white p-2 shadow-lg mb-4 flex items-center space-x-2 cursor-pointer">
          <img src="img/profile (1).png" alt="Company" class="w-12 h-12 rounded-full">
          <div class="flex-1">
            <h3 class="font-bold px-2">Admin</h3>
          </div>
          <div class="relative mt-4 mb-4">
            <button class="flex items-right py-2 pr-6" aria-label="Options">
              <i class="fas fa-ellipsis-v"></i>
            </button>
          </div>
        </div>
        <div class="p-4 overflow-y-auto" style="height: 66%" id="chatWindow">
          <!-- Chat messages will be inserted here dynamically -->
        </div>

        <div class="flex items-center mt-4 p-4">
          <button class="py-2 rounded-lg ml-2" style="font-size: large;" aria-label="Attach File">
            <i class="fas fa-paperclip"></i>
          </button>
      
          <button class="px-4 py-2 rounded-lg ml-2 mr-2" style="font-size: large;" aria-label="Add Image">
            <i class="fas fa-image"></i> 
          </button>

          <input type="text" id="userMessage" class="border border-gray-300 rounded-lg flex-1 p-2" placeholder="Type your message..." 
                 onkeypress="if(event.key === 'Enter') sendMessage()">
          <button class="bg-blue-500 text-white px-4 py-2 rounded-lg ml-2" onclick="sendMessage()" aria-label="Send Message">
            <i class="fas fa-paper-plane"></i>
          </button>
        </div>
      </div>
    </main>
  </div>
</body>
</html>