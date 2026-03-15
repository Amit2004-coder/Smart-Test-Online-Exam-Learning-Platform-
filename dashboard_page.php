<?php
session_start();
include('config.php');

// 1. Security Check
if(!isset($_SESSION['auth']))
{
    $_SESSION['status'] = "You need to Login first!";
    header("Location: index.php");
    exit(0);
}

// ==========================================
//  [EXAM SUBMISSION LOGIC] - With Total Marks
// ==========================================
if($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['subject'])) {

    // Data from Form
    $u_id = $_SESSION['auth_user']['user_id'];
    $sub = mysqli_real_escape_string($conn, $_POST['subject']);
    $lvl = mysqli_real_escape_string($conn, $_POST['level']);
    $tot_q = mysqli_real_escape_string($conn, $_POST['total_questions']);
    $scr = mysqli_real_escape_string($conn, $_POST['score']);
    $time = mysqli_real_escape_string($conn, $_POST['time_taken']);
    
    // Calculate Total Marks (1 marks per question)
    $tot_m = $tot_q * 1; 
    
    // Status & Time
    $status = "Completed"; 
    $end_time = date('Y-m-d H:i:s');

    // --- QUERY UPDATE ---
    // Added: total_marks column
    
    $query = "INSERT INTO results (user_id, subject, score, total_marks, total_questions, level, status, time_taken, end_time) 
              VALUES ('$u_id', '$sub', '$scr', '$tot_m', '$tot_q', '$lvl', '$status', '$time', '$end_time')";
              
    $query_run = mysqli_query($conn, $query);

    if($query_run) {
        $_SESSION['status'] = "Test Submitted Successfully! 🎉";
    } else {
        $_SESSION['status'] = "Error saving result! (Check if total_marks column exists)";
    }

    // Refresh Page
    header("Location: dashboard_page.php");
    exit(0);
}
// ==========================================
//  LOGIC ENDS HERE
// ==========================================


// 2. User Data (Baaki code same rahega)
$user_name = $_SESSION['auth_user']['full_name'];
$user_id = $_SESSION['auth_user']['user_id'];
$user_points = $_SESSION['auth_user']['points'];
$user_pic = isset($_SESSION['auth_user']['profile_pic']) ? "Assets/".$_SESSION['auth_user']['profile_pic'] : "Assets/logo2.jpg";

