<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>North Park Employee Portal</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <style>
    .fade { transition: opacity 1.5s ease-in-out; }
  </style>
</head>
<body class="relative min-h-screen w-full overflow-hidden text-gray-800 font-sans">

  <!-- Background Slider -->
  <div id="slider" class="absolute inset-0 z-0">
    <img src="assets/images/bg-login.jpg" class="absolute inset-0 w-full h-full object-cover opacity-100 fade" />
    <img src="assets/images/bg-register.jpg" class="absolute inset-0 w-full h-full object-cover opacity-0 fade" />
    <img src="assets/images/bg-landing.jpg" class="absolute inset-0 w-full h-full object-cover opacity-0 fade" />
  </div>

  <!-- Overlay -->
  <div class="absolute inset-0 bg-black/50 z-10"></div>

  <!-- Navbar -->
  <nav class="relative z-20 flex flex-col sm:flex-row justify-between items-center px-4 sm:px-8 py-3 text-white text-center sm:text-left space-y-2 sm:space-y-0">
    <div class="flex items-center justify-center sm:justify-start gap-2 sm:gap-3">
      <img src="assets/images/np-logo.png" alt="North Park Logo" class="w-10 sm:w-12">
      <span class="text-lg sm:text-xl font-semibold leading-tight">North Park Noodle House Inc.</span>
    </div>

    <!-- Desktop Help -->
    <div class="hidden sm:block text-xs sm:text-sm opacity-90">
      <a href="help.php" class="hover:underline">Need Help?</a>
    </div>
  </nav>

  <!-- Main Content -->
  <section class="relative z-20 flex flex-col items-center justify-center text-center px-6 py-10 sm:py-16 h-[calc(100vh-150px)] text-white">
    <h1 class="text-3xl sm:text-5xl font-bold mb-3 sm:mb-4 drop-shadow-lg leading-tight">
      Welcome to the Employee Portal
    </h1>
    <p class="max-w-sm sm:max-w-lg mb-6 sm:mb-8 text-base sm:text-lg opacity-90">
      A streamlined platform designed for evaluating employee skills and knowledge through comprehensive online assessments.
    </p>
    <div class="flex flex-col sm:flex-row gap-3 sm:gap-4 w-full sm:w-auto">
      <a href="login.php" class="w-full sm:w-auto px-6 py-3 bg-green-500 text-white rounded-full font-semibold hover:bg-green-400 transition text-center">
        Login
      </a>
      <a href="branches.php" class="w-full sm:w-auto px-6 py-3 border border-white text-white rounded-full hover:bg-white hover:text-black transition text-center">
        North Park Branches
      </a>
    </div>
  </section>

  <!-- Mobile Help Icon -->
  <a href="help.php" 
     class="fixed bottom-5 right-5 bg-green-600 hover:bg-green-700 text-white p-4 rounded-full shadow-lg sm:hidden z-30 transition">
    <!-- Question mark icon -->
    <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
        d="M12 18h.01M12 2a10 10 0 100 20 10 10 0 000-20zm0 8a2 2 0 00-2 2h2v2h2v-2a2 2 0 00-2-2z" />
    </svg>
  </a>

  <!-- Footer -->
  <footer class="relative z-20 text-center text-xs sm:text-sm text-gray-300 py-4 sm:py-6">
    © 2025 North Park Noodle House Inc. | Employee Portal
  </footer>

  <!-- Background Slider Script -->
  <script>
    const slides = document.querySelectorAll('#slider img');
    let current = 0;

    function changeSlide() {
      slides[current].style.opacity = 0;
      current = (current + 1) % slides.length;
      slides[current].style.opacity = 1;
    }

    setInterval(changeSlide, 6000); // Change every 6 seconds
  </script>

</body>
</html>
