<?php
session_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>SMART_TEST - Welcome</title>

  <style>
    /* --- CSS BILLKUL SAME HAI --- */
    * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
    body { min-height: 100vh; display: flex; flex-direction: column; justify-content: center; align-items: center; background: #1d1f2a; color: #f0f0f0; }

    .greeting { font-size: 24px; text-align: center; color: #eddc9aa1; margin-bottom: 100px; letter-spacing: 1px; }

    .container { width: 90%; max-width: 450px; background: #1f1f1f; padding: 40px 30px; border-radius: 12px; text-align: center; box-shadow: 0 8px 30px rgba(0,0,0,0.6); }

    h1 { font-size: 28px; color: #00b894; margin-bottom: 30px; }

    .btn { width: 100%; padding: 14px; margin: 12px 0; border-radius: 10px; border: none; font-size: 16px; cursor: pointer; font-weight: 600; transition: 0.3s; }
    .btn-login { background: #00b894; color: #fff; }
    .btn-register { background: transparent; border: 2px solid #00b894; color: #00b894; }
    .btn-login:hover { background: #019e7e; }
    .btn-register:hover { background: #00b894; color: #fff; }

    /* Modal */
    .modal { display: none; position: fixed; top:0; left:0; width:100%; height:100%; background: rgba(0,0,0,0.8); justify-content:center; align-items:center; z-index:10; }
    .modal-content { width:90%; max-width:400px; background:#1f1f1f; padding:30px 25px; border-radius:12px; position:relative; box-shadow:0 6px 20px rgba(0,0,0,0.5); }
    .close { position:absolute; top:12px; right:18px; font-size:22px; cursor:pointer; color:#fff; }

    .modal-content h2 { color:#00b894; margin-bottom:20px; text-align:center; }

    input[type="text"], input[type="password"], input[type="email"], input[type="tel"] {
      width:100%; padding:12px; margin:10px 0; border-radius:8px; border:1px solid #333; background:#2c2c2c; color:#fff;
    }

    .password-wrapper { position:relative; width:100%; }
    .password-wrapper input { padding-right:45px; }
    .toggle-password {
      position:absolute; top:50%; right:12px;
      transform:translateY(-50%); cursor:pointer;
      font-size:18px; color:#bbb;
    }

    button[type="submit"] {
      width:100%; padding:14px; margin-top:12px;
      border:none; border-radius:8px;
      background:#00b894; color:#fff; font-weight:600; cursor:pointer;
    }
    button[type="submit"]:hover { background:#019e7e; }

    @media(max-width:480px) {
      .container { padding:30px 20px; }
      h1 { font-size:24px; }
      .greeting { font-size:16px; }
    }
  </style>
</head>

<body>

<?php
if(isset($_SESSION['status'])) {
    ?>
    <script>
        alert('<?php echo $_SESSION['status']; ?>');
    </script>
    <?php
    unset($_SESSION['status']);
}
?>

<div class="greeting">
  ᴡᴇʟᴄᴏᴍᴇ ᴛᴏ sᴍᴀʀᴛ_ᴛᴇsᴛ !! ʜᴀᴠᴇ ʙᴇsᴛ ᴡɪsʜᴇs ғᴏʀ ʏᴏᴜ ✨
</div>

<div class="container">
  <h1>SMART_TEST</h1>
  <button class="btn btn-login" id="loginBtn">Login</button>
  <button class="btn btn-register" id="registerBtn">Create New Account</button>
</div>


<div class="modal" id="loginModal">
  <div class="modal-content">
    <span class="close" id="loginClose">&times;</span>
    <h2>Login</h2>

    <form action="auth_code.php" method="POST">
      <input type="text" name="email_or_phone" placeholder="Email or Phone Number" required>

      <div class="password-wrapper">
        <input type="password" id="loginPassword" name="password" placeholder="Password" required>
        <span class="toggle-password" onclick="togglePassword('loginPassword', this)">👁</span>
      </div>

      <button type="submit" name="login_btn">Login</button>

      <p style="margin-top:10px; text-align:center;">
        <a href="#" id="forgotPassBtn" style="color:#00b894;">Forgot Password?</a>
      </p>
    </form>
    </div>
</div>


<div class="modal" id="forgotModal">
  <div class="modal-content">
    <span class="close" id="forgotClose">&times;</span>
    <h2>Reset Password</h2>

    <form action="auth_code.php" method="POST">
      <input type="text" name="reset_email" placeholder="Enter Email or Phone Number" required>

      <div class="password-wrapper">
        <input type="password" id="newPassword" name="new_password" placeholder="New Password" required>
        <span class="toggle-password" onclick="togglePassword('newPassword', this)">👁</span>
      </div>

      <div class="password-wrapper">
        <input type="password" id="newConfirmPassword" name="confirm_new_password" placeholder="Confirm New Password" required>
        <span class="toggle-password" onclick="togglePassword('newConfirmPassword', this)">👁</span>
      </div>

      <button type="submit" name="reset_btn">Reset Password</button>
    </form>

  </div>
</div>


<div class="modal" id="registerModal">
  <div class="modal-content">
    <span class="close" id="registerClose">&times;</span>
    <h2>Create Account</h2>

    <form action="auth_code.php" method="POST">
      <input type="text" name="full_name" placeholder="Full Name" required>
      <input type="tel" name="phone" placeholder="Phone Number" required>
      <input type="email" name="email" placeholder="Email" required>

      <div class="password-wrapper">
        <input type="password" id="regPassword" name="password" placeholder="Password" required>
        <span class="toggle-password" onclick="togglePassword('regPassword', this)">👁</span>
      </div>

      <div class="password-wrapper">
        <input type="password" id="regConfirmPassword" name="confirm_password" placeholder="Confirm Password" required>
        <span class="toggle-password" onclick="togglePassword('regConfirmPassword', this)">👁</span>
      </div>

      <button type="submit" name="register_btn">Register</button>
    </form>
    </div>
</div>


<script>
/* Open & Close Modals */
const loginBtn = document.getElementById('loginBtn');
const registerBtn = document.getElementById('registerBtn');
const forgotPassBtn = document.getElementById('forgotPassBtn');

const loginModal = document.getElementById('loginModal');
const registerModal = document.getElementById('registerModal');
const forgotModal = document.getElementById('forgotModal');

const loginClose = document.getElementById('loginClose');
const registerClose = document.getElementById('registerClose');
const forgotClose = document.getElementById('forgotClose');

loginBtn.onclick = () => loginModal.style.display = "flex";
registerBtn.onclick = () => registerModal.style.display = "flex";
forgotPassBtn.onclick = () => forgotModal.style.display = "flex";

loginClose.onclick = () => loginModal.style.display = "none";
registerClose.onclick = () => registerModal.style.display = "none";
forgotClose.onclick = () => forgotModal.style.display = "none";

/* Close modal on outside click */
window.onclick = (e) => {
  if (e.target === loginModal) loginModal.style.display = "none";
  if (e.target === registerModal) registerModal.style.display = "none";
  if (e.target === forgotModal) forgotModal.style.display = "none";
};

/* Eye Toggle */
function togglePassword(id, icon){
  const input = document.getElementById(id);
  if(input.type === "password"){
    input.type = "text";
    icon.textContent = "🙈";
  } else {
    input.type = "password";
    icon.textContent = "👁";
  }
}
</script>

</body>
</html>