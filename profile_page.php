<?php
session_start();
include('config.php');

// 1. Security Check
if(!isset($_SESSION['auth'])) {
    header("Location: index.php");
    exit(0);
}

// 2. User Data Fetch
$user_id = $_SESSION['auth_user']['user_id'];
$user_name = $_SESSION['auth_user']['full_name'];
// Profile pic agar session me nahi hai to default logo use karo
$user_pic = isset($_SESSION['auth_user']['profile_pic']) ? "Assets/".$_SESSION['auth_user']['profile_pic'] : "Assets/logo2.jpg";
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>SMART_TEST - Profile</title>
<link rel="icon" href="Assets/Smart test Logo.png" type="image/jpeg">
<style>
/* --- SAME CSS AS PROVIDED --- */
*{box-sizing:border-box;margin:0;padding:0;font-family:'Segoe UI',Tahoma,Geneva,Verdana,sans-serif;}
body{background:#1d1f2a;color:#f0f0f0;min-height:100vh;padding-bottom:80px;}
.container{max-width:900px;width:95%;margin:0 auto;padding:20px;}

/* Profile Header */
.profile-header{display:flex;align-items:center;justify-content:space-between;padding:20px 0;border-bottom:1px solid #2c2c2c;}
.profile-info{display:flex;align-items:center;gap:15px;}
.profile-info img{width:80px;height:80px;border-radius:50%;border:2px solid #00b894; object-fit: cover;}
.profile-info div strong{font-size:20px;color:#00b894;}
.profile-info div small{color:#bbb;}
.profile-actions button{margin-left:10px;padding:10px 16px;border:none;border-radius:8px;font-weight:600;cursor:pointer;transition:0.3s;}
.profile-actions button a { text-decoration: none; color: #fff; display: inline-block; width: 100%; height: 100%; }
.profile-actions .edit-btn{background:#00b894;color:#fff;}
.profile-actions .edit-btn:hover{background:#019e7e;}
.profile-actions .cert-btn{background:#3498db;color:#fff;}
.profile-actions .cert-btn:hover{background:#217dbb;}
.profile-actions .logout-btn{background:#e74c3c;color:#fff;}
.profile-actions .logout-btn:hover{background:#c0392b;}

/* History Section */
.history{margin-top:30px;}
.history h3{margin-bottom:15px;color:#00b894;}
.history-card{display:flex;justify-content:space-between;align-items:center;background:#2c2c2c;padding:12px 15px;border-radius:10px;margin-bottom:12px;transition:0.3s;}
.history-card:hover{background:#323444;}
.history-left{display:flex;align-items:center;gap:10px;}
.history-left img{width:50px;height:50px;border-radius:8px; object-fit: contain; background: #fff; padding: 2px;}
.history-left div a{color:#00b894;font-weight:600;text-decoration:none;}
.history-left div small{color:#bbb;}
.history-score{padding:6px 12px;border-radius:8px;font-weight:600;background:#9b59b6;}

/* Footer */
.footer{position:fixed;bottom:0;left:0;width:100%;display:flex;justify-content:space-around;background:#0a0a0a;padding:15px;font-size:14px;border-top:1px solid #2c2c2c;}
.footer a{color:#f0f0f0;text-decoration:none;}
.footer div:hover{color:#00b894;cursor:pointer;}
.footer .active a { color: #00b894; font-weight: bold; }

/* Responsive */
@media(max-width:768px){
  .profile-header{flex-direction:column;align-items:flex-start;gap:10px;}
  .profile-actions button{margin:5px 5px 0 0;}
  .history-card{flex-direction:column;align-items:flex-start;}
  .history-score{margin-top:8px;}
}
</style>
</head>
<body>
<div class="container">

  <div class="profile-header">
    <div class="profile-info">
      <img src="<?php echo $user_pic; ?>" alt="Profile Image" onerror="this.src='Assets/aayanpic.png'">
      <div>
        <strong><?php echo $user_name; ?></strong><br>
        <small>ID-<?php echo $user_id; ?></small>
      </div>
    </div>
    <div class="profile-actions">
      <button class="edit-btn"><a href="edit_page.php">Edit Profile</a></button>
      <button class="cert-btn"><a href="certificate_page.php">Certificate</a></button>
      
      <button class="logout-btn"><a href="logout_page.php">Logout</a></button>
    </div>
  </div>

  <div class="history">
    <h3>Recent Activity</h3>

    <?php
    // Fetch last 5 results from database
    $history_query = "SELECT * FROM results WHERE user_id='$user_id' ORDER BY start_time DESC LIMIT 5";
    $history_run = mysqli_query($conn, $history_query);

    if(mysqli_num_rows($history_run) > 0)
    {
        while($row = mysqli_fetch_assoc($history_run))
        {
            // Icon Logic based on Subject
            $icon_src = "Assets/Smart test Logo.png"; // Default
            if($row['subject'] == 'HTML') $icon_src = "Assets/html.png";
            if($row['subject'] == 'JEE') $icon_src = "Assets/IIT-JEE-logo.jpg";
            if($row['subject'] == 'JavaScript') $icon_src = "Assets/javascript.png";
            if($row['subject'] == 'Python') $icon_src = "Assets/python.jpeg";
            if($row['subject'] == 'C++') $icon_src = "Assets/cpp.png";
            // ... Add more if needed
            ?>
            <div class="history-card">
              <div class="history-left">
                <img src="<?php echo $icon_src; ?>" alt="Quiz Icon" onerror="this.src='Assets/logo2.jpg'">
                <div>
                    <a href="#"><?php echo $row['subject']; ?> Quiz</a><br>
                    <small><?php echo $row['level']; ?> - <?php echo $row['total_questions']; ?> Qs</small>
                </div>
              </div>
              <div class="history-score"><?php echo $row['score']; ?>/<?php echo $row['total_questions']; ?></div>
            </div>
            <?php
        }
    }
    else
    {
        echo "<p style='color:#666; text-align:center;'>No recent history found.</p>";
    }
    ?>

  </div>

</div>

<div class="footer">
  <div><a href="dashboard_page.php">🏠︎ Home</a></div>
  <div><a href="activity_page.php">📑 Activity</a></div>
  <div><a href="saved_page.php">❤️ Saved</a></div>
  <div class="active"><a href="profile_page.php">👤 Profile</a></div>
</div>
</body>
</html>