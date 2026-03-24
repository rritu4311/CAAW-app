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
    <div class="menu-item">
      <form method="POST" action="{{ route('theme.toggle') }}" class="inline">
        @csrf
        <button type="submit" class="theme-toggle-btn" title="{{ session('theme') === 'dark' ? 'Switch to light mode' : 'Switch to dark mode' }}">
          @if(session('theme') === 'dark')
            <!-- Sun icon for light mode (shown when dark) -->
            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"></path>
            </svg>
          @else
            <!-- Moon icon for dark mode (shown when light) -->
            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"></path>
            </svg>
          @endif
        </button>
      </form>
    </div>

    <!-- Notifications -->
    @include('components.notification-icon')

    <!-- Dropdown -->
    <div class="dropdown" id="dropdown">
      <a href="#" id="dropdownBtn" class="user-box">{{ Auth::user()->name }}</a>

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



</body>
</html>