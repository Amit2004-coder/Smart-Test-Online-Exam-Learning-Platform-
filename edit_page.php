<?php
session_start();
include('config.php');

// 1. Security Check
if(!isset($_SESSION['auth'])) {
    header("Location: index.php");
    exit(0);
}

// ------------------------------------------
//  UPDATE LOGIC (Yahi par code add kar diya)
// ------------------------------------------
if(isset($_POST['update_btn']))
{
    $user_id = $_SESSION['auth_user']['user_id'];
    
    $full_name = mysqli_real_escape_string($conn, $_POST['full_name']);
    $phone = mysqli_real_escape_string($conn, $_POST['phone']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $bio = mysqli_real_escape_string($conn, $_POST['bio']);
    $password = $_POST['password'];
    $old_image = $_POST['old_image'];
    $new_image = $_FILES['profile_image']['name'];

    // Password Logic
    if($password != "") {
        $password_query = ", password='$password'";
    } else {
        $password_query = "";
    }

    // Image Logic
    if($new_image != "") {
        $image_ext = pathinfo($new_image, PATHINFO_EXTENSION);
        $update_filename = time() . '.' . $image_ext;
    } else {
        $update_filename = $old_image;
    }

    // Update Query
    $query = "UPDATE users SET full_name='$full_name', phone='$phone', email='$email', bio='$bio', profile_pic='$update_filename' $password_query WHERE id='$user_id'";
    $query_run = mysqli_query($conn, $query);

    if($query_run)
    {
        if($new_image != "") {
            move_uploaded_file($_FILES['profile_image']['tmp_name'], "Assets/".$update_filename);
        }

        // Session Update
        $_SESSION['auth_user']['full_name'] = $full_name;
        $_SESSION['auth_user']['profile_pic'] = $update_filename;

        // Redirect wapas Profile page par
        echo "<script>alert('Profile Updated Successfully'); window.location.href='profile_page.php';</script>";
        exit(0);
    }
    else
    {
        echo "<script>alert('Update Failed!');</script>";
    }
}

// ------------------------------------------
//  FETCH OLD DATA (Form bharne ke liye)
// ------------------------------------------
$current_user_id = $_SESSION['auth_user']['user_id'];
$query = "SELECT * FROM users WHERE id='$current_user_id'";
$query_run = mysqli_query($conn, $query);

if(mysqli_num_rows($query_run) > 0)
{
    $row = mysqli_fetch_assoc($query_run);
    $pic = "Assets/" . $row['profile_pic'];
    if(!file_exists($pic) || $row['profile_pic'] == 'default.png') {
        $pic = "Assets/logo2.jpg"; 
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>SMART TEST - Edit Profile</title>
<style>
/* --- SAME CSS AS PROVIDED --- */
* { box-sizing: border-box; margin: 0; padding: 0; font-family: 'Segoe UI', sans-serif; }
body { background: #1d1f2a; color: #f0f0f0; min-height: 100vh; padding-bottom: 100px; }
.container { max-width: 600px; width: 95%; margin: 0 auto; padding: 20px; }

/* Page Header */
.page-header { text-align: center; margin-bottom: 30px; border-bottom: 1px solid #2c2c2c; padding-bottom: 15px; }
.page-header h2 { color: #00b894; margin-bottom: 5px; }
.page-header p { color: #888; font-size: 14px; }

/* Profile Image Upload */
.profile-upload { display: flex; flex-direction: column; align-items: center; margin-bottom: 30px; position: relative; }
.img-wrapper { position: relative; width: 100px; height: 100px; }
.img-wrapper img { width: 100%; height: 100%; border-radius: 50%; object-fit: cover; border: 3px solid #00b894; }
.camera-icon { position: absolute; bottom: 0; right: 0; background: #00b894; color: #fff; width: 32px; height: 32px; border-radius: 50%; display: flex; justify-content: center; align-items: center; cursor: pointer; border: 2px solid #1d1f2a; }
#fileInput { display: none; }

/* Form Styling */
.form-group { margin-bottom: 20px; }
.form-group label { display: block; margin-bottom: 8px; color: #bbb; font-size: 14px; font-weight: 600; }
.form-group input, .form-group textarea { width: 100%; padding: 12px; background: #2c2c2c; border: 1px solid #444; border-radius: 8px; color: #fff; outline: none; transition: 0.3s; }
.form-group input:focus, .form-group textarea:focus { border-color: #00b894; }
.form-group textarea { resize: none; height: 80px; }

/* Buttons */
.btn-group { display: flex; gap: 15px; margin-top: 30px; }
.btn { flex: 1; padding: 12px; border: none; border-radius: 8px; font-weight: 600; cursor: pointer; font-size: 16px; transition: 0.3s; }
.btn-save { background: #00b894; color: #fff; }
.btn-save:hover { background: #019e7e; }
.btn-cancel { background: #e74c3c; color: #fff; }
.btn-cancel:hover { background: #c0392b; }

/* Footer */
.footer{position:fixed;bottom:0;left:0;width:100%;display:flex;justify-content:space-around;background:#0a0a0a;padding:15px;font-size:14px;border-top:1px solid #2c2c2c;}
.footer a{color:#f0f0f0;text-decoration:none;}
.footer div:hover{color:#00b894;cursor:pointer;}
.footer .active a { color: #00b894; font-weight: bold; }

</style>
</head>
<body>

<div class="container">
  
  <div class="page-header">
    <h2>Edit Profile</h2>
    <p>Update your personal details</p>
  </div>

  <form action="" method="POST" enctype="multipart/form-data"> 
    
    <div class="profile-upload">
      <div class="img-wrapper">
        <img id="profilePreview" src="<?php echo $pic; ?>" alt="Profile" onerror="this.src='Assets/aayanpic.jpg'">
        <label for="fileInput" class="camera-icon">📷</label>
      </div>
      <input type="file" name="profile_image" id="fileInput" accept="image/*" onchange="previewImage(event)">
      <input type="hidden" name="old_image" value="<?php echo $row['profile_pic']; ?>">
      <span style="font-size:12px; color:#666; margin-top:5px;">Tap camera to change</span>
    </div>

    <div class="form-group">
      <label>Full Name</label>
      <input type="text" name="full_name" value="<?php echo $row['full_name']; ?>" placeholder="Enter your name">
    </div>

    <div class="form-group">
      <label>Username (Cannot be changed)</label>
      <input type="text" value="<?php echo $row['username']; ?>" readonly style="background:#222; color:#777; cursor:not-allowed;">
    </div>

    <div class="form-group">
      <label>Phone Number</label>
      <input type="tel" name="phone" value="<?php echo $row['phone']; ?>" placeholder="Enter phone number">
    </div>

    <div class="form-group">
      <label>Email Address</label>
      <input type="email" name="email" value="<?php echo $row['email']; ?>" placeholder="Enter email">
    </div>

    <div class="form-group">
      <label>Bio / About Me</label>
      <textarea name="bio" placeholder="Write something about yourself..."><?php echo $row['bio']; ?></textarea>
    </div>

    <div class="form-group">
      <label>New Password</label>
      <input type="password" name="password" placeholder="Leave blank to keep current password">
    </div>

    <div class="btn-group">
      <button type="button" class="btn btn-cancel" onclick="history.back()">Cancel</button>
      <button type="submit" name="update_btn" class="btn btn-save">Save Changes</button>
    </div>

  </form>

</div>

<div class="footer">
  <div><a href="dashboard_page.php">🏠︎ Home</a></div>
  <div><a href="activity_page.php">📑 Activity</a></div>
  <div><a href="saved_page.php">❤️ Saved</a></div>
  <div class="active"><a href="profile_page.php">👤 Profile</a></div>
</div>

<script>
  function previewImage(event) {
    const reader = new FileReader();
    reader.onload = function(){
      const output = document.getElementById('profilePreview');
      output.src = reader.result;
    }
    reader.readAsDataURL(event.target.files[0]);
  }
</script>

</body>
</html>