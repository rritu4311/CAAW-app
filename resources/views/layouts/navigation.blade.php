<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Responsive Header</title>


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

/* User Box Styles */
.user-box {
 display: inline-flex;
 align-items: center;
 padding: 6px 12px;
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

 <li>
 <a href="{{ route('workspaces.share-index') }}">Share Workspace</a>
 </li>

 <li>
 <a href="{{ route('projects.share-index') }}">Share Project</a>
 </li>

 </ul>

 <!-- Notifications -->
 @include('components.notification-icon')

 <!-- Dropdown -->
 <div class="dropdown" id="dropdown">
 <a href="#" id="dropdownBtn" class="user-box">
 <img src="{{ Auth::user()->gravatar }}" alt="{{ Auth::user()->name }}" class="w-8 h-8 rounded-full object-cover border-2 border-white/30">
 <span class="ml-2">{{ Auth::user()->name }}</span>
 </a>

 <div class="dropdown-menu">
 <a href="{{ route('profile.edit') }}">Profile</a>
 <form method="POST" action="{{ route('logout') }}" class="inline">
 @csrf
 <button type="submit" class="w-full text-left px-3 py-2 text-white hover:bg-gray-700 rounded">Logout</button>
 </form>
 </div>
 </div>

 <div class="menu-toggle" id="menuToggle">&#9776;</div>
 </nav>
</header>

<script>
// Existing dropdown functionality
const dropdown = document.getElementById('dropdown');
const dropdownBtn = document.getElementById('dropdownBtn');
const navLinks = document.getElementById('navLinks');
const menuToggle = document.getElementById('menuToggle');

dropdownBtn.addEventListener('click', (e) => {
 e.preventDefault();
 dropdown.classList.toggle('active');
});

// Mobile menu toggle
menuToggle.addEventListener('click', () => {
 navLinks.classList.toggle('active');
});

// Close dropdown when clicking outside
document.addEventListener('click', (e) => {
 if (!dropdown.contains(e.target)) {
 dropdown.classList.remove('active');
 }
});
</script>



</body>
</html>