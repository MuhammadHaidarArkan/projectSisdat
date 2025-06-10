<?php
session_start();
include 'config/db.php';

$login_error = '';
$signup_error = '';
$login_role = 'user';
$form_view = 'login';

// Logout
if (isset($_GET['logout'])) {
    session_destroy();
    header('Location: index.php');
    exit;
}

// Fungsi login
function validate_login($koneksi, $username, $password, $role) {
    $stmt = $koneksi->prepare("SELECT * FROM User WHERE username = ? AND role = ?");
    $stmt->bind_param("ss", $username, $role);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows == 1) {
        $user = $result->fetch_assoc();
        // Untuk sekarang belum pakai hashing
        if ($password === $user['password']) {
            $_SESSION['id_user'] = $user['id_user'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['role'] = $user['role'];
            return true;
        }
    }
    return false;
}

// Form handler
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        if ($_POST['action'] === 'login') {
            $login_role = ($_POST['role'] === 'admin') ? 'admin' : 'user';
            $username = trim($_POST['username']);
            $password = $_POST['password'];

            if ($username === '' || $password === '') {
                $login_error = 'Username dan password wajib diisi.';
            } elseif (validate_login($koneksi, $username, $password, $login_role)) {
                $_SESSION['logged_in'] = true;
                if ($login_role === 'admin') {
                    header('Location: adminDashboard.php');
                } else {
                    header('Location: userDashboard.php');
                }
                exit;
            } else {
                $login_error = 'Username atau password salah.';
            }
            $form_view = 'login';
        }

        if ($_POST['action'] === 'signup') {
            $new_username = trim($_POST['new_username']);
            $new_password = $_POST['new_password'];
            $confirm_password = $_POST['confirm_password'];

            if ($new_username === '' || $new_password === '' || $confirm_password === '') {
                $signup_error = 'Semua field wajib diisi.';
            } elseif ($new_password !== $confirm_password) {
                $signup_error = 'Password tidak cocok.';
            } else {
                $cek = $koneksi->prepare("SELECT * FROM User WHERE username = ?");
                $cek->bind_param("s", $new_username);
                $cek->execute();
                $result = $cek->get_result();
                if ($result->num_rows > 0) {
                    $signup_error = 'Username sudah terdaftar.';
                } else {
                    $id_user = uniqid();
                    $role = 'user';
                    $stmt = $koneksi->prepare("INSERT INTO User (id_user, username, password, role) VALUES (?, ?, ?, ?)");
                    $stmt->bind_param("ssss", $id_user, $new_username, $new_password, $role);
                    $stmt->execute();

                    $_SESSION['logged_in'] = true;
                    $_SESSION['username'] = $new_username;
                    $_SESSION['role'] = $role;
                    $_SESSION['id_user'] = $id_user;
                    header('Location: userDashboard.php');
                    exit;
                }
            }
            $form_view = 'signup';
        }
    }
}
?>
<!-- HTML-nya tetap sama seperti sebelumnya (form login & signup) -->
<!-- Bisa langsung dipakai dari kode kamu sebelumnya -->

