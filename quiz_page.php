<?php
session_start();
include('config.php');

// 1. Security Check
if(!isset($_SESSION['auth'])) {
    header("Location: index.php");
    exit(0);
}

// 2. User & Quiz Details
$user_id = $_SESSION['auth_user']['user_id'];
$subject = isset($_GET['subject']) ? mysqli_real_escape_string($conn, $_GET['subject']) : 'HTML'; 
$level = isset($_GET['level']) ? mysqli_real_escape_string($conn, $_GET['level']) : 'Beginner';

// 3. Question Limit Logic
$limit = 30; 
if($level == 'Intermediate') { $limit = 50; }
if($level == 'Advanced') { $limit = 100; }

// 4. Fetch Questions
$sql = "SELECT * FROM questions WHERE subject='$subject' AND level='$level' ORDER BY RAND() LIMIT $limit";
$result = mysqli_query($conn, $sql);

$questions_array = [];
if(mysqli_num_rows($result) > 0) {
    while($row = mysqli_fetch_assoc($result)) {
        $questions_array[] = $row;
    }
}

// 5. FETCH USER'S FAVORITE QUESTIONS (Ids)
// Taaki hum pehle se Heart ko Red dikha sakein agar wo saved hai
$fav_q_ids = [];
$fav_sql = "SELECT question_id FROM saved_items WHERE user_id='$user_id' AND type='question'";
$fav_run = mysqli_query($conn, $fav_sql);
while($frow = mysqli_fetch_assoc($fav_run)) {
    $fav_q_ids[] = $frow['question_id'];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Quiz | SMART_TEST</title>

<style>
/* --- BASE CSS --- */
*{box-sizing:border-box;margin:0;padding:0;font-family:'Segoe UI',Tahoma,Verdana,sans-serif;}
body{background:#1d1f2a;color:#f0f0f0;min-height:100vh;display:flex;justify-content:center;align-items:center;}

.container{width:95%;max-width:800px;background:#1f1f1f;border-radius:15px;padding:25px;box-shadow:0 8px 30px rgba(0,0,0,.6); position:relative;}

.top-bar{display:flex;justify-content:space-between;align-items:center;margin-bottom:20px;}
.subject{font-weight:600;color:#00b894;}
.timer{background:#2c2c2c;padding:6px 14px;border-radius:20px;font-size:14px;}

.progress{height:6px;background:#2c2c2c;border-radius:5px;overflow:hidden;margin-bottom:20px;}
.progress span{display:block;height:100%;width:0%;background:#00b894; transition: width 0.3s;}

/* Question Header with Heart */
.q-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px; }
.question-count{font-size:14px;color:#bbb;}

/* Heart Button Style */
.fav-btn {
    background: none; border: none; font-size: 24px; cursor: pointer; 
    color: #555; transition: 0.3s; padding: 0 10px;
}
.fav-btn:hover { transform: scale(1.2); }
.fav-btn.active { color: #e74c3c; } /* Red Heart */

.question{font-size:18px;margin-bottom:20px;line-height:1.5;}

.options{display:grid;grid-template-columns:1fr;gap:12px;}
.option{
  background:#2c2c2c; padding:14px 15px; border-radius:10px; cursor:pointer;
  transition:.3s; border:1px solid transparent;
}
.option:hover{background:#323444;border-color:#00b894;}
.option.selected { border-color: #00b894; background: #323444; }

.actions{display:flex;justify-content:space-between;margin-top:25px;}
.btn{
  padding:10px 22px; border-radius:20px; border:none; cursor:pointer; font-weight:600; transition:.3s;
}
.btn-prev{background:#2c2c2c;color:#fff;}
.btn-next{background:#00b894;color:#fff;}
.btn-next:hover{background:#019e7e;}

/* --- SUBMIT GREETING POPUP --- */
.submit-overlay {
    position: fixed; top: 0; left: 0; width: 100%; height: 100%;
    background: rgba(0,0,0,0.85); z-index: 9999;
    display: none; /* Hidden Default */
    justify-content: center; align-items: center; flex-direction: column;
}
.submit-box {
    background: #252736; padding: 40px; border-radius: 20px; text-align: center;
    border: 2px solid #00b894; box-shadow: 0 0 30px rgba(0, 184, 148, 0.5);
    animation: zoomIn 0.3s ease;
}
.submit-icon { font-size: 60px; margin-bottom: 15px; display: block; animation: bounce 1s infinite; }
@keyframes zoomIn { from{transform:scale(0.5);opacity:0;} to{transform:scale(1);opacity:1;} }
@keyframes bounce { 0%, 100% {transform:translateY(0);} 50% {transform:translateY(-10px);} }

@media(max-width:480px){ .question{font-size:16px;} }
</style>
</head>

<body>

<div class="submit-overlay" id="submitOverlay">
    <div class="submit-box">
        <span class="submit-icon">🎉</span>
        <h2 style="color:white; margin:0;">Test Submitted!</h2>
        <p style="color:#bbb; margin-top:10px; font-size: 14px;">Calculating your result...</p>
    </div>
</div>

<div class="container">

  <div class="top-bar">
    <div class="subject"><?php echo $subject; ?> • <?php echo $level; ?></div>
    <div class="timer" id="timer">⏱ 00:00</div>
  </div>

  <div class="progress"><span id="progressBar"></span></div>

  <div class="q-header">
      <div class="question-count" id="questionCount">Question 1 of <?php echo $limit; ?></div>
      
      <button class="fav-btn" id="favHeart" onclick="toggleFavorite()" title="Save Question">♡</button>
  </div>

  <div class="question" id="questionText">Loading Question...</div>

  <div class="options" id="optionsContainer"></div>

  <div class="actions">
    <button class="btn btn-prev" id="prevBtn" onclick="prevQuestion()">Previous</button>
    <button class="btn btn-next" id="nextBtn" onclick="nextQuestion()">Next</button>
  </div>

</div>

<form id="submitForm" action="dashboard_page.php" method="POST" style="display:none;">
    <input type="hidden" name="subject" value="<?php echo $subject; ?>">
    <input type="hidden" name="level" value="<?php echo $level; ?>">
    <input type="hidden" name="total_questions" value="<?php echo $limit; ?>">
    <input type="hidden" name="score" id="finalScore" value="0">
    <input type="hidden" name="time_taken" id="timeTaken" value="0">
</form>

<script>
  // 1. Data from PHP
  const questions = <?php echo json_encode($questions_array); ?>;
  const favoriteIDs = <?php echo json_encode($fav_q_ids); ?>; // IDs of saved questions
  const totalQuestions = questions.length;
  
  let currentQuestionIndex = 0;
  let userAnswers = {}; 
  let timerInterval;
  let seconds = 0;

  // --- TIMER ---
  function startTimer() {
      timerInterval = setInterval(() => {
          seconds++;
          const mins = Math.floor(seconds / 60).toString().padStart(2, '0');
          const secs = (seconds % 60).toString().padStart(2, '0');
          document.getElementById('timer').innerText = `⏱ ${mins}:${secs}`;
      }, 1000);
  }

  // --- LOAD QUESTION ---
  function loadQuestion(index) {
      if(totalQuestions === 0) {
          document.getElementById('questionText').innerText = "No questions found!";
          return;
      }

      const q = questions[index];
      
      // Update UI Text
      document.getElementById('questionText').innerText = q.question;
      document.getElementById('questionCount').innerText = `Question ${index + 1} of ${totalQuestions}`;
      
      // Progress Bar
      const progressPercent = ((index + 1) / totalQuestions) * 100;
      document.getElementById('progressBar').style.width = progressPercent + "%";

      // --- HEART LOGIC ---
      const heartBtn = document.getElementById('favHeart');
      // Check array if current Question ID is saved
      // Note: questions[index].id string/int type match zaroori hai
      if(favoriteIDs.includes(q.id.toString()) || favoriteIDs.includes(parseInt(q.id))) {
          heartBtn.classList.add('active');
          heartBtn.innerText = "❤️";
      } else {
          heartBtn.classList.remove('active');
          heartBtn.innerText = "♡";
      }

      // Options Render
      const optionsDiv = document.getElementById('optionsContainer');
      optionsDiv.innerHTML = ''; 

      const opts = [
          { key: 'A', val: q.option_a },
          { key: 'B', val: q.option_b },
          { key: 'C', val: q.option_c },
          { key: 'D', val: q.option_d }
      ];

      opts.forEach(opt => {
          const div = document.createElement('div');
          div.className = 'option';
          div.innerText = opt.val;
          if(userAnswers[index] === opt.key) { div.classList.add('selected'); }
          div.onclick = () => selectOption(index, opt.key, div);
          optionsDiv.appendChild(div);
      });

      // --- BUTTON LOGIC (CRITICAL FIX) ---
      const nextBtn = document.getElementById('nextBtn');
      
      // Remove old event listeners to prevent stacking logic issues
      // This is a cleaner way to swap functionality
      const newBtn = nextBtn.cloneNode(true);
      nextBtn.parentNode.replaceChild(newBtn, nextBtn);
      
      // Now configure the fresh button
      if(index === totalQuestions - 1) {
          newBtn.innerText = "Submit Test";
          newBtn.style.backgroundColor = "#e74c3c"; // Red Color
          newBtn.onclick = submitExam; // Assign Submit Function
      } else {
          newBtn.innerText = "Next";
          newBtn.style.backgroundColor = "#00b894"; // Green Color
          newBtn.onclick = nextQuestion; // Assign Next Function
      }

      // Previous button visibility
      document.getElementById('prevBtn').style.visibility = (index === 0) ? 'hidden' : 'visible';
  }

  function selectOption(qIndex, answerKey, element) {
      userAnswers[qIndex] = answerKey;
      const allOptions = document.querySelectorAll('.option');
      allOptions.forEach(opt => opt.classList.remove('selected'));
      element.classList.add('selected');
  }

  // --- FAVORITE TOGGLE (AJAX) ---
  function toggleFavorite() {
      const heartBtn = document.getElementById('favHeart');
      const currentQ = questions[currentQuestionIndex];
      const qID = currentQ.id;

      if(heartBtn.classList.contains('active')) {
          // Visual remove
          heartBtn.classList.remove('active');
          heartBtn.innerText = "♡";
          // Optional: Add logic to remove from DB here if desired
      } else {
          // Visual add
          heartBtn.classList.add('active');
          heartBtn.innerText = "❤️";
          
          // Send Request to Save
          fetch(`save_favorite.php?type=question&qid=${qID}`)
            .then(response => {
                // Add to local array so it stays red if we come back
                favoriteIDs.push(qID);
            })
            .catch(err => console.error(err));
      }
  }

  // --- NAVIGATION (FIXED) ---
  function nextQuestion() {
      if(currentQuestionIndex < totalQuestions - 1) {
          currentQuestionIndex++;
          loadQuestion(currentQuestionIndex);
      }
      // Removed the 'else { submitExam() }' block completely
  }

  function prevQuestion() {
      if(currentQuestionIndex > 0) {
          currentQuestionIndex--;
          loadQuestion(currentQuestionIndex);
      }
  }

  // --- SUBMIT EXAM LOGIC ---
  function submitExam() {
      // User confirmation
      if(!confirm("Are you sure you want to submit the test?")) return;
      
      // Stop Timer
      clearInterval(timerInterval);
      
      // 1. Show Greeting Popup
      document.getElementById('submitOverlay').style.display = 'flex';

      // 2. Calculate Score
      let score = 0;
      questions.forEach((q, index) => {
          if(userAnswers[index] === q.correct_option) { score += 1; }
      });

      // Update hidden inputs
      document.getElementById('finalScore').value = score;
      document.getElementById('timeTaken').value = Math.ceil(seconds / 60);
      
      // 3. Wait 1.3 Seconds (1300ms) then Submit Form
      setTimeout(() => {
          document.getElementById('submitForm').submit();
      }, 1300); 
  }

  // Init
  window.onload = function() {
      loadQuestion(0);
      startTimer();
  };
</script>
</body>
</html>