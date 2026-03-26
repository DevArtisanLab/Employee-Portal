<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>North Park | Register</title>
  <link rel="stylesheet" href="assets/css/register.css">
 <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

  <style>
    .input-group {
      margin-bottom: 18px;
      display: flex;
      flex-direction: column;
      position: relative;
    }

    .input-group label {
      font-weight: 600;
      margin-bottom: 6px;
      color: #333;
    }

    .input-group input {
      padding: 10px 12px;
      font-size: 0.95rem;
      border: 1px solid #ccc;
      border-radius: 6px;
      transition: border-color 0.2s ease;
    }

    .input-group input:focus {
      border-color: #2e8b57;
      outline: none;
    }

    /* Password input container */
.input-group.password-group {
  position: relative;
}

/* ================================================= */
/* ======== ICON ALIGNMENT FIX (ONLY DESIGN) ======= */
/* ================================================= */

/* space for icons */
.dropdown-container input,
.password-group input {
  padding-right: 40px;
}

/* dropdown arrow aligned to input */
.dropdown-container {
  position: relative;
}

.dropdown-arrow {
  position: absolute;
  right: 12px;
  top: 38px;   /* aligns with input field */
  color: #888;
  cursor: pointer;
  z-index: 2;
}

/* password icons aligned to input */
.password-group {
  position: relative;
}

.toggle-password,
.toggle-confirm {
  position: absolute;
  right: 12px;
  top: 38px;   /* aligns with password field */
  cursor: pointer;
  color: #888;
  font-size: 1rem;
  z-index: 2;
}

.toggle-password:hover,
.toggle-confirm:hover {
  color: #2e8b57;
}

/* ================================================= */

/* Optional: change color on hover */
.input-group.password-group i:hover {
  color: #2e8b57;
}

/* ================================================= */
/* ======== ADDED FIX (DO NOT CHANGE YOUR CODE) ===== */
/* ================================================= */

/* Add space for icons inside inputs */
.dropdown-container input,
.password-group input {
  padding-right: 40px;
}

/* Align dropdown arrow */
.dropdown-arrow {
  position: absolute;
  right: 12px;
  top: 50%;
  transform: translateY(-50%);
  color: #888;
  cursor: pointer;
  z-index: 2;
}

/* Align password icons */
.toggle-password,
.toggle-confirm {
  position: absolute;
  right: 12px;
  top: 50%;
  transform: translateY(-50%);
  cursor: pointer;
  z-index: 2;
}

/* ================================================= */

    /* Error state */
    .input-error {
      border-color: red !important;
    }

    .error-text {
      color: red;
      font-size: 0.85rem;
      margin-top: 5px;
      display: none;
    }

    .error-text.active {
      display: block;
    }

    .dropdown-container {
      position: relative;
    }

    .dropdown-menu {
      display: none;
      position: absolute;
      width: 100%;
      background: #fff;
      border: 1px solid #ccc;
      border-radius: 5px;
      margin-top: 2px;
      list-style: none;
      max-height: 150px;
      overflow-y: auto;
      z-index: 10;
      padding: 0;
    }

    .dropdown-menu.active {
      display: block;
    }

    .dropdown-menu li {
      padding: 8px 12px;
      cursor: pointer;
    }

    .dropdown-menu li:hover {
      background: #f0f0f0;
    }

    .btn-next, .btn-prev, .btn-register {
      background-color: #2e8b57;
      color: white;
      border: none;
      padding: 10px 16px;
      border-radius: 5px;
      cursor: pointer;
      font-size: 0.95rem;
      transition: background 0.2s;
    }

    .btn-next:hover, .btn-prev:hover, .btn-register:hover {
      background-color: #256f45;
    }

    .button-group {
      display: flex;
      justify-content: space-between;
      gap: 10px;
      margin-top: 10px;
    }

    @media (max-width: 768px) {
      .register-form {
        margin-top: 50px;
      }
    }
  </style>
</head>
<body>