?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Room Booking System - Login</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
  <style>
    /* Default Design Guidelines */
    body {
      font-family: 'Poppins', sans-serif;
      background-color: #ffffff;
      color: #6b7280;
      padding-top: 72px;
      min-height: 100vh;
    }
    header {
      position: fixed;
      top: 0;
      width: 100%;
      height: 72px;
      background: #fff;
      box-shadow: 0 2px 8px rgba(0,0,0,0.05);
      display: flex;
      align-items: center;
      justify-content: space-between;
      padding: 0 2rem;
      user-select: none;
      z-index: 1000;
    }
    .logo {
      font-weight: 800;
      font-size: 1.75rem;
      color: #2563eb;
      letter-spacing: 0.05em;
    }
    main {
      max-width: 430px;
      margin: 4rem auto 6rem;
      background: #fff;
      border-radius: 0.75rem;
      padding: 3rem 3.5rem;
      box-shadow: 0 10px 25px rgba(0,0,0,0.07);
      user-select: none;
    }
    h1 {
      font-weight: 800;
      font-size: 2.75rem;
      color: #111827;
      margin-bottom: 0.75rem;
      user-select: text;
    }
    p.subtitle {
      font-weight: 400;
      font-size: 1.125rem;
      margin-bottom: 2rem;
      user-select: text;
    }
    .role-buttons {
      display: flex;
      gap: 1rem;
      justify-content: center;
      margin-bottom: 1.5rem;
      user-select: none;
    }
    .role-button {
      padding: 0.5rem 1.75rem;
      font-weight: 600;
      font-size: 1rem;
      border-radius: 0.75rem;
      border: 2px solid #d1d5db;
      background-color: #f9fafb;
      color: #374151;
      cursor: pointer;
      user-select: none;
      transition: background-color 0.2s, border-color 0.2s;
    }
    .role-button:hover, .role-button:focus {
      border-color: #2563eb;
      outline: none;
      background-color: #e0e7ff;
    }
    .role-button.active {
      background-color: #2563eb;
      border-color: #2563eb;
      color: white;
      cursor: default;
      user-select: none;
    }
    .error-message {
      color: #dc2626;
      font-weight: 600;
      text-align: center;
      margin-bottom: 1rem;
      user-select: text;
    }
    form label {
      font-weight: 600;
      color: #111827;
      margin-bottom: .3rem;
      display: block;
    }
    form input {
      border-radius: 0.75rem;
      border: 1.5px solid #d1d5db;
      padding-left: 1rem;
      height: 44px;
      font-size: 1rem;
      background-color: #f9fafb;
      transition: border-color 0.2s;
      margin-bottom: 0.75rem;
      width: 100%;
      box-sizing: border-box;
    }
    form input:focus {
      border-color: #2563eb;
      outline: none;
      box-shadow: 0 0 8px rgba(37, 99, 235, 0.25);
    }
    .btn-primary {
      font-weight: 700;
      border-radius: 0.75rem;
      font-size: 1.125rem;
      height: 48px;
      transition: background-color 0.3s;
      user-select: none;
      width: 100%;
    }
    .btn-primary:hover, .btn-primary:focus {
      background-color: #1d4ed8;
      outline: none;
    }
    .toggle-links {
      margin-top: 1.25rem;
      text-align: center;
      font-size: 0.95rem;
      color: #6b7280;
      user-select: text;
    }
    .toggle-links a {
      font-weight: 600;
      color: #2563eb;
      cursor: pointer;
      text-decoration: none;
      transition: color 0.2s;
    }
    .toggle-links a:hover, .toggle-links a:focus {
      color: #1d4ed8;
      outline: none;
    }
  </style>
  <script>
    document.addEventListener('DOMContentLoaded', function() {
      const loginForm = document.getElementById('login-form');
      const signupForm = document.getElementById('signup-form');
      const signUpLink = document.getElementById('signup-form-link');
      const signInLink = document.getElementById('login-form-link');
      const btnUser = document.getElementById('btn-role-user');
      const btnAdmin = document.getElementById('btn-role-admin');
      const roleInput = document.getElementById('login-role');

      let currentForm = "<?php echo $form_view === 'signup' ? 'signup' : 'login'; ?>";
      let currentRole = "<?php echo $login_role; ?>";

      function showForm(name) {
        if (name === 'login') {
          loginForm.style.display = 'block';
          signupForm.style.display = 'none';
          signInLink.classList.add('active');
          signUpLink.classList.remove('active');
        } else {
          loginForm.style.display = 'none';
          signupForm.style.display = 'block';
          signInLink.classList.remove('active');
          signUpLink.classList.add('active');
        }
      }

      function setRole(role) {
        if (role === 'admin') {
          btnAdmin.classList.add('active');
          btnUser.classList.remove('active');
          roleInput.value = 'admin';
          btnAdmin.setAttribute('aria-pressed', 'true');
          btnUser.setAttribute('aria-pressed', 'false');
        } else {
          btnUser.classList.add('active');
          btnAdmin.classList.remove('active');
          roleInput.value = 'user';
          btnUser.setAttribute('aria-pressed', 'true');
          btnAdmin.setAttribute('aria-pressed', 'false');
        }
      }

      showForm(currentForm);
      setRole(currentRole);

      signUpLink.addEventListener('click', function(e) {
        e.preventDefault();
        showForm('signup');
      });

      signInLink.addEventListener('click', function(e) {
        e.preventDefault();
        showForm('login');
      });

      btnUser.addEventListener('click', () => setRole('user'));
      btnAdmin.addEventListener('click', () => setRole('admin'));
    });
  </script>
</head>
<body>
  <header>
    <div class="logo" aria-label="Room Booking System">RoomBooking</div>
  </header>
  <main>
    <h1>Welcome to Room Booking System</h1>
    <p class="subtitle">Please sign in or create an account to continue booking rooms seamlessly.</p>

    <!-- Login Form -->
    <form id="login-form" method="POST" action="index.php" novalidate>
      <input type="hidden" name="action" value="login" />
      <input type="hidden" id="login-role" name="role" value="user" />

      <div class="role-buttons" role="group" aria-label="Select login role">
        <button type="button" id="btn-role-user" class="role-button active" aria-pressed="true">User Login</button>
        <button type="button" id="btn-role-admin" class="role-button" aria-pressed="false">Admin Login</button>
      </div>

      <?php if ($login_error): ?>
        <div class="error-message" role="alert"><?php echo htmlspecialchars($login_error); ?></div>
      <?php endif; ?>

      <label for="username">Username</label>
      <input type="text" id="username" name="username" required autocomplete="username" value="<?php echo isset($_POST['username']) ? htmlspecialchars($_POST['username']) : ''; ?>" autofocus />

      <label for="password">Password</label>
      <input type="password" id="password" name="password" required autocomplete="current-password" />

      <button type="submit" class="btn btn-primary mt-3">Sign In</button>

      <p class="toggle-links">Don't have an account? <a href="#" id="signup-form-link">Sign Up</a></p>
    </form>

    <!-- Signup Form -->
    <form id="signup-form" method="POST" action="index.php" novalidate style="display:none;">
      <input type="hidden" name="action" value="signup" />

      <?php if ($signup_error): ?>
        <div class="error-message" role="alert"><?php echo htmlspecialchars($signup_error); ?></div>
      <?php endif; ?>

      <label for="new_username">Username</label>
      <input type="text" id="new_username" name="new_username" required autocomplete="username" value="<?php echo isset($_POST['new_username']) ? htmlspecialchars($_POST['new_username']) : ''; ?>" />

      <label for="new_password">Password</label>
      <input type="password" id="new_password" name="new_password" required autocomplete="new-password" />

      <label for="confirm_password">Confirm Password</label>
      <input type="password" id="confirm_password" name="confirm_password" required autocomplete="new-password" />

      <button type="submit" class="btn btn-primary mt-3">Create Account</button>

      <p class="toggle-links">Already have an account? <a href="#" id="login-form-link">Sign In</a></p>
    </form>
  </main>
</body>
</html>

