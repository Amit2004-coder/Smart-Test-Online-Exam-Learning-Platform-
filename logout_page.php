<?php
session_start();

// 1. Logout Logic: Session ko destroy kar do
if(isset($_SESSION['auth']))
{
    unset($_SESSION['auth']);
    unset($_SESSION['auth_user']);
    session_destroy();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Logged Out - SMART TEST</title>
<style>
  /* --- SAME CSS (NO CHANGES) --- */
  * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
  body { background: #1d1f2a; color: #f0f0f0; height: 100vh; display: flex; justify-content: center; align-items: center; text-align: center; padding: 20px; }

  /* Card Design */
  .logout-card { background: #2c2c2c; padding: 40px 30px; border-radius: 15px; width: 100%; max-width: 400px; box-shadow: 0 10px 25px rgba(0,0,0,0.5); animation: fadeIn 0.8s ease; }
  
  /* Icon & Text */
  .icon { font-size: 60px; margin-bottom: 20px; display: block; }
  h1 { color: #00b894; margin-bottom: 10px; font-size: 28px; }
  p { color: #bbb; margin-bottom: 30px; font-size: 16px; line-height: 1.5; }

  /* Login Button */
  .login-btn { display: inline-block; padding: 12px 35px; background: #00b894; color: #fff; text-decoration: none; border-radius: 50px; font-weight: 600; transition: all 0.3s ease; }
  .login-btn:hover { background: #019e7e; transform: translateY(-3px); box-shadow: 0 5px 15px rgba(0, 184, 148, 0.4); }

  /* Animation */
  @keyframes fadeIn { from { opacity: 0; transform: translateY(20px); } to { opacity: 1; transform: translateY(0); } }
</style>
</head>
<body>

<div class="logout-card">
  <span class="icon">👋</span>
  <h1>See You Soon!</h1>
  <p>You have successfully logged out of <strong>SMART TEST</strong>.<br>Hope you learned something new today.</p>
  
  <a href="index.php" class="login-btn">Login Again</a>
</div>

<script>
  setTimeout(function(){
    // Redirect Updated to index.php
    window.location.href = 'index.php';
  }, 3000);
</script>

</body>
</html>