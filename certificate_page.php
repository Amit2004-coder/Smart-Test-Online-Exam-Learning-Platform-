<?php
session_start();
include('config.php');

// Security Check
if(!isset($_SESSION['auth'])) {
    header("Location: index.php");
    exit(0);
}

$user_id = $_SESSION['auth_user']['user_id'];
$user_name = $_SESSION['auth_user']['full_name'];

// =========================================================================
// PART 1: AGAR URL ME 'id' HAI -> TO ASLI GOLDEN CERTIFICATE DIKHAO
// =========================================================================
if(isset($_GET['id'])) {
    $test_id = mysqli_real_escape_string($conn, $_GET['id']);
    $query = "SELECT * FROM results WHERE id='$test_id' AND user_id='$user_id' AND (score/total_marks)*100 >= 40";
    $run = mysqli_query($conn, $query);

    if(mysqli_num_rows($run) <= 0) {
        echo "<h2 style='color:white; text-align:center; margin-top:50px;'>Certificate Not Found or Not Eligible!</h2>";
        exit(0);
    }

    $row = mysqli_fetch_assoc($run);
    $percentage = ($row['score'] / $row['total_marks']) * 100;
    $is_golden = ($percentage == 100);

    if($percentage == 100) $filled_stars = 5;
    elseif($percentage >= 80) $filled_stars = 5;
    elseif($percentage >= 60) $filled_stars = 4;
    elseif($percentage >= 40) $filled_stars = 3;
    else $filled_stars = 0;
    ?>
    <!DOCTYPE html>
    <html lang="en">
    <head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CERTIFICATE - SMART TEST</title>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; font-family: 'Segoe UI', sans-serif; }
        body { background: #e0eafc; display: flex; flex-direction: column; justify-content: center; align-items: center; min-height: 100vh; gap: 20px; padding: 20px; }

        .back-btn { padding: 10px 20px; background: #333; color: white; text-decoration: none; border-radius: 20px; font-weight: bold; position: absolute; top: 20px; left: 20px; }
        .back-btn:hover { background: #111; }

        #certificate-node { width: 900px; height: 600px; background: #fff; border: 15px solid; border-image-slice: 1; border-width: 15px; border-image-source: linear-gradient(45deg, #00b894, #00cec9, #0984e3, #6c5ce7); border-radius: 5px; padding: 50px; box-shadow: 0 20px 50px rgba(0,0,0,0.2); position: relative; text-align: center; overflow: hidden; display: flex; flex-direction: column; justify-content: space-between; }
        .golden-border { border-image-source: linear-gradient(45deg, #D4AF37, #FFD700, #B8860B, #D4AF37) !important; }
        #certificate-node::after { content: "SMART TEST"; position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%) rotate(-30deg); font-size: 140px; font-weight: 900; color: rgba(0,0,0,0.02); z-index: 0; pointer-events: none; }
        
        /* NAYA CSS FOR REG ID */
        .cert-id { position: absolute; top: 30px; left: 30px; font-size: 14px; color: #666; font-family: monospace; font-weight: bold; z-index: 2; }

        .logo-area { display: flex; justify-content: center; align-items: center; gap: 15px; position: relative; z-index: 1; }
        .logo-area h1 { font-size: 34px; color: #00b894; letter-spacing: 2px; font-weight: 800; }
        .recipient-name { font-size: 45px; color: #333; margin-top: 30px; font-weight: 800; text-decoration: underline; text-underline-offset: 10px; text-transform: uppercase; position: relative; z-index: 1; }
        .msg { font-size: 20px; color: #555; font-style: italic; margin-top: 15px; position: relative; z-index: 1; }
        .sub-detail { font-size: 24px; font-weight: 700; color: #222; margin-top: 10px; position: relative; z-index: 1; }
        .level-detail { font-size: 20px; font-weight: 600; color: #555; margin-top: 5px; position: relative; z-index: 1; }
        .stars-box { font-size: 55px; margin-top: 20px; position: relative; z-index: 1; }
        .star { color: #ddd; margin: 0 3px; }
        .star.filled { color: #f1c40f; } 
        .star.golden { color: #D4AF37; text-shadow: 0 0 15px rgba(212,175,55,0.4); }
        .signatures { display: flex; justify-content: space-between; padding: 0 60px; margin-top: 20px; position: relative; z-index: 1; }
        .sign { text-align: center; width: 200px; }
        .sign img { width: 100%; height: 65px; object-fit: contain; border-bottom: 2px solid #333; margin-bottom: 8px; }
        .sign b { font-size: 14px; color: #333; }
        .download-trigger { padding: 15px 40px; background: #00b894; color: white; border: none; border-radius: 50px; font-weight: bold; cursor: pointer; font-size: 18px; transition: 0.3s; }
        .download-trigger:hover { background: #008f72; transform: scale(1.05); }
    </style>
    </head>
    <body>
        <a href="certificate_page.php" class="back-btn">⬅ Back to Gallery</a>

        <div id="certificate-node">
            <div class="cert-id">Reg ID: ST-<?php echo date('Y', strtotime($row['end_time'])); ?>-<?php echo str_pad($row['id'], 4, '0', STR_PAD_LEFT); ?></div>

            <div class="logo-area">
                <img src="Assets/Smart test Logo.png" width="80" onerror="this.src='https://via.placeholder.com/80?text=LOGO'">
                <h1>SMART TEST</h1>
            </div>
            <div class="recipient-name"><?php echo htmlspecialchars(strtoupper($user_name)); ?></div>
            <p class="msg">Congratulations! You have successfully completed your journey with SMART TEST.</p>
            <div class="sub-detail">Subject: <?php echo isset($row['subject']) ? htmlspecialchars(strtoupper($row['subject'])) : 'GENERAL ASSESSMENT'; ?></div>
            <div class="level-detail">
                Level: <?php echo isset($row['level']) ? htmlspecialchars(ucfirst($row['level'])) : 'Beginner'; ?> 
                (<?php echo htmlspecialchars($row['score']); ?>/<?php echo htmlspecialchars($row['total_marks']); ?>)
            </div>
            <div class="stars-box" id="star-row"></div>
            <div class="signatures">
                <div class="sign"><img src="Assets/aayan signature.png" onerror="this.src='https://via.placeholder.com/150x60?text=Signature'"><br><b>Co-founder: Aayan Ahmad</b></div>
                <div class="sign"><img src="Assets/mozzamil signature.png" onerror="this.src='https://via.placeholder.com/150x60?text=Signature'"><br><b>Director: Mozzamil Husain</b></div>
            </div>
        </div>
        <button class="download-trigger" onclick="downloadImage()">Download Certificate!! ⬇</button>

    <script>
        function updateStars(count, isGolden) {
            const node = document.getElementById('certificate-node');
            const starRow = document.getElementById('star-row');
            starRow.innerHTML = '';
            if(isGolden) { node.classList.add('golden-border'); } 
            else { node.classList.remove('golden-border'); }
            for(let i=1; i<=5; i++) {
                let cls = "";
                if(i <= count) { cls = isGolden ? "golden" : "filled"; }
                starRow.innerHTML += `<span class="star ${cls}">★</span>`;
            }
        }
        function downloadImage() {
            const btn = document.querySelector('.download-trigger');
            btn.innerText = "Processing...";
            html2canvas(document.querySelector("#certificate-node"), { scale: 2 }).then(canvas => {
                let link = document.createElement('a');
                link.download = 'SMART_TEST_CERTIFICATE.png';
                link.href = canvas.toDataURL();
                link.click();
                btn.innerText = "Download Certificate!! ⬇"; 
            });
        }
        window.onload = () => updateStars(<?php echo $filled_stars; ?>, <?php echo $is_golden ? 'true' : 'false'; ?>);
    </script>
    </body>
    </html>
    <?php
    exit(0); 
}

// =========================================================================
// PART 2: AGAR URL ME 'id' NAHI HAI -> TO LIST/GALLERY DIKHAO
// =========================================================================
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>My Certificates | SMART_TEST</title>
<link rel="icon" href="Assets/Smart test Logo.png" type="image/jpeg">
<style>
    * { box-sizing: border-box; margin: 0; padding: 0; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
    body { background: #1d1f2a; color: #f0f0f0; min-height: 100vh; padding: 30px 20px; }
    .container { max-width: 900px; margin: 0 auto; }
    .header { display: flex; align-items: center; justify-content: space-between; margin-bottom: 30px; border-bottom: 1px solid #333; padding-bottom: 15px; }
    .header h2 { color: #D4AF37; display: flex; align-items: center; gap: 10px; }
    .btn-back { background: #333; color: #fff; padding: 8px 15px; border-radius: 8px; text-decoration: none; font-size: 14px; transition: 0.3s; }
    .btn-back:hover { background: #444; }
    .cert-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(250px, 1fr)); gap: 20px; }
    .cert-card { background: #252736; padding: 25px 20px; border-radius: 12px; text-align: center; border: 1px solid #444; transition: 0.3s; border-top: 4px solid #D4AF37; }
    .cert-card:hover { transform: translateY(-5px); box-shadow: 0 10px 20px rgba(0,0,0,0.3); border-color: #D4AF37; }
    
    /* IMAGES KE LIYE NAYA CSS */
    .cert-icon img { width: 70px; height: 70px; object-fit: contain; border-radius: 8px; margin-bottom: 15px; background: #fff; padding: 5px; border: 2px solid #D4AF37; }
    
    .cert-card h3 { color: #fff; margin-bottom: 5px; font-size: 20px; }
    .cert-card p { color: #bbb; font-size: 14px; margin-bottom: 5px; }
    .cert-card .score { color: #00b894; font-weight: bold; margin-bottom: 15px; display: block; }
    .btn-view { background: #D4AF37; color: #000; padding: 10px 20px; border-radius: 20px; text-decoration: none; font-weight: bold; font-size: 14px; display: inline-block; transition: 0.3s; }
    .btn-view:hover { background: #b5952f; color: #fff; }
    .empty-state { text-align: center; padding: 50px 20px; background: #252736; border-radius: 12px; border: 1px dashed #555; }
    .empty-state h3 { color: #bbb; margin-top: 15px; }
</style>
</head>
<body>

<div class="container">
    <div class="header">
        <h2>🏆 My Achieved Certificates</h2>
        <a href="profile_page.php" class="btn-back">⬅ Back to Profile</a>
    </div>

    <div class="cert-grid">
        <?php
        // Database se image lane ke liye LEFT JOIN
        $query = "SELECT r.*, s.subject_img 
                  FROM results r 
                  LEFT JOIN subjects s ON r.subject = s.subject_name 
                  WHERE r.user_id='$user_id' AND (r.score/r.total_marks)*100 >= 40 
                  ORDER BY r.start_time DESC";
        $run = mysqli_query($conn, $query);

        if(mysqli_num_rows($run) > 0) {
            while($row = mysqli_fetch_assoc($run)) {
                $date = date('d M Y', strtotime($row['end_time']));
                
                // Dynamic Image Logic: Agar database me img hai to wo, warna default logo
                $icon_src = !empty($row['subject_img']) ? $row['subject_img'] : "Assets/logo2.jpg";
                ?>
                <div class="cert-card">
                    <div class="cert-icon">
                        <img src="<?php echo $icon_src; ?>" alt="Logo" onerror="this.src='Assets/logo2.jpg'">
                    </div>
                    
                    <h3><?php echo htmlspecialchars(strtoupper($row['subject'])); ?></h3>
                    <p>Level: <b><?php echo htmlspecialchars($row['level']); ?></b></p>
                    <span class="score">Score: <?php echo $row['score'] . '/' . $row['total_marks']; ?></span>
                    <p style="font-size: 12px; color: #888; margin-bottom: 15px;">Date: <?php echo $date; ?></p>
                    
                    <a href="certificate_page.php?id=<?php echo $row['id']; ?>" class="btn-view">Open Certificate</a>
                </div>
                <?php
            }
        } else {
            ?>
            <div style="grid-column: 1 / -1;" class="empty-state">
                <span style="font-size: 50px;">🚀</span>
                <h3>No Certificates Yet!</h3>
                <p style="color: #888; margin-top: 10px;">Give a test and score at least 40% to earn your first certificate.</p>
                <br>
                <a href="dashboard_page.php" class="btn-view" style="background: #00b894; color: white;">Take a Test Now</a>
            </div>
            <?php
        }
        ?>
    </div>
</div>

</body>
</html>