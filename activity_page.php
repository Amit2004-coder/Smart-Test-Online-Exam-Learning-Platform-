<?php
session_start();
include('config.php');
if(!isset($_SESSION['auth'])) { header("Location: index.php"); exit(0); }
$user_id = $_SESSION['auth_user']['user_id'];

$total_tests = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM results WHERE user_id='$user_id'"))['count'];
$total_minutes = mysqli_fetch_assoc(mysqli_query($conn, "SELECT SUM(time_taken) as total_time FROM results WHERE user_id='$user_id'"))['total_time'];
$display_time = ($total_minutes > 0) ? round($total_minutes / 60, 1) . "h" : "0h";
$avg_data = mysqli_fetch_assoc(mysqli_query($conn, "SELECT SUM(score) as total_correct, SUM(total_questions) as total_q FROM results WHERE user_id='$user_id'"));
$avg_score = ($avg_data['total_q'] > 0) ? round(($avg_data['total_correct'] / $avg_data['total_q']) * 100) . "%" : "0%";

$chart_data = [];
$chart_run = mysqli_query($conn, "SELECT total_questions, start_time, time_taken FROM results WHERE user_id='$user_id' ORDER BY start_time DESC LIMIT 7");
while($r = mysqli_fetch_assoc($chart_run)) { $chart_data[] = $r; }
$chart_data = array_reverse($chart_data);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Activity | SMART TEST</title>
<style>
*{box-sizing:border-box;margin:0;padding:0;font-family:'Segoe UI',sans-serif;}
body{background:#1d1f2a;color:#f0f0f0;min-height:100vh;padding-bottom:100px; font-size: 16px;}
a{text-decoration:none;color:inherit;}
.container{max-width:900px;width:95%;margin:0 auto;padding:25px;}

/* Header Section */
.page-header{margin-bottom:30px;border-bottom:1px solid #2c2c2c;padding-bottom:20px;}
.page-header h2{color:#00b894;font-size:28px;margin-bottom:8px;}
.page-header p{color:#bbb;font-size:16px;}

/* Stats Section */
.stats-row{display:flex;gap:15px;margin-bottom:35px;flex-wrap:wrap;}
.stat-card{flex:1;min-width:145px;background:#2c2c2c;padding:22px;border-radius:15px;text-align:center;cursor:pointer;transition:0.3s;position:relative;}
.stat-card:hover{transform:translateY(-5px);background:#323444;}
.stat-card h3{font-size:32px;color:#fff;margin-bottom:8px;}
.stat-card span{font-size:13px;color:#aaa;text-transform:uppercase;letter-spacing:1px; font-weight: 600;}
.stat-card.green h3{color:#00b894;}.stat-card.blue h3{color:#3498db;}.stat-card.purple h3{color:#9b59b6;}

#timeHistory{display:none;background:#15171f;padding:12px;border-radius:10px;margin-top:12px;font-size:14px;color:#bbb;text-align:left;border:1px solid #333; line-height: 1.6;}

/* Performance Chart */
.chart-section{background:#2c2c2c;padding:25px;border-radius:15px;margin-bottom:35px;}
.bars{display:flex;justify-content:space-around;align-items:flex-end;height:160px;border-bottom:2px solid #444;padding-bottom:8px;gap:8px;}
.bar{flex:1;max-width:40px;background:#00b894;border-radius:5px 5px 0 0;position:relative;min-height:8px;transition:0.5s;}
.bar span{position:absolute;top:-25px;left:50%;transform:translateX(-50%);font-size:13px;color:#fff;font-weight:bold;}
.days{display:flex;justify-content:space-around;margin-top:15px;color:#bbb;font-size:13px; font-weight: 500;}

/* Recent History List */
.list-header{margin-bottom:20px;display:flex;justify-content:space-between;align-items:center;}
.list-header h3 {font-size: 20px;}
.list-header a{font-size:14px;color:#00b894;border:1px solid #00b894;padding:6px 12px;border-radius:6px; font-weight: 600;}

.activity-item{display:flex;justify-content:space-between;align-items:center;background:#2c2c2c;padding:18px;border-radius:15px;margin-bottom:15px;transition:0.2s;}
.activity-item:hover{background:#323444;transform:translateX(8px);}
.item-left{display:flex;align-items:center;gap:15px;}
.icon-box{width:50px;height:50px;background:rgba(0, 184, 148, 0.1);color:#00b894;display:flex;justify-content:center;align-items:center;border-radius:10px;font-size:22px;}
.item-left h4 {font-size: 17px; margin-bottom: 4px;}
.item-left small {font-size: 13px; color: #888;}

.item-right{text-align:right;display:flex;flex-direction:column;gap:8px;align-items:flex-end;}
.score-badge{background:#1d1f2a;padding:6px 12px;border-radius:8px;font-weight:bold;border:1px solid #333;font-size:15px;}
.score-badge.high{color:#00b894;border-color:#00b894;}.score-badge.avg{color:#f1c40f;border-color:#f1c40f;}

.cert-link{font-size:12px;background:#00b894;color:white;padding:5px 12px;border-radius:6px;font-weight:bold;display:inline-block; transition: 0.2s;}
.cert-link:hover {background: #019e7e;}

/* Footer Navigation */
.footer{position:fixed;bottom:0;left:0;width:100%;display:flex;justify-content:space-around;background:#0a0a0a;padding:18px;border-top:1px solid #2c2c2c;z-index:1000;}
.footer a{color:#f0f0f0;font-size:16px; font-weight: 500;}
.footer .active-link{color:#00b894;font-weight:bold;}

/* Responsive Layout */
@media(max-width:600px){
    .stat-card{flex:1 1 calc(50% - 10px);}
    .stat-card:last-child{flex:1 1 100%;}
    .page-header h2 {font-size: 24px;}
    .item-left h4 {font-size: 15px;}
}
</style>
</head>
<body>
<div class="container">
    <div class="page-header"><h2>My Activity</h2><p>Track your progress and achievements.</p></div>
    
    <div class="stats-row">
        <div class="stat-card green"><h3><?=$total_tests?></h3><span>Tests Taken</span></div>
        <div class="stat-card blue"><h3><?=$avg_score?></h3><span>Avg Score</span></div>
        <div class="stat-card purple" onclick="toggleTimeHistory()"><h3><?=$display_time?></h3><span>Time Spent</span>
            <div id="timeHistory"><strong>Last 7 History:</strong><br>
                <?php foreach(array_reverse($chart_data) as $row): ?>
                • <?=date('d M', strtotime($row['start_time']))?>: <?=$row['time_taken']?> mins<br>
                <?php endforeach; ?>
            </div>
        </div>
    </div>

    

    <div class="chart-section"><h4 style="margin-bottom:20px;font-size:16px;color:#00b894;">Performance (Last 7 Tests)</h4>
        <div class="bars">
            <?php $max_q=1; foreach($chart_data as $d) if(isset($d['total_questions']) && $d['total_questions']>$max_q) $max_q = $d['total_questions'];
            foreach($chart_data as $data): 
                $q_val = $data['total_questions'] ?? 0;
                $h=($q_val/$max_q)*100; ?>
            <div class="bar" style="height:<?=max($h,10)?>%"><span><?=$q_val?></span></div>
            <?php endforeach; ?>
        </div>
        <div class="days"><?php foreach($chart_data as $data): ?><div style="flex:1;text-align:center;"><?=date('d M', strtotime($data['start_time']))?></div><?php endforeach; ?></div>
    </div>

    <div class="list-header"><h3>Recent History</h3><a href="activity_page.php?view=all">View All</a></div>

    <?php 
    $limit=(isset($_GET['view'])&&$_GET['view']=='all')?50:10;
    $history=mysqli_query($conn,"SELECT * FROM results WHERE user_id='$user_id' ORDER BY start_time DESC LIMIT $limit");
    while($row=mysqli_fetch_assoc($history)): 
        $percent=($row['total_questions']>0)?($row['score']/$row['total_questions'])*100:0; 
        $bc=($percent>=70)?'high':'avg'; 
    ?>
    <div class="activity-item">
        <div class="item-left">
            <div class="icon-box">📝</div>
            <div>
                <h4><?=$row['subject']?>: <?=$row['level']?></h4>
                <small><?=date('d M Y',strtotime($row['start_time']))?> • <?=$row['total_questions']?> Questions</small>
            </div>
        </div>
        <div class="item-right">
            <div class="score-badge <?=$bc?>"><?=$row['score']?>/<?=$row['total_questions']?></div>
            <?php if($percent >= 40): ?>
                <a href="certificate_page.php?id=<?=$row['id']?>" class="cert-link">View Certificate 📜</a>
            <?php endif; ?>
        </div>
    </div>
    <?php endwhile; ?>
</div>

<script>function toggleTimeHistory(){var x=document.getElementById("timeHistory");x.style.display=(x.style.display==="block")?"none":"block";}</script>


<div class="footer">
  <div><a href="dashboard_page.php">🏠︎ Home</a></div>
  <div><a href="activity_page.php">📑 Activity</a></div>
  <div><a href="saved_page.php">❤️ Saved</a></div>
  <div><a href="profile_page.php">👤 Profile</a></div>
</div>
</body>
</html>