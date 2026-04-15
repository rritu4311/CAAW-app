<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>{{ config('app.name', 'Laravel') }} - Register</title>

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" 
          rel="stylesheet">

    <!-- Font Awesome (for eye icon) -->
    <link rel="stylesheet" 
          href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

</head>
<body class="bg-light">
<div class="container-fluid vh-100 p-0">

    <div class="row g-0 h-100">

        <!-- LEFT SIDE (IMAGE - 50%) -->
        <div class="col-md-6 h-100">
            <img src="https://ied.eu/wp-content/uploads/2018/10/ERP-Business-Intelligence-Wallpaper.png"
                 class="w-100 h-100"
                 style="object-fit: cover;">
        </div>

        <!-- RIGHT SIDE (FORM - 50%) -->
        <div class="col-md-6 d-flex align-items-center justify-content-center bg-white h-100">

            <div style="width: 100%; max-width: 420px;">

                <!-- TITLE -->
                <h2 class="text-center fw-bold mb-2">CAAW</h2>
                <p class="text-center text-muted mb-4">
                    Create Your Account
                </p>

                <form method="POST" action="{{ route('register') }}">
                    @csrf

                    <!-- NAME -->
                    <div class="mb-3">
                        <label class="form-label">Name</label>
                        <input type="text" name="name" value="{{ old('name') }}"
                               class="form-control form-control-lg @error('name') is-invalid @enderror"
                               placeholder="Enter your name" required autofocus autocomplete="name">
                        @error('name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- USERNAME -->
                    <div class="mb-3">
                        <label class="form-label">Username</label>
                        <input type="text" name="username" value="{{ old('username') }}"
                               class="form-control form-control-lg @error('username') is-invalid @enderror"
                               placeholder="Enter your username" required autocomplete="username">
                        @error('username')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- EMAIL -->
                    <div class="mb-3">
                        <label class="form-label">Email</label>
                        <input type="email" name="email" value="{{ old('email') }}"
                               class="form-control form-control-lg @error('email') is-invalid @enderror"
                               placeholder="Enter your email" required autocomplete="username">
                        @error('email')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- PASSWORD -->
                    <div class="mb-3">
                        <label class="form-label">Password</label>
                        <div class="input-group">
                            <input id="password" type="password" name="password"
                                   class="form-control form-control-lg @error('password') is-invalid @enderror"
                                   placeholder="Enter your password" required autocomplete="new-password">
                            <button type="button" onclick="togglePassword('password', 'eye-icon')" class="btn btn-outline-secondary">
                                <i id="eye-icon" class="fa fa-eye"></i>
                            </button>
                        </div>
                        @error('password')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- CONFIRM PASSWORD -->
                    <div class="mb-4">
                        <label class="form-label">Confirm Password</label>
                        <div class="input-group">
                            <input id="password_confirmation" type="password" name="password_confirmation"
                                   class="form-control form-control-lg"
                                   placeholder="Confirm your password" required autocomplete="new-password">
                            <button type="button" onclick="togglePassword('password_confirmation', 'eye-icon-confirm')" class="btn btn-outline-secondary">
                                <i id="eye-icon-confirm" class="fa fa-eye"></i>
                            </button>
                        </div>
                    </div>

                    <!-- BUTTON -->
                    <button type="submit" class="btn btn-danger w-100 py-3 fw-semibold mb-3">
                        Register
                    </button>

                </form>

                <!-- LOGIN LINK -->
                <p class="text-center mb-0">
                    Already have an account?
                    <a href="{{ route('login') }}" class="fw-semibold text-decoration-none">
                        Login
                    </a>
                </p>

            </div>

        </div>

    </div>

</div>

<script>
function togglePassword(inputId, iconId) {
    const password = document.getElementById(inputId);
    const icon = document.getElementById(iconId);

    if (password.type === "password") {
        password.type = "text";
        icon.classList.replace("fa-eye", "fa-eye-slash");
    } else {
        password.type = "password";
        icon.classList.replace("fa-eye-slash", "fa-eye");
    }
}
</script>

</body>
</html>
