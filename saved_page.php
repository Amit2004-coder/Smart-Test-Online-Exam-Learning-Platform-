<?php
session_start();
include('config.php');

// Security Check
if(!isset($_SESSION['auth'])) { header("Location: index.php"); exit(0); }

$user_id = $_SESSION['auth_user']['user_id'];

// ==========================================
//  A. SMART DELETE LOGIC
// ==========================================

if(isset($_POST['delete_selected_btn']))
{
    if(isset($_POST['selected_ids']))
    {
        $all_ids = $_POST['selected_ids'];
        $extract_id = implode(',' , $all_ids);
        $query = "DELETE FROM saved_items WHERE id IN($extract_id) AND user_id='$user_id'";
        mysqli_query($conn, $query);
        $_SESSION['status'] = "Deleted successfully! 🗑️";
    }
    else { $_SESSION['status'] = "No items selected!"; }
    header("Location: saved_page.php"); exit(0);
}

if(isset($_POST['clear_all_context_btn']))
{
    $scope = $_POST['delete_scope'];
    if($scope == 'all') {
        $query = "DELETE FROM saved_items WHERE user_id='$user_id'";
        $msg = "All items cleared! 🧹";
    } else {
        $query = "DELETE FROM saved_items WHERE user_id='$user_id' AND type='$scope'";
        $msg = "All $scope(s) cleared! 🧹";
    }
    if(mysqli_query($conn, $query)) { $_SESSION['status'] = $msg; } 
    else { $_SESSION['status'] = "Failed to clear."; }
    header("Location: saved_page.php"); exit(0);
}

// ==========================================
//  B. FETCH DATA
// ==========================================
$all_items = [];
$q_query = "SELECT s.id, s.saved_at, s.type, q.question as title, q.subject, q.level FROM saved_items s JOIN questions q ON s.question_id = q.id WHERE s.user_id = '$user_id' AND s.type='question'";
$q_run = mysqli_query($conn, $q_query);
while($row = mysqli_fetch_assoc($q_run)) { $all_items[] = $row; }

$fav_query = "SELECT id, saved_at, type, title, 'General' as subject, 'Standard' as level FROM saved_items WHERE user_id='$user_id' AND (type='Test' OR type='Note')";
$fav_run = mysqli_query($conn, $fav_query);
while($row = mysqli_fetch_assoc($fav_run)) { $all_items[] = $row; }

