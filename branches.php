<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>North Park Branches | Employee Portal</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <style>
    .fade { transition: opacity 1.5s ease-in-out; }
    .modal {
      position: fixed;
      inset: 0;
      background: rgba(0,0,0,0.7);
      display: flex;
      align-items: center;
      justify-content: center;
      z-index: 50;
    }
  </style>
</head>
<body class="relative min-h-screen w-screen overflow-x-hidden text-gray-800 font-sans">

  <!-- Background -->
  <div class="absolute inset-0 z-0">
    <img src="assets/images/bg-landing.jpg" class="absolute inset-0 w-full h-full object-cover opacity-100 fade" />
  </div>
  <div class="absolute inset-0 bg-black/60 z-10"></div>

  <!-- Navbar -->
  <nav class="relative z-20 flex flex-wrap items-center justify-between px-6 py-4 text-white">
    <div class="flex items-center gap-3">
      <img src="assets/images/np-logo.png" alt="North Park Logo" class="w-10 h-10">
      <span class="text-xl font-semibold">North Park Noodle House Inc.</span>
    </div>
    <div class="flex items-center gap-4 text-sm mt-2 sm:mt-0">
      <a href="index.php" class="hover:underline opacity-80 hover:opacity-100">Home</a>
      <a href="login.php" class="hover:underline opacity-80 hover:opacity-100">Employee Login</a>
    </div>
  </nav>

  <!-- Main Content -->
  <section class="relative z-20 px-4 sm:px-8 py-8 text-white">
    <div class="text-center mb-8">
      <h1 class="text-4xl font-bold drop-shadow-lg mb-2">North Park Branches</h1>
      <p class="text-gray-300 text-lg">Find information about our branches nationwide.</p>
    </div>

    <!-- Search Bar -->
    <div class="max-w-3xl mx-auto mb-8">
      <input id="search" type="text" placeholder="Search branch name or city..." 
        class="w-full p-3 rounded-lg text-gray-900 focus:outline-none focus:ring-2 focus:ring-green-500" />
    </div>

    <!-- Branch List -->
    <div id="branch-list" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6 max-w-6xl mx-auto"></div>

    <!-- Pagination Controls -->
    <div class="flex justify-center items-center gap-4 mt-8">
      <button id="prevBtn" class="bg-yellow-500 hover:bg-yellow-600 px-5 py-2 rounded-lg text-white font-semibold disabled:opacity-50 disabled:cursor-not-allowed">
        Previous
      </button>
      <button id="nextBtn" class="bg-green-600 hover:bg-green-700 px-5 py-2 rounded-lg text-white font-semibold disabled:opacity-50 disabled:cursor-not-allowed">
        Next
      </button>
    </div>
  </section>

  <!-- Footer -->
  <footer class="relative z-20 text-center text-sm text-gray-300 py-6 bg-black/40 backdrop-blur-sm mt-8">
    © 2025 North Park Noodle House Inc. | Employee Portal
  </footer>

  <!-- 🏪 Editable Branch Data -->
  <script id="branch-data" type="application/json">
  [
    {
      "name": "North Park Antiolo",
      "address": "123 Makati Ave, Makati City",
      "contact": "9199146896",
      "hours": {
        "monday":   {"open":"10:00","close":"10:00"},
        "tuesday":  {"open":"10:00","close":"10:00"},
        "wednesday":{"open":"10:00","close":"10:00"},
        "thursday": {"open":"10:00","close":"10:00"},
        "friday":   {"open":"10:00","close":"10:00"},
        "saturday": {"open":"10:00","close":"10:00"},
        "sunday":   {"open":"10:00","close":"10:00"}
      }
    },
    {
      "name": "North Park Greenhills",
      "address": "Annapolis St., Greenhills, San Juan City",
      "contact": "(02) 878-9999",
      "hours": {
        "monday":   {"open":"11:00","close":"20:00"},
        "tuesday":  {"open":"11:00","close":"20:00"},
        "wednesday":{"open":"11:00","close":"20:00"},
        "thursday": {"open":"11:00","close":"20:00"},
        "friday":   {"open":"00:00","close":"23:59"},
        "saturday": {"open":"00:00","close":"23:59"},
        "sunday":   {"open":"00:00","close":"23:59"}
      }
    },
    {
      "name": "North Park SM North EDSA",
      "address": "Upper Ground Level, SM City North EDSA, Quezon City",
      "contact": "(02) 345-6789",
      "hours": {
        "monday":   {"open":"10:00","close":"21:00"},
        "tuesday":  {"open":"10:00","close":"21:00"},
        "wednesday":{"open":"10:00","close":"21:00"},
        "thursday": {"open":"10:00","close":"21:00"},
        "friday":   {"open":"00:00","close":"23:59"},
        "saturday": {"open":"00:00","close":"23:59"},
        "sunday":   {"open":"00:00","close":"23:59"}
      }
    },
    {
      "name": "North Park Makati Avenue",
      "address": "123 Makati Ave, Makati City",
      "contact": "(02) 812-3456",
      "hours": {
        "monday":   {"open":"09:00","close":"22:00"},
        "tuesday":  {"open":"09:00","close":"22:00"},
        "wednesday":{"open":"09:00","close":"22:00"},
        "thursday": {"open":"09:00","close":"22:00"},
        "friday":   {"open":"00:00","close":"23:59"},
        "saturday": {"open":"00:00","close":"23:59"},
        "sunday":   {"open":"00:00","close":"23:59"}
      }
    },
    {
      "name": "North Park Greenhills",
      "address": "Annapolis St., Greenhills, San Juan City",
      "contact": "(02) 878-9999",
      "hours": {
        "monday":   {"open":"11:00","close":"20:00"},
        "tuesday":  {"open":"11:00","close":"20:00"},
        "wednesday":{"open":"11:00","close":"20:00"},
        "thursday": {"open":"11:00","close":"20:00"},
        "friday":   {"open":"00:00","close":"23:59"},
        "saturday": {"open":"00:00","close":"23:59"},
        "sunday":   {"open":"00:00","close":"23:59"}
      }
    },
    {
      "name": "North Park SM North EDSA",
      "address": "Upper Ground Level, SM City North EDSA, Quezon City",
      "contact": "(02) 345-6789",
      "hours": {
        "monday":   {"open":"10:00","close":"21:00"},
        "tuesday":  {"open":"10:00","close":"21:00"},
        "wednesday":{"open":"10:00","close":"21:00"},
        "thursday": {"open":"10:00","close":"21:00"},
        "friday":   {"open":"00:00","close":"23:59"},
        "saturday": {"open":"00:00","close":"23:59"},
        "sunday":   {"open":"00:00","close":"23:59"}
      }
    },
    {
      "name": "North Park Makati Avenue",
      "address": "123 Makati Ave, Makati City",
      "contact": "(02) 812-3456",
      "hours": {
        "monday":   {"open":"09:00","close":"22:00"},
        "tuesday":  {"open":"09:00","close":"22:00"},
        "wednesday":{"open":"09:00","close":"22:00"},
        "thursday": {"open":"09:00","close":"22:00"},
        "friday":   {"open":"00:00","close":"23:59"},
        "saturday": {"open":"00:00","close":"23:59"},
        "sunday":   {"open":"00:00","close":"23:59"}
      }
    },
    {
      "name": "North Park Greenhills",
      "address": "Annapolis St., Greenhills, San Juan City",
      "contact": "(02) 878-9999",
      "hours": {
        "monday":   {"open":"11:00","close":"20:00"},
        "tuesday":  {"open":"11:00","close":"20:00"},
        "wednesday":{"open":"11:00","close":"20:00"},
        "thursday": {"open":"11:00","close":"20:00"},
        "friday":   {"open":"00:00","close":"23:59"},
        "saturday": {"open":"00:00","close":"23:59"},
        "sunday":   {"open":"00:00","close":"23:59"}
      }
    },
    {
      "name": "North Park SM North EDSA",
      "address": "Upper Ground Level, SM City North EDSA, Quezon City",
      "contact": "(02) 345-6789",
      "hours": {
        "monday":   {"open":"10:00","close":"21:00"},
        "tuesday":  {"open":"10:00","close":"21:00"},
        "wednesday":{"open":"10:00","close":"21:00"},
        "thursday": {"open":"10:00","close":"21:00"},
        "friday":   {"open":"00:00","close":"23:59"},
        "saturday": {"open":"00:00","close":"23:59"},
        "sunday":   {"open":"00:00","close":"23:59"}
      }
    }
  ]
  </script>

  <!-- ⚙️ Main Script -->
  <script>
    const branches = JSON.parse(document.getElementById('branch-data').textContent);
    const container = document.getElementById('branch-list');
    const prevBtn = document.getElementById('prevBtn');
    const nextBtn = document.getElementById('nextBtn');
    const perPage = 6;
    let currentPage = 1;
    const totalPages = Math.ceil(branches.length / perPage);
    const days = ["sunday","monday","tuesday","wednesday","thursday","friday","saturday"];

    // Get today's hours
    function getTodayHours(branch) {
      const today = days[new Date().getDay()];
      return branch.hours[today];
    }

    // Status logic
    function getBranchStatus(openTime, closeTime) {
      const now = new Date();
      const current = now.getHours() * 60 + now.getMinutes();
      const [openH, openM] = openTime.split(':').map(Number);
      const [closeH, closeM] = closeTime.split(':').map(Number);
      const openMins = openH * 60 + openM;
      const closeMins = closeH * 60 + closeM;

      if (openTime === "00:00" && closeTime === "23:59")
        return { text: "Open 24 Hours", color: "bg-green-600" };

      if (current >= openMins && current <= closeMins) {
        const hoursLeft = closeMins - current;
        if (hoursLeft < 60) return { text: "Closing Soon", color: "bg-yellow-500" };
        return { text: "Open", color: "bg-green-600" };
      }
      return { text: "Closed", color: "bg-red-600" };
    }

    // Render branch cards
    function renderBranches(branchArray = branches) {
      container.innerHTML = "";
      const start = (currentPage - 1) * perPage;
      const end = start + perPage;
      const visibleBranches = branchArray.slice(start, end);

      visibleBranches.forEach((branch, index) => {
        const todayHours = getTodayHours(branch);
        const status = getBranchStatus(todayHours.open, todayHours.close);

        const card = document.createElement("div");
        card.className = "bg-white/10 backdrop-blur-md p-5 rounded-2xl shadow-lg border border-white/20";
        card.innerHTML = `
          <h2 class="text-xl font-semibold">${branch.name}</h2>
          <p class="text-gray-300 text-sm mt-2">${branch.address}</p>
          <p class="text-gray-300 text-sm mt-1">Contact: ${branch.contact}</p>
          <p class="text-gray-400 text-xs mt-1">Today: ${todayHours.open} - ${todayHours.close}</p>
          <span class="inline-block mt-3 px-3 py-1 ${status.color} text-white text-xs font-semibold rounded-full">${status.text}</span>
          <button class="mt-4 bg-blue-500 hover:bg-blue-600 px-3 py-1 rounded-lg text-white text-xs" onclick="openModal(${start + index})">
            View Store Hours
          </button>
        `;
        container.appendChild(card);
      });

      prevBtn.disabled = currentPage === 1;
      nextBtn.disabled = currentPage === totalPages;
    }

    // Pagination
    prevBtn.addEventListener("click", () => {
      if (currentPage > 1) { currentPage--; renderBranches(); }
    });

    nextBtn.addEventListener("click", () => {
      if (currentPage < totalPages) { currentPage++; renderBranches(); }
    });

    // 🔍 Search
    const searchInput = document.getElementById("search");
    searchInput.addEventListener("input", () => {
      const term = searchInput.value.toLowerCase();
      const filtered = branches.filter(b =>
        b.name.toLowerCase().includes(term) || b.address.toLowerCase().includes(term)
      );
      currentPage = 1;
      renderBranches(filtered);
    });

    // 🕓 Modal for store hours
    function openModal(index) {
      const branch = branches[index];
      const modal = document.createElement("div");
      modal.className = "modal";
      modal.innerHTML = `
        <div class="bg-white rounded-xl p-6 w-80 text-gray-800 shadow-xl relative">
          <h2 class="text-lg font-semibold mb-4 text-center">${branch.name}</h2>
          <table class="w-full text-sm mb-4">
            ${Object.entries(branch.hours).map(([day, hours]) => `
              <tr>
                <td class="capitalize font-medium py-1">${day}</td>
                <td class="text-right">${hours.open} - ${hours.close}</td>
              </tr>
            `).join("")}
          </table>
          <button onclick="this.closest('.modal').remove()" class="w-full bg-red-500 hover:bg-red-600 text-white py-2 rounded-lg font-medium">Close</button>
        </div>
      `;
      document.body.appendChild(modal);
    }

    renderBranches();
  </script>
</body>
</html>
