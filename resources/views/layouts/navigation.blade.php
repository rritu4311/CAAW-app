<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Responsive Header</title>

<!-- Alpine.js -->
<script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

<style>
* {
  margin: 0;
  padding: 0;
  box-sizing: border-box;
  font-family: Arial, sans-serif;
}

header {
  background: #8d99b6;
  color: #fff;
  padding: 15px 20px;
}

/* Dark mode styles */
.dark header {
  background: #0f172a;
}

.navbar {
  display: flex;
  justify-content: space-between;
  align-items: center;
  position: relative;
}

.logo {
  font-size: 22px;
  font-weight: bold;
}

.nav-links {
  list-style: none;
  display: flex;
  gap: 25px;
  align-items: center;
}

.nav-links li {
  list-style: none;
}

.nav-links li a {
  color: white;
  text-decoration: none;
  font-size: 16px;
  transition: 0.3s;
}

.nav-links li a:hover {
  color: #38bdf8;
}

/* Dropdown */
.dropdown {
  position: relative;
}

.dropdown-menu {
  display: none;
  position: absolute;
  background: #1e293b;
  top: 40px;
  right: 0;
  min-width: 150px;
  border-radius: 6px;
  overflow: hidden;
}

.dropdown-menu a {
  display: block;
  padding: 10px;
  color: white;
}

.dropdown-menu a:hover {
  background: #334155;
}

.dropdown.active .dropdown-menu {
  display: block;
}

/* Hamburger */
.menu-toggle {
  display: none;
  font-size: 26px;
  cursor: pointer;
}

/* Theme Toggle Button */
.theme-toggle-btn {
  background: none;
  border: none;
  color: white;
  cursor: pointer;
  padding: 8px;
  border-radius: 4px;
  transition: background-color 0.3s;
}

.theme-toggle-btn:hover {
  background-color: rgba(255, 255, 255, 0.1);
}

.theme-toggle-btn svg {
  width: 20px;
  height: 20px;
}

/* User Box Styles */
.user-box {
  display: inline-block;
  padding: 8px 12px;
  background: rgba(255, 255, 255, 0.1);
  border-radius: 4px;
  border: 1px solid rgba(255, 255, 255, 0.2);
  color: white;
  text-decoration: none;
  transition: background-color 0.3s;
}

.user-box:hover {
  background: rgba(255, 255, 255, 0.2);
}

/* Dark mode styles */
.dark .user-box {
  background: rgba(0, 0, 0, 0.3);
  border-color: rgba(255, 255, 255, 0.3);
  color: #e5e7eb;
}

.dark .user-box:hover {
  background: rgba(0, 0, 0, 0.5);
}

/* Mobile */
@media (max-width: 768px) {

  .nav-links {
    position: absolute;
    top: 60px;
    left: 0;
    width: 100%;
    background: #0f172a;
    flex-direction: column;
    align-items: center;
    display: none;
    gap: 0;
  }

  .nav-links.active {
    display: flex;
  }

  .nav-links li {
    width: 100%;
    text-align: center;
    padding: 12px 0;
  }

  .nav-links li a,
  .menu-item button {
    width: 100%;
    display: block;
  }

  .dropdown-menu {
    position: static;
    width: 100%;
  }

  .menu-toggle {
    display: block;
  }
}
</style>
</head>

<body>

<header>
  <nav class="navbar">
    <div class="logo">CAAW</div>

    <ul class="nav-links" id="navLinks">

      <li>
        <a href="{{ route('dashboard') }}">Dashboard</a>
      </li>

      <li>
        <a href="{{ route('workspaces.page') }}">Workspaces</a>
      </li>

      <li>
        <a href="{{ route('activity.log') }}">Activity Log</a>
      </li>

    </ul>

    <!-- Theme Toggle -->
    <div class="menu-item" x-data="{ isDark: false }" x-init="isDark = document.documentElement.classList.contains('dark')">
      <button @click="toggleTheme()" class="theme-toggle-btn" :title="isDark ? 'Switch to light mode' : 'Switch to dark mode'">
        <!-- Sun icon for light mode (shown when dark) -->
        <svg x-show="isDark" class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"></path>
        </svg>
        <!-- Moon icon for dark mode (shown when light) -->
        <svg x-show="!isDark" class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"></path>
        </svg>
      </button>
    </div>

    <!-- Notifications -->
    @include('components.notification-icon')

    <!-- Dropdown -->
    <div class="dropdown" id="dropdown">
      <a href="#" id="dropdownBtn" class="user-box">{{ Auth::user()->name }}</a>

      <div class="dropdown-menu">
        <a href="{{ route('profile.edit') }}">Profile</a>
        <a href="#" onclick="event.preventDefault(); document.getElementById('logout-form').submit();">Logout</a>
      </div>
    </div>

    <div class="menu-toggle" id="menuToggle">&#9776;</div>
  </nav>
</header>

<!-- Logout Form -->
<form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
    @csrf
</form>

<script>
const toggle = document.getElementById("menuToggle");
const nav = document.getElementById("navLinks");
const dropdown = document.getElementById("dropdown");
const dropdownBtn = document.getElementById("dropdownBtn");

// Initialize theme on page load
document.addEventListener('DOMContentLoaded', () => {
  const savedTheme = localStorage.getItem('theme');
  const prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
  
  if (savedTheme === 'dark' || (!savedTheme && prefersDark)) {
    document.documentElement.classList.add('dark');
  }
});

// Mobile menu toggle
toggle.addEventListener("click", () => {
  nav.classList.toggle("active");
});

// Close menu after click (mobile UX)
document.querySelectorAll(".nav-links a").forEach(link => {
  link.addEventListener("click", () => {
    nav.classList.remove("active");
  });
});

// Close menu when clicking outside (mobile UX)
document.addEventListener("click", (e) => {
  if (!nav.contains(e.target) && !toggle.contains(e.target)) {
    nav.classList.remove("active");
  }
});

// Dropdown toggle
dropdownBtn.addEventListener("click", (e) => {
  e.preventDefault();
  dropdown.classList.toggle("active");
});

// Close dropdown when clicking outside
document.addEventListener("click", (e) => {
  if (!dropdown.contains(e.target)) {
    dropdown.classList.remove("active");
  }
});

// Theme toggle function
function toggleTheme() {
  const html = document.documentElement;
  const isDark = html.classList.contains('dark');
  
  if (isDark) {
    html.classList.remove('dark');
    localStorage.setItem('theme', 'light');
  } else {
    html.classList.add('dark');
    localStorage.setItem('theme', 'dark');
  }
  
  // Update Alpine.js data
  const themeElement = document.querySelector('[x-data*="theme"]');
  if (themeElement && themeElement._x_dataStack) {
    const alpineData = themeElement._x_dataStack[themeElement._x_dataStack.length - 1];
    if (alpineData && alpineData.isDark !== undefined) {
      alpineData.isDark = !isDark;
    }
  }
}
</script>

</body>
</html>