usort($all_items, function($a, $b) { return strtotime($b['saved_at']) - strtotime($a['saved_at']); });
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>SMART TEST - Saved Items</title>
<link rel="icon" href="Assets/Smart test Logo.png" type="image/jpeg">
<style>
  /* --- CSS --- */
  * { box-sizing: border-box; margin: 0; padding: 0; font-family: 'Segoe UI', sans-serif; }
  body { background: #1d1f2a; color: #f0f0f0; min-height: 100vh; padding-bottom: 90px; }
  .container { max-width: 900px; width: 95%; margin: 0 auto; padding: 20px; }
  
  .page-header { margin-bottom: 25px; border-bottom: 1px solid #2c2c2c; padding-bottom: 15px; display: flex; justify-content: space-between; align-items: center; }
  .page-header h2 { color: #00b894; font-size: 24px; }
  
  .manage-btn { font-size: 13px; color: #e74c3c; cursor: pointer; border: 1px solid #e74c3c; padding: 5px 12px; border-radius: 5px; transition: 0.3s; background: transparent; }
  .manage-btn:hover { background: #e74c3c; color: white; }

  /* TABS */
  .tabs { display: flex; gap: 10px; margin-bottom: 10px; overflow-x: auto; padding-bottom: 5px; }
  .tab { padding: 8px 16px; background: #2c2c2c; border-radius: 20px; font-size: 13px; cursor: pointer; border: 1px solid #333; white-space: nowrap; transition:0.3s; }
  .tab.active { background: #00b894; color: #fff; border-color: #00b894; }

  /* ACTION TOOLBAR */
  .action-toolbar { display: none; background: #252736; padding: 10px 15px; border-radius: 8px; margin-bottom: 20px; border: 1px solid #444; justify-content: space-between; align-items: center; animation: fadeIn 0.3s; }
  .toolbar-left { font-size: 14px; color: #bbb; }
  .toolbar-right { display: flex; gap: 10px; }
  .btn-action-delete { background: #e74c3c; color: white; border: none; padding: 8px 15px; border-radius: 6px; cursor: pointer; font-size: 13px; font-weight: bold; }
  .btn-action-clear { background: transparent; border: 1px solid #666; color: #bbb; padding: 8px 15px; border-radius: 6px; cursor: pointer; font-size: 13px; }
  .btn-action-clear:hover { border-color: #e74c3c; color: #e74c3c; }

  /* SAVED CARDS */
  .saved-card { display: flex; justify-content: space-between; align-items: center; background: #2c2c2c; padding: 15px; border-radius: 12px; margin-bottom: 15px; transition: 0.3s; border-left: 4px solid #00b894; }
  .saved-card:hover { transform: translateY(-3px); box-shadow: 0 5px 15px rgba(0,0,0,0.3); }
  .card-left { display: flex; align-items: center; gap: 15px; }
  .icon-box { width: 45px; height: 45px; background: rgba(255, 255, 255, 0.05); border-radius: 8px; display: flex; justify-content: center; align-items: center; font-size: 20px; }
  .card-info h4 { font-size: 16px; margin-bottom: 4px; color: #fff; }
  .card-info small { color: #bbb; font-size: 12px; }
  .card-info .tag { background: #333; padding: 2px 6px; border-radius: 4px; font-size: 10px; margin-left: 5px; color: #00b894; }

  .card-actions { display: flex; gap: 10px; }
  .action-btn { background: none; border: none; cursor: pointer; font-size: 18px; padding: 5px; transition: 0.2s; border-radius: 50%; }
  .action-btn.view { color: #3498db; }
  .select-checkbox { display: none; width: 18px; height: 18px; margin-right: 15px; accent-color: #e74c3c; cursor: pointer; }

  /* FOOTER */
  .footer{position:fixed;bottom:0;left:0;width:100%;display:flex;justify-content:space-around;background:#0a0a0a;padding:15px;font-size:14px;border-top:1px solid #2c2c2c;}
  .footer a{color:#f0f0f0;text-decoration:none;}
  .footer div:hover{color:#00b894;cursor:pointer;}
  .footer .active a { color: #00b894; font-weight: bold; }

  @keyframes fadeIn { from{opacity:0; transform:translateY(-10px);} to{opacity:1; transform:translateY(0);} }
  @media(max-width: 600px) { .saved-card { flex-direction: column; align-items: flex-start; gap: 10px; } .card-actions { align-self: flex-end; } }
</style>
</head>
<body>

<?php if(isset($_SESSION['status'])) { ?>
    <div id="statusPopup" style="position: fixed; top: 20px; left: 50%; transform: translateX(-50%); background: #00b894; color: white; padding: 12px 25px; border-radius: 50px; font-weight: bold; box-shadow: 0 5px 20px rgba(0,0,0,0.3); z-index: 9999; text-align: center; min-width: 250px;">
        <?php echo $_SESSION['status']; ?>
    </div>
    <script>
        setTimeout(function(){
            var popup = document.getElementById('statusPopup');
            popup.style.transition = "opacity 0.5s ease, transform 0.5s ease";
            popup.style.opacity = "0"; 
            popup.style.transform = "translateX(-50%) translateY(-20px)";
            setTimeout(function(){ popup.style.display = 'none'; }, 500); 
        }, 1500); // <-- Change this to 300 if you want 0.3s
    </script>
    <?php unset($_SESSION['status']); ?>
<?php } ?>
<div class="container">

  <div class="page-header">
    <h2>Saved Items</h2>
    <button type="button" class="manage-btn" id="manageBtn" onclick="toggleSelectionMode()">Manage / Clear</button>
  </div>

  <div class="tabs">
    <div class="tab active" onclick="filterItems('all', this)">All Items</div>
    <div class="tab" onclick="filterItems('question', this)">Questions</div>
    <div class="tab" onclick="filterItems('Note', this)">Notes</div>
    <div class="tab" onclick="filterItems('Test', this)">Tests</div>
  </div>

  <form action="" method="POST" id="deleteForm">
      
      <input type="hidden" name="delete_scope" id="scopeInput" value="all">

      <div class="action-toolbar" id="actionBar">
          <div class="toolbar-left">Select items to remove</div>
          <div class="toolbar-right">
              <button type="submit" name="clear_all_context_btn" class="btn-action-clear" id="clearContextBtn" onclick="return confirm('Are you sure?')">Clear All</button>
              <button type="submit" name="delete_selected_btn" class="btn-action-delete">Delete Selected</button>
          </div>
      </div>

      <div class="saved-list">
          <?php
          if(count($all_items) > 0)
          {
              foreach($all_items as $row)
              {
                  $type = $row['type'];
                  $title = ($type == 'question') ? substr($row['title'], 0, 60).'...' : $row['title'];
                  $color = '#00b894'; $icon = '❓';
                  if($type == 'Test') { $color = '#9b59b6'; $icon = '📝'; }
                  if($type == 'Note') { $color = '#3498db'; $icon = '📄'; }
                  ?>
                  <div class="saved-card item-card" style="border-left-color: <?php echo $color; ?>;" data-cat="<?php echo $type; ?>">
                    <div class="card-left">
                      <input type="checkbox" name="selected_ids[]" value="<?php echo $row['id']; ?>" class="select-checkbox">
                      <div class="icon-box"><?php echo $icon; ?></div>
                      <div class="card-info">
                        <h4><?php echo $title; ?></h4>
                        <small>Saved: <?php echo date('d M', strtotime($row['saved_at'])); ?> <span class="tag" style="color:<?php echo $color; ?>"><?php echo ucfirst($type); ?></span></small>
                      </div>
                    </div>
                    <div class="card-actions normal-actions">
    
    <?php if($type == 'Test') { ?>
        <a href="level_page.php?subject=<?php echo $row['title']; ?>" class="action-btn view">▶️</a>
    
    <?php } elseif($type == 'Note') { ?>
        <a href="notes_page.php?subject=<?php echo $row['title']; ?>" class="action-btn view" title="Open Notes">📂</a>
    
    <?php } else { ?>
        <button type="button" class="action-btn view">👁️</button>
    
    <?php } ?>

</div>
                  </div>
                  <?php
              }
          }
          else { echo "<p style='text-align:center; color:#666; margin-top:50px;'>No saved items found.</p>"; }
          ?>
      </div>
  </form>

<div class="footer">
    <div><a href="dashboard_page.php">🏠︎ Home</a></div>
    <div><a href="activity_page.php">📑 Activity</a></div>
    <div class="active"><a href="saved_page.php">❤️ Saved</a></div>
    <div><a href="profile_page.php">👤 Profile</a></div>
  </div>
</div> 

<script>
    function filterItems(category, btnElement) {
        document.querySelectorAll('.tab').forEach(t => t.classList.remove('active'));
        btnElement.classList.add('active');
        document.getElementById('scopeInput').value = category;
        const clearBtn = document.getElementById('clearContextBtn');
        if(category === 'all') clearBtn.innerText = "Clear All Items";
        else clearBtn.innerText = "Clear All " + category + "s";

        let cards = document.querySelectorAll('.item-card');
        cards.forEach(card => {
            if (category === 'all' || card.getAttribute('data-cat') === category) {
                card.style.display = 'flex';
            } else {
                card.style.display = 'none';
                let cb = card.querySelector('.select-checkbox');
                if(cb) cb.checked = false;
            }
        });
    }

    function toggleSelectionMode() {
        let checkboxes = document.querySelectorAll('.select-checkbox');
        let actions = document.querySelectorAll('.normal-actions');
        let actionBar = document.getElementById('actionBar');
        let btn = document.getElementById('manageBtn');

        if (actionBar.style.display === 'flex') {
            checkboxes.forEach(cb => { cb.style.display = 'none'; cb.checked = false; });
            actions.forEach(ac => ac.style.display = 'flex');
            actionBar.style.display = 'none';
            btn.innerText = "Manage / Clear";
            btn.style.color = "#e74c3c";
            btn.style.background = "transparent";
        } else {
            checkboxes.forEach(cb => {
                if(cb.closest('.item-card').style.display !== 'none') {
                    cb.style.display = 'block';
                }
            });
            actions.forEach(ac => ac.style.display = 'none');
            actionBar.style.display = 'flex';
            btn.innerText = "Done";
            btn.style.background = "#e74c3c";
            btn.style.color = "white";
        }
    }
</script>

</body>
</html>