<div class="register-container">
  <!-- Left Image -->
  <div class="register-image">
    <img src="assets/images/bg-register.jpg" alt="Registration Image">
  </div>

  <!-- Right Form -->
  <div class="register-form">
    <div class="form-box">
      <h2>Employee Registration</h2>
      <p class="subtitle">Step <span id="stepNumber">1</span> of 2</p>

      <form action="config/register_process.php" method="POST" id="registerForm">
        <!-- STEP 1 -->
        <div class="form-step active" id="step1">
          <div class="input-group">
            <label for="full_name">Full Name</label>
            <input type="text" name="full_name" id="full_name" placeholder="Enter your full name"
              oninput="this.value = this.value.replace(/[^a-zA-Z.\s]/g, '')" required>
          </div>

          <div class="input-group">
            <label for="employee_number">Employee Number</label>
            <input type="text" name="employee_number" id="employee_number" placeholder="Enter your 6-digit employee number"
              maxlength="6" oninput="this.value = this.value.replace(/[^0-9]/g, '')" required>
            <small id="employeeError" style="color: red; display: none;">Employee number must be exactly 6 digits.</small>
          </div>

          <!-- Branch Dropdown -->
          <div class="input-group dropdown">
            <label for="branch">Branch</label>
            <div class="dropdown-container">
              <input type="text" id="branch" name="branch" placeholder="Select or search branch" autocomplete="off" required>
              <i class="fas fa-chevron-down dropdown-arrow"></i>
              <ul id="branchList" class="dropdown-menu">
                <li>Ma Chicken Trinoma</li>
                <li>Kopi Tiam Benitez</li>
                <li>Next Door Texas</li>
                <li>North Park P. Guevarra</li>
                <li>North Park Caltext Macapagal</li>
                <li>North Park Caltex Slex</li>
                <li>North Park Westgate</li>
                <li>North Park Fairview Terraces</li>
                <li>North Park Santana Grove</li>
                <li>North Park Parqal</li>
                <li>North Park SM Fairview</li>
                <li>North Park Banawe</li>
                <li>North Park SM Bicutan</li>
                <li>North Park SM Muntinlupa</li>
                <li>North Park Tomas Morato</li>
                <li>North Park SM Molino</li>
                <li>North Park Convergys</li>
                <li>North Park Hypermarket Antipolo</li>
                <li>North Park Hypermarket Cainta</li>
                <li>North Park Hypermarket EDSA</li>
                <li>North Park Hypermarket FTI</li>
                <li>North Park Hypermarket Makati</li>
                <li>North Park Market Market</li>
                <li>North Park Greenfield</li>
                <li>North Park Ortigas Home Depot</li>
                <li>North Park Paseo De Roces</li>
                <li>North Park SM Dasmarinas</li>
                <li>North Park Eton Centris</li>
                <li>North Park Arnolds</li>
                <li>North Park Marquee Mall</li>
                <li>North Park Valenzuela</li>
                <li>North Park Shell Slex</li>
                <li>North Park SM North Edsa</li>
                <li>North Park Alabang Town Center</li>
                <li>North Park G. Araneta Ave</li>
                <li>North Park SM Mall of Asia (Annex)</li>
                <li>North Park SM Bacoor</li>
                <li>North Park Jetti Macapagal</li>
                <li>North Park Park Square</li>
                <li>North Park Hypermarket Imus</li>
                <li>North Park Hypermarket Antipolo (G/F)</li>
              </ul>
            </div>
            <span class="error-text" id="branchError">Please select a valid branch from the list.</span>
          </div>

          <!-- Position Dropdown -->
          <div class="input-group dropdown">
            <label for="position">Position</label>
            <div class="dropdown-container">
              <input type="text" id="position" name="position" placeholder="Select or search position" autocomplete="off" required>
              <i class="fas fa-chevron-down dropdown-arrow"></i>
              <ul id="positionList" class="dropdown-menu">
                <li>Store Manager</li>
                <li>Assistant Store Manager</li>
                <li>Management Trainee</li>
                <li>Admin Assistant</li>
                <li>Dining Supervisor</li>
                <li>Kitchen Supervisor</li>
                <li>Cashier</li>
                <li>Dining Staff</li>
                <li>Kitchen Staff</li> 
              </ul>
            </div>
            <span class="error-text" id="positionError">Please select a valid position from the list.</span>
          </div>

          <!-- Date Started -->
          <div class="input-group">
            <label for="date_started">Date Started</label>
            <input type="date" name="date_started" id="date_started" required>
            <small id="dateError" style="color: red; display: none;">Year must be 1988 or later.</small>
          </div>

          <button type="button" class="btn-next">Next</button>
        </div>

        <!-- STEP 2 -->
        <div class="form-step" id="step2">
          <div class="input-group">
            <label for="email">Email Address</label>
            <input type="email" name="email" id="email" placeholder="Enter your email" required>
          </div>

          <div class="input-group">
            <label for="username">Username</label>
            <input type="text" name="username" id="username" placeholder="Choose a username" required>
          </div>

          <!-- Password -->
          <div class="input-group password-group">
            <label for="password">Password</label>
            <input type="password" name="password" id="password" placeholder="Enter your password" required>
            <i class="fas fa-eye toggle-password" id="togglePassword"></i>
          </div>

          <div class="input-group password-group">
            <label for="confirm_password">Confirm Password</label>
            <input type="password" name="confirm_password" id="confirm_password" placeholder="Confirm your password" required>
            <i class="fas fa-eye toggle-confirm" id="toggleConfirm"></i>
            <span class="error-text" id="passwordError">Passwords do not match.</span>
          </div>

          <div class="button-group">
            <button type="button" class="btn-prev">Back</button>
            <button type="submit" class="btn-register">Register</button>
          </div>
        </div>
      </form>

      <p class="login-link">
        Already have an account? <a href="login.php">Login</a>
      </p>
    </div>
  </div>
</div>

