<?php
session_start();
include('config.php'); // Database connection zaroori hai

// ==========================================
// 1. REGISTRATION LOGIC
// ==========================================
if(isset($_POST['register_btn']))
{
    // Data sanitize karke lena
    $full_name = mysqli_real_escape_string($conn, $_POST['full_name']);
    $phone = mysqli_real_escape_string($conn, $_POST['phone']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $password = mysqli_real_escape_string($conn, $_POST['password']);
    
    // Username banana
    $username = "@" . explode("@", $email)[0];

    // Check karna ki Email pehle se hai ya nahi
    $check_email = "SELECT * FROM users WHERE email='$email' LIMIT 1";
    $check_email_run = mysqli_query($conn, $check_email);

    if(mysqli_num_rows($check_email_run) > 0)
    {
        $_SESSION['status'] = "Email ID already exists!";
        header("Location: index.php");
        exit(0);
    }
    else
    {
        // Data Insert karna
        $query = "INSERT INTO users (full_name, username, email, phone, password) VALUES ('$full_name', '$username', '$email', '$phone', '$password')";
        $query_run = mysqli_query($conn, $query);

        if($query_run)
        {
            $_SESSION['status'] = "Registration Successful! Please Login.";
            header("Location: index.php");
            exit(0);
        }
        else
        {
            $_SESSION['status'] = "Registration Failed! Error: " . mysqli_error($conn);
            header("Location: index.php");
            exit(0);
        }
    }
}

// ==========================================
// 2. LOGIN LOGIC (Jo abhi add kiya hai)
// ==========================================
if(isset($_POST['login_btn']))
{
    $email_or_phone = mysqli_real_escape_string($conn, $_POST['email_or_phone']);
    $password = mysqli_real_escape_string($conn, $_POST['password']);

    // Check karo user Email ya Phone se exist karta hai kya
    $login_query = "SELECT * FROM users WHERE email='$email_or_phone' OR phone='$email_or_phone' LIMIT 1";
    $login_query_run = mysqli_query($conn, $login_query);

    if(mysqli_num_rows($login_query_run) > 0)
    {
        $row = mysqli_fetch_array($login_query_run);
        
        // Password Match Karna (Filhal simple text check)
        if($password == $row['password'])
        {
            // --- SUCCESS ---
            $_SESSION['auth'] = true;
            $_SESSION['auth_user'] = [
                'user_id' => $row['id'],
                'full_name' => $row['full_name'],
                'email' => $row['email'],
                'username' => $row['username'],
                'profile_pic' => $row['profile_pic'],
                'points' => $row['points']
            ];

            $_SESSION['status'] = "Login Successful! Welcome back.";
            header("Location: dashboard_page.php");
            exit(0);
        }
        else
        {
            // --- WRONG PASSWORD ---
            $_SESSION['status'] = "Invalid Password!";
            header("Location: index.php");
            exit(0);
        }
    }
    else
    {
        // --- USER NOT FOUND ---
        $_SESSION['status'] = "Invalid Email or Phone Number!";
        header("Location: index.php");
        exit(0);
    }
}

?>