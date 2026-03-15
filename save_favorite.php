<?php
session_start();
include('config.php');

if(!isset($_SESSION['auth'])) {
    $_SESSION['fav_popup'] = ['status'=>'error', 'title'=>'Login Required!', 'msg'=>'Please login first.'];
    header("Location: index.php");
    exit(0);
}

$type = isset($_GET['type']) ? $_GET['type'] : ''; 
$title = isset($_GET['name']) ? $_GET['name'] : ''; 
$user_id = $_SESSION['auth_user']['user_id'];
$user_name = $_SESSION['auth_user']['full_name'];

if($type != '' && $title != '')
{
    // 1. Check karo: Kya ye pehle se saved hai?
    $check_query = "SELECT * FROM saved_items WHERE user_id='$user_id' AND title='$title' AND type='$type'";
    $check_run = mysqli_query($conn, $check_query);

    if(mysqli_num_rows($check_run) > 0)
    {
        // --- DELETE (UNFAVORITE) ---
        $delete_query = "DELETE FROM saved_items WHERE user_id='$user_id' AND title='$title' AND type='$type'";
        mysqli_query($conn, $delete_query);

        // Remove Popup Message
        $_SESSION['fav_popup'] = [
            'status' => 'warning', // Warning icon for remove
            'title' => 'Removed! 🗑️',
            'msg' => "<b>$title</b> has been removed from your favorites."
        ];
    }
    else
    {
        // --- INSERT (FAVORITE) ---
        $insert_query = "INSERT INTO saved_items (user_id, title, type) VALUES ('$user_id', '$title', '$type')";
        mysqli_query($conn, $insert_query);

        // Success Popup Message
        $_SESSION['fav_popup'] = [
            'status' => 'success', // Party icon for add
            'title' => 'Added to Favorites! ❤️',
            'msg' => "Great! <b>$title</b> is now in your saved list."
        ];
    }
}

// Wapas usi page par bhejo jahan se click kiya tha
header("Location: dashboard_page.php");
exit(0);
?>