// 3. FETCH USER'S FAVORITES
$my_favorites = []; 
$fav_check = "SELECT title, type FROM saved_items WHERE user_id='$user_id'";
$fav_run = mysqli_query($conn, $fav_check);
if(mysqli_num_rows($fav_run) > 0){
    while($f_row = mysqli_fetch_assoc($fav_run)){
        $key = $f_row['title'] . '_' . $f_row['type'];
        $my_favorites[] = $key;
    }
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>SMART_TEST - Dashboard</title>
<link rel="icon" href="Assets/Smart test Logo.png" type="image/jpeg">
<style>
/* --- CSS --- */
*{box-sizing:border-box;margin:0;padding:0;font-family:'Segoe UI',Tahoma,Geneva,Verdana,sans-serif;}
body{background:#1d1f2a;color:#f0f0f0;min-height:100vh;margin:0;padding:0;padding-bottom:70px;}

.container{max-width:1200px;width:95%;margin:0 auto;}

/* HEADER */
.header{background: #012035; padding:15px 0; position:sticky; top:0; z-index:1000; border-bottom:1px solid #2c2c2c; box-shadow: 0 4px 10px rgba(0,0,0,0.3);}
.header-content { display: flex; justify-content: space-between; align-items: center; }

.profile-wrapper { position: relative; cursor: pointer; }
.profile{display:flex;align-items:center;gap:12px; padding: 5px; border-radius: 8px; transition: 0.3s;}
.profile:hover { background: rgba(255,255,255,0.05); }
.profile img{width:50px;height:50px;border-radius:50%; border:2px solid #00b894; object-fit: cover;}
.profile div{display:flex;flex-direction:column; justify-content: center;}
.profile strong{font-size: 16px; letter-spacing: 0.5px;}
.profile .user-id{color: #00b894; font-size: 12px; font-weight: bold;}
.profile .user-bio{color: #bbb; font-size: 11px; font-style: italic;}

.dropdown-menu { display: none; position: absolute; top: 65px; left: 0; background: #2c2c2c; width: 150px; border-radius: 8px; box-shadow: 0 5px 15px rgba(0,0,0,0.5); border: 1px solid #444; overflow: hidden; z-index: 1001; }
.dropdown-menu a { display: block; padding: 12px; color: #f0f0f0; text-decoration: none; font-size: 14px; transition: 0.2s; }
.dropdown-menu a:hover { background: #e74c3c; color: white; }

.points{font-size:15px;background:#2c2c2c;padding:8px 14px;border-radius:20px; border:1px solid #444; color:#56ddff; font-weight:bold;}

/* Search */
.search{margin-top: 20px;}
.search input{width:100%;padding:12px;border-radius:8px;border:none;background:#ced4d8;color:#111;margin-bottom:15px; outline:none;}
.search input:focus{border:2px solid #00b894;}

.section-title { margin: 20px 0 10px 0; color: #00b894; font-size: 18px; border-left: 4px solid #00b894; padding-left: 10px; }
.categories-wrapper{overflow:hidden;margin-bottom:10px;}
.categories{display:flex;gap:15px;overflow-x:auto;padding-bottom:8px; padding-top: 5px; scroll-behavior:smooth;}
.categories::-webkit-scrollbar{display:none;}

/* CATEGORY ITEM CARD */
.cat-item { 
    position: relative; 
    text-align:center; 
    min-width:110px; /* Thoda chouda kiya taaki button fit ho */
    flex-shrink:0; 
    background: #252736; 
    padding: 10px; 
    padding-bottom: 30px; /* Neeche jagah banayi button ke liye */
    border-radius: 12px; 
    border: 1px solid #333; 
    transition: 0.3s; 
}
.cat-item:hover { transform: translateY(-3px); border-color: #00b894; }
.cat-item img{width:50px;height:50px;border-radius:8px;margin-bottom:5px; object-fit: contain;}
.cat-item a.main-link { text-decoration: none; color: #f0f0f0; font-size: 13px; display: block; }

/* FIX: Heart Button Position (Bottom Right) */
.fav-btn {
    position: absolute; 
    bottom: 5px; /* Neeche se 5px */
    right: 5px;  /* Right se 5px */
    font-size: 16px;
    color: #888; 
    cursor: pointer; 
    text-decoration: none;
    transition: 0.2s; 
    z-index: 10;
    padding: 2px;
}
.fav-btn:hover { transform: scale(1.2); }
.fav-btn.active { color: #e74c3c; /* Red color for filled heart */ }

/* Activity Cards */
.activity { margin-top: 20px; }
.card{display:flex;justify-content:space-between;align-items:center;background:#2c2c2c;padding:12px 10px;border-radius:10px;margin-bottom:12px;transition:0.3s;}
.card:hover{background:#323444; transform:translateY(-2px);}
.card-left{display:flex;align-items:center;gap:10px;}
.card-left img{width:45px;height:45px;border-radius:8px; object-fit:contain; background:#fff; padding:2px;}
.card-left a{color:#00b894;font-weight:600;text-decoration:none;}
.card-left small{color:#bbb;}
.score{padding:6px 12px;border-radius:8px;font-weight:600;}
.red{background:#e74c3c;color:#fff;} .yellow{background:#f1c40f;color:#000;} .blue{background:#3498db;color:#fff;} .purple{background:#9b59b6;color:#fff;}

/* Footer */
.footer{position:fixed;bottom:0;left:0;width:100%;display:flex;justify-content:space-around;background:#0a0a0a;padding:15px;font-size:14px;border-top:1px solid #2c2c2c; z-index:1000;}
.footer a{color:#f0f0f0;text-decoration:none;}
.footer div:hover{color:#00b894;cursor:pointer;}

@media(max-width:480px){ .categories img{width:45px;height:45px;} }
</style>
</head>
<body>

<?php if(isset($_SESSION['fav_popup'])) { ?>
    
    <div id="favModal" style="position: fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.6); z-index:9999; display:flex; justify-content:center; align-items:center; animation: fadeIn 0.3s;">
        
        <div style="background:#252736; width:90%; max-width:350px; padding:30px; border-radius:15px; text-align:center; border:2px solid #00b894; box-shadow:0 0 20px rgba(0,184,148,0.4); transform:scale(0.9); animation: popUp 0.3s forwards;">
            
            <div style="font-size:40px; margin-bottom:10px;">
                <?php echo ($_SESSION['fav_popup']['status'] == 'success') ? '🎉' : '🗑️'; ?>
            </div>
            
            <h2 style="color:#fff; margin-bottom:10px;"><?php echo $_SESSION['fav_popup']['title']; ?></h2>
            
            <p style="color:#bbb; font-size:14px; margin-bottom:0; line-height:1.5;"><?php echo $_SESSION['fav_popup']['msg']; ?></p>
        </div>
    </div>

    <style> 
        @keyframes fadeIn {from{opacity:0}to{opacity:1}} 
        @keyframes popUp {to{transform:scale(1)}} 
    </style>

    <script>
        setTimeout(function(){
            var modal = document.getElementById('favModal');
            // Dhire se gayab hone ke liye transition
            modal.style.transition = "opacity 0.5s ease";
            modal.style.opacity = 0; 
            
            // 0.5 second baad display none kar do
            setTimeout(function(){ modal.style.display = 'none'; }, 500);
            
        }, 450); // .3 Seconds baad band ho jayega
    </script>

    <?php unset($_SESSION['fav_popup']); ?>
<?php } ?>

<div class="header">
    <div class="container header-content">
        <div class="profile-wrapper" onclick="toggleDropdown()">
            <div class="profile">
                <img src="<?php echo $user_pic; ?>" alt="profile" onerror="this.src='Assets/aayanpic.png'">
                <div>
                    <strong><?php echo $user_name; ?></strong>
                    <span class="user-id">ID-<?php echo $user_id; ?></span>
                    <span class="user-bio">Learning Full Stack 👨‍💻</span>
                </div>
                <span style="font-size: 12px; color: #bbb;">▼</span>
            </div>
            <div class="dropdown-menu" id="logoutDropdown">
                <a href="profile_page.php">👤 View Profile</a>
                <a href="logout_page.php">⏻ Logout</a>
            </div>
        </div>
        <div class="points">💎 <?php echo $user_points; ?></div>
    </div>
</div>

<div class="container">
  
  <div class="search">
    <input type="text" id="searchInput" onkeyup="filterContent()" placeholder="🔍 Search for quizzes or notes...">
  </div>

  <h3 class="section-title">Tests (Quizzes)</h3>
  <div class="categories-wrapper">
    <div class="categories" id="testList">
        <?php
        $query = "SELECT * FROM subjects";
        $result = mysqli_query($conn, $query);

        if(mysqli_num_rows($result) > 0)
        {
            while($row = mysqli_fetch_assoc($result))
            {
                $check_key = $row['subject_name'] . '_Test';
                $is_fav = in_array($check_key, $my_favorites);
                
                // FIX: Use Hollow Heart for Unfav, Red for Fav
                $heart_icon = $is_fav ? '❤️' : '♡'; 
                $active_class = $is_fav ? 'active' : '';
                ?>
                <div class="cat-item filter-item"> <a href="save_favorite.php?type=Test&name=<?php echo $row['subject_name']; ?>" class="fav-btn <?php echo $active_class; ?>" title="Favorite">
                        <?php echo $heart_icon; ?>
                    </a>
                    
                    <a href="level_page.php?subject=<?php echo $row['subject_name']; ?>" class="main-link">
                        <img src="<?php echo $row['subject_img']; ?>" alt="Icon" onerror="this.src='Assets/aayanpic.jpg'">
                        <br>
                        <span class="item-name"><?php echo $row['subject_name']; ?></span>
                    </a>
                </div>
                <?php
            }
        }
        else { echo "<p style='color:#bbb;'>No tests available.</p>"; }
        ?>
    </div>
  </div>

  <h3 class="section-title">Study Notes</h3>
<div class="categories-wrapper">
    <div class="categories" id="notesList">
        <?php
        // NEW LOGIC: Sirf wahi subjects lao jinke notes 'notes' table me exist karte hain
        // Hum 'INNER JOIN' use karenge taaki sirf matching data aaye
        // 'GROUP BY' use kiya taaki agar ek subject ke 10 notes hain, to wo subject 10 baar na dikhe, bas ek baar dikhe.
        
        $notes_query = "SELECT s.* FROM subjects s 
                        INNER JOIN notes n ON s.subject_name = n.subject_name 
                        GROUP BY s.subject_name";
        
        $result_notes = mysqli_query($conn, $notes_query);
        
        if(mysqli_num_rows($result_notes) > 0)
        {
            while($row = mysqli_fetch_assoc($result_notes))
            {
                // Favorite Check Logic
                $check_key = $row['subject_name'] . '_Note';
                $is_fav = (isset($my_favorites) && in_array($check_key, $my_favorites));
                
                $heart_icon = $is_fav ? '❤️' : '♡';
                $active_class = $is_fav ? 'active' : '';

                // Link Safety
                $subject_safe = urlencode($row['subject_name']);
                ?>
                <div class="cat-item filter-item" style="border-color: #3498db;">
                    <a href="save_favorite.php?type=Note&name=<?php echo $subject_safe; ?>" class="fav-btn <?php echo $active_class; ?>" title="Favorite">
                        <?php echo $heart_icon; ?>
                    </a>

                    <a href="notes_page.php?subject=<?php echo $subject_safe; ?>" class="main-link">
                        <img src="<?php echo $row['subject_img']; ?>" alt="Icon" onerror="this.src='Assets/logo2.jpg'">
                        <br>
                        <span class="item-name"><?php echo $row['subject_name']; ?></span>
                    </a>
                </div>
                <?php
            }
        }
        else 
        { 
            // Empty State
            echo "<p style='color:#bbb; padding-left:5px; font-size:14px;'>No study notes uploaded yet.</p>"; 
        }
        ?>
    </div>
</div>

  <div class="activity">
    <h3 class="section-title" style="border-color: #9b59b6;">Recent Activity</h3>
    <?php
    $act_query = "SELECT r.*, s.subject_img FROM results r LEFT JOIN subjects s ON r.subject = s.subject_name WHERE r.user_id = '$user_id' ORDER BY r.start_time DESC LIMIT 5";
    $act_run = mysqli_query($conn, $act_query);

    if(mysqli_num_rows($act_run) > 0) {
        while($row = mysqli_fetch_assoc($act_run)) {
            $total = $row['total_questions'];
            $score = $row['score'];
            $per = ($total > 0) ? ($score / $total) * 100 : 0;
            $color_class = 'red'; 
            if($per >= 40) $color_class = 'yellow';
            if($per >= 70) $color_class = 'blue';
            if($per >= 90) $color_class = 'purple';
            $img = !empty($row['subject_img']) ? $row['subject_img'] : 'Assets/logo2.jpg';
            ?>
            <div class="card">
                <div class="card-left">
                    <a href="#"><img src="<?php echo $img; ?>" alt="icon"></a>
                    <div>
                        <a href="#"><?php echo $row['subject']; ?></a><br>
                        <small><?php echo $row['level']; ?></small>
                    </div>
                </div>
                <div class="score <?php echo $color_class; ?>"><?php echo $score . '/' . $total; ?></div>
            </div>
            <?php
        }
    } else {
        echo "<p style='color:#bbb; padding:10px;'>No recent activity yet.</p>";
    }
    ?>
  </div>
</div>

<div class="footer">
  <div><a href="dashboard_page.php">🏠︎ Home</a></div>
  <div><a href="activity_page.php">📑 Activity</a></div>
  <div><a href="saved_page.php">❤️ Saved</a></div>
  <div><a href="profile_page.php">👤 Profile</a></div>
</div>

<script>
    function toggleDropdown() {
        var dropdown = document.getElementById("logoutDropdown");
        dropdown.style.display = (dropdown.style.display === "block") ? "none" : "block";
    }
    window.onclick = function(event) {
        if (!event.target.closest('.profile-wrapper')) {
            document.getElementById("logoutDropdown").style.display = "none";
        }
    }
    
    // FIX: Search Functionality
    function filterContent() {
        let input = document.getElementById('searchInput').value.toUpperCase();
        let items = document.getElementsByClassName('filter-item');

        for (let i = 0; i < items.length; i++) {
            let nameSpan = items[i].getElementsByClassName('item-name')[0];
            if (nameSpan) {
                let txtValue = nameSpan.textContent || nameSpan.innerText;
                if (txtValue.toUpperCase().indexOf(input) > -1) {
                    items[i].style.display = "";
                } else {
                    items[i].style.display = "none";
                }
            }
        }
    }

    function enableScroll(id) {
        const list = document.getElementById(id);
        let isDown = false, startX, scrollLeft;
        list.addEventListener('mousedown', (e) => { isDown = true; startX = e.pageX - list.offsetLeft; scrollLeft = list.scrollLeft; });
        list.addEventListener('mouseleave', () => isDown = false);
        list.addEventListener('mouseup', () => isDown = false);
        list.addEventListener('mousemove', (e) => { if(!isDown) return; e.preventDefault(); const x = e.pageX - list.offsetLeft; const walk = (x - startX) * 2; list.scrollLeft = scrollLeft - walk; });
    }
    enableScroll('testList');
    enableScroll('notesList');
</script>

</body>
</html>