<script>
  // === PASSWORD TOGGLE ===
  const togglePassword = document.getElementById("togglePassword");
  const toggleConfirm = document.getElementById("toggleConfirm");
  const password = document.getElementById("password");
  const confirmPassword = document.getElementById("confirm_password");

  togglePassword.addEventListener("click", () => {
      password.type = password.type === "password" ? "text" : "password";
      togglePassword.classList.toggle("fa-eye-slash");
  });

  toggleConfirm.addEventListener("click", () => {
      confirmPassword.type = confirmPassword.type === "password" ? "text" : "password";
      toggleConfirm.classList.toggle("fa-eye-slash");
  });

  // === STEP NAVIGATION ===
  const nextBtn = document.querySelector(".btn-next");
  const prevBtn = document.querySelector(".btn-prev");
  const step1 = document.getElementById("step1");
  const step2 = document.getElementById("step2");
  const stepNumber = document.getElementById("stepNumber");

  nextBtn.addEventListener("click", () => {
      const inputs = step1.querySelectorAll("input[required]");
      let valid = true;

      inputs.forEach(input => {
        if (!input.checkValidity()) valid = false;
      });

      const branchList = Array.from(document.querySelectorAll("#branchList li")).map(li => li.textContent.toLowerCase());
      const positionList = Array.from(document.querySelectorAll("#positionList li")).map(li => li.textContent.toLowerCase());

      const branchInput = document.getElementById("branch");
      const positionInput = document.getElementById("position");
      const branchError = document.getElementById("branchError");
      const positionError = document.getElementById("positionError");

      if (!branchList.includes(branchInput.value.toLowerCase())) {
        branchInput.classList.add("input-error");
        branchError.classList.add("active");
        valid = false;
      } else {
        branchInput.classList.remove("input-error");
        branchError.classList.remove("active");
      }

      if (!positionList.includes(positionInput.value.toLowerCase())) {
        positionInput.classList.add("input-error");
        positionError.classList.add("active");
        valid = false;
      } else {
        positionInput.classList.remove("input-error");
        positionError.classList.remove("active");
      }

      if (valid) {
        step1.classList.remove("active");
        step2.classList.add("active");
        stepNumber.textContent = "2";
      }
  });

  prevBtn.addEventListener("click", () => {
      step2.classList.remove("active");
      step1.classList.add("active");
      stepNumber.textContent = "1";
  });

  // === PASSWORD VALIDATION ===
  document.getElementById("registerForm").addEventListener("submit", e => {
      if (password.value !== confirmPassword.value) {
          e.preventDefault();
          password.classList.add("input-error");
          confirmPassword.classList.add("input-error");
          document.getElementById("passwordError").classList.add("active");
      }
  });

  // === DATE VALIDATION ===
  const dateInput = document.getElementById("date_started");
  const dateError = document.getElementById("dateError");

  document.getElementById("registerForm").addEventListener("submit", e => {
      const selectedDate = new Date(dateInput.value);
      if (selectedDate.getFullYear() < 1988) {
          e.preventDefault();
          dateError.style.display = "block";
          dateInput.classList.add("input-error");
          dateInput.focus();
      }
  });

  // === EMPLOYEE NUMBER VALIDATION ===
  const empInput = document.getElementById("employee_number");
  const empError = document.getElementById("employeeError");

  empInput.addEventListener("input", () => {
      if (empInput.value.length < 6) {
          empInput.style.borderColor = "red";
          empError.style.display = "block";
      } else {
          empInput.style.borderColor = "#2e8b57";
          empError.style.display = "none";
      }
  });

  document.getElementById("registerForm").addEventListener("submit", e => {
      if (empInput.value.length !== 6) {
          e.preventDefault();
          empInput.style.borderColor = "red";
          empError.style.display = "block";
          empInput.focus();
      }
  });

  // === DROPDOWN LOGIC ===
  document.querySelectorAll(".dropdown").forEach(dropdown => {
      const input = dropdown.querySelector("input");
      const menu = dropdown.querySelector(".dropdown-menu");
      const items = menu.querySelectorAll("li");
      const arrow = dropdown.querySelector(".dropdown-arrow");
      const errorText = dropdown.querySelector(".error-text");

      const toggleMenu = e => {
        e.stopPropagation();
        menu.classList.toggle("active");
      };

      input.addEventListener("click", toggleMenu);
      arrow.addEventListener("click", toggleMenu);

      input.addEventListener("input", () => {
        const value = input.value.toLowerCase();
        let found = false;
        items.forEach(item => {
          const match = item.textContent.toLowerCase().includes(value);
          item.style.display = match ? "block" : "none";
          if (match) found = true;
        });
        if (!found && input.value.trim() !== "") {
          input.classList.add("input-error");
          if (errorText) errorText.classList.add("active");
        } else {
          input.classList.remove("input-error");
          if (errorText) errorText.classList.remove("active");
        }
      });

      items.forEach(item => {
        item.addEventListener("click", () => {
          input.value = item.textContent;
          input.classList.remove("input-error");
          if (errorText) errorText.classList.remove("active");
          menu.classList.remove("active");
        });
      });

      document.addEventListener("click", e => {
        if (!dropdown.contains(e.target)) menu.classList.remove("active");
      });
  });
</script>
</body>
</html>