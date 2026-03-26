<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Need Help? | North Park Employee Portal</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <style>
    body::before {
      content: "";
      position: fixed;
      inset: 0;
      background: url('assets/images/bg-login.jpg') center/cover no-repeat;
      filter: brightness(0.4);
      z-index: -1;
    }
  </style>
</head>
<body class="min-h-screen flex flex-col items-center justify-center text-white font-sans">

  <!-- Header -->
  <header class="text-center mb-10">
    <div class="flex items-center justify-center gap-3 mb-4">
      <img src="assets/images/np-logo.png" alt="North Park Logo" class="w-14 drop-shadow-lg">
      <h1 class="text-2xl sm:text-3xl font-bold">Need Help?</h1>
    </div>
    <p class="text-gray-200 max-w-md text-sm sm:text-base">
      We're here to assist you. Whether you’re having trouble logging in, finding a branch, or need technical support, just send us a message.
    </p>
  </header>

  <!-- Help Form -->
  <section class="bg-white/10 backdrop-blur-md rounded-2xl shadow-lg p-8 w-11/12 sm:w-2/3 md:w-1/2 max-w-lg">
    <form class="space-y-5">
      <div>
        <label for="name" class="block text-sm font-medium mb-1">Full Name</label>
        <input id="name" type="text" placeholder="Enter your name" class="w-full px-4 py-2 rounded-lg border border-gray-300 text-gray-800 focus:ring-2 focus:ring-green-400 outline-none">
      </div>

      <div>
        <label for="email" class="block text-sm font-medium mb-1">Email Address</label>
        <input id="email" type="email" placeholder="Enter your email" class="w-full px-4 py-2 rounded-lg border border-gray-300 text-gray-800 focus:ring-2 focus:ring-green-400 outline-none">
      </div>

      <div>
        <label for="issue" class="block text-sm font-medium mb-1">Issue Category</label>
        <select id="issue" class="w-full px-4 py-2 rounded-lg border border-gray-300 text-gray-800 focus:ring-2 focus:ring-green-400 outline-none">
          <option>Login Problem</option>
          <option>Branch Information Update</option>
          <option>Account or Access Issue</option>
          <option>System Error or Bug</option>
          <option>Other Concern</option>
        </select>
      </div>

      <div>
        <label for="message" class="block text-sm font-medium mb-1">Message</label>
        <textarea id="message" rows="4" placeholder="Describe your concern..." class="w-full px-4 py-2 rounded-lg border border-gray-300 text-gray-800 focus:ring-2 focus:ring-green-400 outline-none"></textarea>
      </div>

      <button type="submit" class="w-full bg-green-600 hover:bg-green-700 transition font-semibold py-2.5 rounded-lg">
        Submit Request
      </button>
    </form>
  </section>

  <!-- Quick Links -->
  <div class="mt-8 text-center text-gray-200 text-sm">
    <p class="mb-2">Quick Links:</p>
    <div class="flex flex-wrap justify-center gap-3">
      <a href="index.php" class="hover:underline">Home</a>
      <a href="login.php" class="hover:underline">Login Page</a>
      <a href="branches.php" class="hover:underline">Branch Directory</a>

    </div>
  </div>

  <!-- Back to Portal -->
  <a href="index.php" class="fixed bottom-6 right-6 bg-green-600 hover:bg-green-700 p-4 rounded-full shadow-lg">
    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
    </svg>
  </a>

  <!-- Footer -->
  <footer class="mt-10 text-xs text-gray-400">
    © 2025 North Park Noodle House Inc. | Employee Support Center
  </footer>
</body>
</html>
