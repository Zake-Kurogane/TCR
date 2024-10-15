<?php

session_start();
include('config.php');

// Check if the user is logged in and is an admin
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true || $_SESSION["role"] !== 'admin') {
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
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="icon" href="img/logo_web.png" type="image/png">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <script>

        function toggleSidebar() {
            document.getElementById('sidebar').classList.toggle('-translate-x-full');
            document.getElementById('overlay').classList.toggle('hidden');
        }

        //send a message
        function sendMessage() {
            const input = document.getElementById('messageInput');
            const messageText = input.value.trim();
            if (messageText) {
                addMessageToChat('user', messageText);
                input.value = '';
            }
        }

        //add a message to the chat
        function addMessageToChat(sender, message) {
            const chatContainer = document.getElementById('chatMessages');
            const messageDiv = document.createElement('div');
            messageDiv.className = 'flex mb-4';

            if (sender === 'company') {
                messageDiv.innerHTML = `
          <img src="img/profile (1).png" alt="Company" class="w-10 h-10 rounded-full">
          <div class="bg-blue-500 text-white p-3 rounded-lg ml-2">${message}</div>
        `;
            } else {
                messageDiv.className += ' justify-end';
                messageDiv.innerHTML = `
          <div class="bg-gray-200 text-gray-800 p-3 rounded-lg mr-2">${message}</div>
          <img src="img/profile (1).png" alt="User" class="w-10 h-10 rounded-full">
        `;
            }

            chatContainer.appendChild(messageDiv);
            chatContainer.scrollTop = chatContainer.scrollHeight;
        }

        //select a chat
        function selectChat(customerName) {
            const activeChatName = document.getElementById('activeChatName');
            activeChatName.textContent = customerName;
            const chatContainer = document.getElementById('chatMessages');
            chatContainer.innerHTML = '';
        }

        //add new chat
        function addNewChat() {
            const modal = document.getElementById('newChatModal');
            modal.classList.remove('hidden');
        }

        //save new chat
        function saveNewChat() {
            const customerNameInput = document.getElementById('customerNameInput');
            const customerName = customerNameInput.value.trim();
            if (customerName) {
                const chatList = document.getElementById('chatList');
                const newChatButton = document.createElement('button');
                newChatButton.onclick = function () { selectChat(customerName); };
                newChatButton.className = "flex items-center space-x-2 bg-gray-100 p-2 rounded-lg shadow-lg cursor-pointer w-full text-left";
                newChatButton.innerHTML = `
                    <img src="img/profile (1).png" alt="Company" class="w-12 h-12 rounded-full">
                    <div class="flex-1">
                        <h3 class="font-bold">${customerName}</h3>
                        <p class="text-gray-600 text-sm">New message...</p>
                    </div>
                    <span class="text-gray-400" style="font-size: 12px;">Just now</span>
                `;
                chatList.appendChild(newChatButton);
                selectChat(customerName);
                closeModal();
            }
        }

        function closeModal() {
            const modal = document.getElementById('newChatModal');
            modal.classList.add('hidden');
            document.getElementById('customerNameInput').value = '';
        }
    </script>
</head>

<body class="bg-gray-100">
    <!-- Main container -->
    <div class="flex min-h-screen">
        <nav class="h-screen w-30 bg-white shadow-lg flex flex-col items-center pt-6 fixed">
            <div class="px-6">
                <!-- Logo and navigation links -->
                <div class="flex justify-between items-center">
                    <img src="img/logo-2x.png" alt="TC Car Rental" class="w-20">
                    <button onclick="toggleSidebar()" class="lg:hidden focus:outline-none text-gray-500">
                    </button>
                </div>
                <nav class="flex flex-col space-y-8 text-gray-500 mt-14">
                    <a href="admin_dashboard.php" class="flex justify-center items-center space-x-2"><img
                            src="img/home.png" alt="Home Icon" class="w-7 h-7"></a>
                    <a href="admin_notification.php" class="flex justify-center items-center space-x-2"><img
                            src="img/mail.png" alt="Mail Icon" class="w-6 h-6"></a>
                    <a href="admin_settings.php" class="flex justify-center items-center space-x-2"><img
                            src="img/setting (2).png" alt="Settings Icon" class="w-6 h-6"></a>
                    <a href="admin_chat.php" class="flex justify-center items-center space-x-2 text-blue-500"><img
                            src="img/bubble-chat (1).png" alt="Chat Icon" class="w-7 h-7"></a>
                    <div class="px-6 py-44">
                        <a href="login.php" class="fixed mt-14 flex justify-center items-center space-x-2"><img
                                src="img/logout.png" alt="Logout Icon" class="w-6 h-6"></a>
                    </div>
                </nav>
            </div>
        </nav>

        <!-- Chat Section -->
        <main class="flex-1 flex ml-[129px]">
            <!-- Chat List -->
            <div class="w-89 bg-white p-2 shadow-lg overflow-y-auto">
                <div class="w-full h-20 bg-white p-2 flex items-center space-x-2">
                    <div class="flex-1">
                        <h3 class="font-bold px-2" style="font-size: 25px;">Chats</h3>
                    </div>
                    <div class="relative mt-4 mb-4">
                        <button onclick="addNewChat()" class="flex items-right py-2 pr-2">
                            <i class="fas fa-plus-circle" style="font-size: 20px;"></i>
                        </button>
                    </div>
                </div>
                <div class="relative mb-4">
                    <input type="text"
                        class="border border-gray-300 rounded-lg pl-10 pr-4 py-2 w-full shadow-lg p-4"
                        style="font-size: 14px;" placeholder="Search Customers">
                    <i class="fas fa-search absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-800"></i>
                </div>

                <div class="space-y-4" id="chatList">
                    <button onclick="selectChat('Customer D. Luffy')"
                        class="flex items-center space-x-2 bg-gray-100 p-2 rounded-lg shadow-lg cursor-pointer w-full text-left">
                        <img src="img/profile (1).png" alt="Company" class="w-12 h-12 rounded-full">
                        <div class="flex-1">
                            <h3 class="font-bold">Customer D. Luffy</h3>
                            <p class="text-gray-600 text-sm">Hello Konichiwa Konbanwa</p>
                        </div>
                        <span class="text-gray-400" style="font-size: 12px;">10:30 AM</span>
                    </button>

                    <button onclick="selectChat('Customer Nami')"
                        class="flex items-center space-x-2 bg-gray-100 p-2 rounded-lg shadow-lg cursor-pointer w-full text-left">
                        <img src="img/profile (1).png" alt="Company" class="w-12 h-12 rounded-full">
                        <div class="flex-1">
                            <h3 class="font-bold">Customer Nami</h3>
                            <p class="text-gray-600 text-sm">Need help with my booking!</p>
                        </div>
                        <span class="text-gray-400" style="font-size: 12px;">9:32 AM</span>
                    </button>

                    <button onclick="selectChat('Customer Zoro')"
                        class="flex items-center space-x-2 bg-gray-100 p-2 rounded-lg shadow-lg cursor-pointer w-full text-left">
                        <img src="img/profile (1).png" alt="Company" class="w-12 h-12 rounded-full">
                        <div class="flex-1">
                            <h3 class="font-bold">Customer Zoro</h3>
                            <p class="text-gray-600 text-sm">Looking for a vehicle!</p>
                        </div>
                        <span class="text-gray-400" style="font-size: 12px;">8:50 PM</span>
                    </button>
                </div>
            </div>

            <!-- Chat Window -->
            <div class="flex-1 py-2 bg-white shadow-lg">
                <div class="w-full h-20 bg-white p-2 shadow-lg mb-4 flex items-center space-x-2 cursor-pointer">
                    <img src="img/profile (1).png" alt="Company" class="w-12 h-12 rounded-full">
                    <div class="flex-1">
                        <h3 class="font-bold px-2" id="activeChatName">Customer D. Luffy</h3>
                    </div>
                    <div class="relative mt-4 mb-4">
                        <button class="flex items-right py-2 pr-6">
                            <i class="fas fa-ellipsis-v"></i>
                        </button>
                    </div>
                </div>
                <div id="chatMessages" class="p-4 h-96 overflow-y-auto">
                    <!-- Message Items will be appended here -->
                </div>

                <div class="flex items-center mt-4 p-4">
                    <button class="py-2 rounded-lg ml-2" style="font-size: large;">
                        <i class="fas fa-paperclip"></i>
                    </button>

                    <button class="px-4 py-2 rounded-lg ml-2 mr-2" style="font-size: large;">
                        <i class="fas fa-image"></i>
                    </button>

                    <input id="messageInput" type="text" class="border border-gray-300 rounded-lg flex-1 p-2"
                        placeholder="Type your message...">
                    <button onclick="sendMessage()" class="bg-blue-500 text-white px-4 py-2 rounded-lg ml-2">
                        <i class="fas fa-paper-plane"></i>
                    </button>
                </div>
            </div>
        </main>
    </div>

    <!-- new chat -->
    <div id="newChatModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center hidden">
        <div class="bg-white rounded-lg shadow-lg p-6 w-96">
            <h2 class="text-lg font-bold mb-4">Add New Chat</h2>
            <input id="customerNameInput" type="text" class="border border-gray-300 rounded-lg p-2 w-full" placeholder="Enter customer name">
            <div class="flex justify-end mt-4">
                <button onclick="closeModal()" class="bg-gray-300 text-gray-700 px-4 py-2 rounded-lg mr-2">Cancel</button>
                <button onclick="saveNewChat()" class="bg-blue-500 text-white px-4 py-2 rounded-lg">Add Chat</button>
            </div>
        </div>
    </div>
</body>

</html>