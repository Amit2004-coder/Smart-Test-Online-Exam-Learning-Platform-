<?php
session_start();
include('config.php');

// Security Check
if(!isset($_SESSION['auth'])) { 
    header("Location: index.php"); 
    exit(0); 
}

$subject = isset($_GET['subject']) ? mysqli_real_escape_string($conn, $_GET['subject']) : 'General';
$user_id = $_SESSION['auth_user']['user_id'];
$is_subscribed = isset($_SESSION['auth_user']['is_subscribed']) ? $_SESSION['auth_user']['is_subscribed'] : 0; 
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?php echo htmlspecialchars($subject); ?> Notes | SMART_TEST</title>
<link rel="icon" href="Assets/Smart test Logo.png" type="image/jpeg">
<style>
  * { box-sizing: border-box; font-family: 'Segoe UI', sans-serif; margin: 0; padding: 0; }
  body { background: #1d1f2a; color: #f0f0f0; padding: 20px; min-height: 100vh; }
  .container { max-width: 900px; margin: 0 auto; }
  
  /* Header */
  .header { text-align: center; margin-bottom: 30px; border-bottom: 1px solid #333; padding-bottom: 20px; }
  h1 { color: #00b894; letter-spacing: 1px; margin-bottom: 5px; }
  p { color: #bbb; font-size: 14px; }

  /* Note Card */
  .note-card { background: #252736; padding: 15px; border-radius: 12px; margin-bottom: 15px; display: flex; justify-content: space-between; align-items: center; border: 1px solid #333; }
  .card-left { display: flex; align-items: center; gap: 15px; flex: 1; }
  .note-img { width: 120px; height: 75px; border-radius: 8px; object-fit: cover; border: 1px solid #444; background: #111; }
  .note-info h3 { font-size: 18px; margin-bottom: 4px; color: #fff; }
  .note-info p { color: #888; font-size: 13px; line-height: 1.4; }
  .note-meta { font-size: 11px; color: #555; margin-top: 3px; }

  /* Buttons */
  .btn-download { background: #3498db; color: white; text-decoration: none; padding: 10px 20px; border-radius: 50px; font-weight: bold; font-size: 14px; display: flex; align-items: center; gap: 5px; cursor: pointer; border: none; }
  .btn-locked { background: #e74c3c; }
  .btn-view { background: #00b894; }
  .fav-action { margin-right: 15px; font-size: 20px; color: #555; text-decoration: none; cursor: pointer; }
  .fav-action.active { color: #e74c3c; }

  /* --- FULL PAGE VIEWER (जो आपको चाहिए था) --- */
  .full-page-viewer {
      display: none; /* Default me chhipa rahega */
      position: fixed; top: 0; left: 0; width: 100vw; height: 100vh;
      background: #000; /* Pura black background */
      z-index: 99999; flex-direction: column;
  }
  .viewer-header {
      width: 100%; height: 60px; background: #111;
      display: flex; justify-content: space-between; align-items: center; padding: 0 20px;
      border-bottom: 1px solid #333;
  }
  .viewer-title { color: #fff; font-size: 18px; font-weight: bold; }
  .btn-close-viewer {
      background: #e74c3c; color: white; border: none; padding: 8px 20px;
      cursor: pointer; font-weight: bold; border-radius: 5px; font-size: 16px;
  }
  .btn-close-viewer:hover { background: #c0392b; }
  .viewer-content {
      width: 100%; height: calc(100vh - 60px);
      display: flex; justify-content: center; align-items: center;
  }
  .viewer-content video, .viewer-content iframe { width: 100%; height: 100%; border: none; outline: none; }

  @media(max-width: 600px) {
      .note-card { flex-direction: column; align-items: flex-start; gap: 15px; }
      .card-left { width: 100%; }
      .action-buttons { width: 100%; display: flex; gap: 10px; justify-content: flex-end; }
      .btn-download { flex: 1; justify-content: center; }
  }
</style>
</head>
<body>

<div class="container">
  <div class="header">
      <h1>Study Materials: <?php echo htmlspecialchars($subject); ?></h1>
      <p>Watch videos and read notes for <?php echo htmlspecialchars($subject); ?>.</p>
  </div>

  <div class="notes-list">
      <?php
      $query = "SELECT * FROM notes WHERE subject_name='$subject' ORDER BY created_at DESC";
      $query_run = mysqli_query($conn, $query);

      $my_favs = [];
      $f_check = mysqli_query($conn, "SELECT title FROM saved_items WHERE user_id='$user_id' AND type='Note'");
      while($fr = mysqli_fetch_assoc($f_check)) { $my_favs[] = $fr['title']; }

      if($query_run && mysqli_num_rows($query_run) > 0)
      {
          while($row = mysqli_fetch_assoc($query_run))
          {
              $img = !empty($row['note_image']) ? $row['note_image'] : 'Assets/pdf-icon.png';
              $is_fav = in_array($row['chapter_name'], $my_favs); 
              $heart = $is_fav ? '❤️' : '♡';
              $active = $is_fav ? 'active' : '';
              
              $safe_chapter = urlencode($row['chapter_name']);
              $current_url = urlencode("notes_page.php?subject=$subject");
              
              $file_type = strtolower($row['file_type']);
              $is_video = (strpos($file_type, 'video') !== false || strpos($file_type, 'mp4') !== false);
              $media_type = $is_video ? 'video' : 'pdf';
              $clean_title = htmlspecialchars($row['chapter_name'], ENT_QUOTES);
              ?>
              
              <div class="note-card">
                <div class="card-left">
                    <img src="<?php echo htmlspecialchars($img); ?>" alt="Cover" class="note-img" onerror="this.src='Assets/logo2.jpg'">
                    <div class="note-info">
                        <h3><?php echo htmlspecialchars($row['chapter_name']); ?></h3>
                        <p><?php echo htmlspecialchars($row['description']); ?></p>
                        <div class="note-meta">Type: <?php echo htmlspecialchars($row['file_type']); ?></div>
                    </div>
                </div>

                <div class="action-buttons" style="display:flex; align-items:center; gap: 10px;">
                    <a href="save_favorite.php?type=Note&name=<?php echo $safe_chapter; ?>&redirect=<?php echo $current_url; ?>" class="fav-action <?php echo $active; ?>"><?php echo $heart; ?></a>

                    <button onclick="openFullPage('Assets/<?php echo htmlspecialchars($row['file_path']); ?>', '<?php echo $media_type; ?>', '<?php echo $clean_title; ?>')" class="btn-download btn-view">
                        <?php echo $is_video ? '▶ Watch' : '👁️ View'; ?>
                    </button>

                    <?php if($is_subscribed) { ?>
                        <a href="Assets/<?php echo htmlspecialchars($row['file_path']); ?>" class="btn-download" download>⬇ Download</a>
                    <?php } else { ?>
                        <a href="javascript:void(0)" onclick="alert('🔒 Premium Feature!')" class="btn-download btn-locked">🔒 Pro</a>
                    <?php } ?>
                </div>
              </div>
              <?php
          }
      }
      ?>
  </div>
</div>

<div id="fullPageViewer" class="full-page-viewer">
    <div class="viewer-header">
        <div class="viewer-title" id="viewerTitle">Title</div>
        <button class="btn-close-viewer" onclick="closeFullPage()">✖ Close</button>
    </div>
    <div class="viewer-content" id="viewerContent">
        </div>
</div>

<script>
    function openFullPage(filePath, type, title) {
        var viewer = document.getElementById("fullPageViewer");
        var content = document.getElementById("viewerContent");
        
        // Title set karna
        document.getElementById("viewerTitle").innerText = title;
        
        // Pura page cover karna aur pichhe ka scroll rokna
        viewer.style.display = "flex";
        document.body.style.overflow = "hidden"; 

        if (type === 'video') {
            // Video with FULL CONTROLS
            content.innerHTML = `
                <video controls autoplay controlsList="nodownload" style="width:100%; max-height:100%;">
                    <source src="${filePath}" type="video/mp4">
                    Browser not supported.
                </video>`;
        } else {
            // PDF Viewer
            content.innerHTML = `<iframe src="${filePath}#toolbar=0" style="width:100%; height:100%; border:none;"></iframe>`;
        }
    }

    function closeFullPage() {
        var viewer = document.getElementById("fullPageViewer");
        var content = document.getElementById("viewerContent");
        
        // Viewer band karna aur scroll wapas lana
        viewer.style.display = "none";
        document.body.style.overflow = "auto"; 
        
        // Video stop karne ke liye content hata dena
        content.innerHTML = ""; 
    }
</script>

</body>
</html>