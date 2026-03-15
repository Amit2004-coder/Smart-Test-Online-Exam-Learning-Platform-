<?php
session_start();

// 1. Security Check
if(!isset($_SESSION['auth'])) {
    header("Location: index.php");
    exit(0);
}

// 2. Dashboard se aaya hua Subject pakadna
// Agar URL me subject nahi hai, to default 'General' maan lenge
$subject = isset($_GET['subject']) ? $_GET['subject'] : 'General';
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Select Level | SMART_TEST</title>
<link rel="icon" href="Assets/Smart test Logo.png" type="image/jpeg">

<style>
/* --- SAME CSS (NO CHANGES) --- */
*{box-sizing:border-box;margin:0;padding:0;font-family:'Segoe UI',Tahoma,Geneva,Verdana,sans-serif;}
body{background:#1d1f2a;color:#f0f0f0;min-height:100vh;display:flex;justify-content:center;align-items:center;}

.container{width:90%;max-width:900px;}
h1{text-align:center;margin-bottom:30px;color:#00b894;letter-spacing:1px;}

.levels{display:grid;grid-template-columns:repeat(auto-fit,minmax(260px,1fr));gap:20px;}

.level-card{
  background:#1f1f1f;
  border-radius:14px;
  padding:25px;
  text-align:center;
  box-shadow:0 6px 20px rgba(0,0,0,0.6);
  transition:0.3s;
  cursor:pointer;
}
.level-card:hover{transform:translateY(-6px);box-shadow:0 10px 30px rgba(0,0,0,0.8);}

.level-card h2{margin-bottom:10px;}
.level-card p{color:#bbb;font-size:14px;margin-bottom:20px;}

.level-card .questions{
  font-size:22px;
  font-weight:700;
  margin-bottom:15px;
}

.btn{
  display:inline-block;
  padding:12px 24px;
  border-radius:8px;
  background:#00b894;
  color:#fff;
  text-decoration:none;
  font-weight:600;
  transition:0.3s;
}
.btn:hover{background:#019e7e;}

.beginner{border-top:4px solid #2ecc71;}
.intermediate{border-top:4px solid #f1c40f;}
.advanced{border-top:4px solid #e74c3c;}

@media(max-width:480px){
  h1{font-size:22px;}
}
</style>
</head>

<body>

<div class="container">
  <h1>Select Level for <?php echo htmlspecialchars($subject); ?></h1>

  <div class="levels">

    <div class="level-card beginner">
      <h2>Beginner Level</h2>
      <p>Best for fundamentals & basics</p>
      <div class="questions">30 Questions</div>
      <a href="quiz_page.php?subject=<?php echo $subject; ?>&level=Beginner" class="btn">Start Test</a>
    </div>

    <div class="level-card intermediate">
      <h2>Intermediate Level</h2>
      <p>Moderate difficulty & concepts</p>
      <div class="questions">50 Questions</div>
      <a href="quiz_page.php?subject=<?php echo $subject; ?>&level=Intermediate" class="btn">Start Test</a>
    </div>

    <div class="level-card advanced">
      <h2>Advanced Level</h2>
      <p>High difficulty & exam level</p>
      <div class="questions">100 Questions</div>
      <a href="quiz_page.php?subject=<?php echo $subject; ?>&level=Advanced" class="btn">Start Test</a>
    </div>

  </div>
</div>

</body>
</html>