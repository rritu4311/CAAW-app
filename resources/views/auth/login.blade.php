<!DOCTYPE html>
<html lang="en">
<head>
 <meta charset="UTF-8">
 <meta name="viewport" content="width=device-width, initial-scale=1.0">

 <title>{{ config('app.name', 'Laravel') }}</title>

 <!-- Bootstrap CSS -->
 <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" 
 rel="stylesheet">

 <!-- Font Awesome (for eye icon) -->
 <link rel="stylesheet" 
 href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

</head>
<div class="container-fluid vh-100 p-0">

 <div class="row g-0 h-100">

 <!-- LEFT SIDE (IMAGE - 50%) -->
 <div class="col-md-6 h-100">
 <img src="https://ied.eu/wp-content/uploads/2018/10/ERP-Business-Intelligence-Wallpaper.png"
 class="w-100 h-100"
 style="object-fit: cover;">
 </div>

 <!-- RIGHT SIDE (FORM - 50%) -->
 <div class="col-md-6 d-flex align-items-center justify-content-center bg-white dark:bg-gray-800 h-100">

 <div style="width: 100%; max-width: 420px;">

 <!-- TITLE -->
 <h2 class="text-center fw-bold mb-2">CAAW</h2>
 <p class="text-center text-muted mb-4">
 Content & Asset Approval Workflow Tool
 </p>

 <form method="POST" action="{{ route('login') }}">
 @csrf

 <!-- EMAIL OR USERNAME -->
 <div class="mb-3">
 <label class="form-label">Username or Email</label>
 <input type="text" name="email"
 class="form-control form-control-lg"
 placeholder="Enter your username or email" required>
 </div>

 <!-- PASSWORD -->
 <div class="mb-3">
 <label class="form-label">Password</label>
 <div class="input-group">
 <input id="password" type="password" name="password"
 class="form-control form-control-lg"
 placeholder="Enter your password" required>
 <button type="button" onclick="togglePassword()" class="btn btn-outline-secondary">
 <i id="eye-icon" class="fa fa-eye"></i>
 </button>
 </div>
 </div>

 <!-- REMEMBER + FORGOT -->
 <div class="d-flex justify-content-between align-items-center mb-4">
 <div class="form-check">
 <input class="form-check-input" type="checkbox" name="remember">
 <label class="form-check-label">
 Remember me
 </label>
 </div>

 <a href="{{ route('password.request') }}" class="text-decoration-none">
 Forgot password?
 </a>
 </div>

 <!-- BUTTON -->
 <button type="submit" class="btn btn-danger w-100 py-3 fw-semibold">
 Login
 </button>

 </form>

 <!-- REGISTER -->
 <p class="mt-4 text-center">
 Don't have an account?
 <a href="{{ route('register') }}" class="fw-semibold text-decoration-none">
 Create one
 </a>
 </p>

 </div>

 </div>

 </div>

</div>

<script>
function togglePassword() {
 const password = document.getElementById("password");
 const icon = document.getElementById("eye-icon");

 if (password.type === "password") {
 password.type = "text";
 icon.classList.replace("fa-eye", "fa-eye-slash");
 } else {
 password.type = "password";
 icon.classList.replace("fa-eye-slash", "fa-eye");
 }
}
</script>