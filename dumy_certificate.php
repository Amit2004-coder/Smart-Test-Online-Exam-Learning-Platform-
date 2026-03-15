<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>CERTIFICATE PROTOTYPE - SMART TEST</title>
<script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>
<style>
    * { box-sizing: border-box; margin: 0; padding: 0; font-family: 'Segoe UI', sans-serif; }
    body { background: #e0eafc; display: flex; flex-direction: column; justify-content: center; align-items: center; min-height: 100vh; gap: 20px; padding: 20px; }

    /* DEVELOPER CONTROLS */
    .dev-panel { background: #fff; padding: 15px; border-radius: 50px; box-shadow: 0 5px 15px rgba(0,0,0,0.1); display: flex; gap: 10px; z-index: 100; }
    .dev-btn { padding: 8px 15px; border: none; border-radius: 20px; cursor: pointer; font-weight: bold; font-size: 12px; transition: 0.3s; color: white; }

    /* CERTIFICATE MAIN BOX */
    #certificate-node {
        width: 900px; height: 600px; background: #fff;
        border: 15px solid; border-image-slice: 1; border-width: 15px;
        border-image-source: linear-gradient(45deg, #00b894, #00cec9, #0984e3, #6c5ce7);
        border-radius: 5px; padding: 50px; box-shadow: 0 20px 50px rgba(0,0,0,0.2);
        position: relative; text-align: center; overflow: hidden;
        display: flex; flex-direction: column; justify-content: space-between;
    }

    /* GOLDEN BORDER CLASS */
    .golden-border { border-image-source: linear-gradient(45deg, #D4AF37, #FFD700, #B8860B, #D4AF37) !important; }

    /* WATERMARK */
    #certificate-node::after {
        content: "SMART TEST"; position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%) rotate(-30deg);
        font-size: 140px; font-weight: 900; color: rgba(0,0,0,0.02); z-index: 0; pointer-events: none;
    }

    .logo-area { display: flex; justify-content: center; align-items: center; gap: 15px; position: relative; z-index: 1; }
    .logo-area h1 { font-size: 34px; color: #00b894; letter-spacing: 2px; font-weight: 800; }

    .recipient-name { font-size: 45px; color: #333; margin-top: 30px; font-weight: 800; text-decoration: underline; text-underline-offset: 10px; text-transform: uppercase; position: relative; z-index: 1; }
    .msg { font-size: 20px; color: #555; font-style: italic; margin-top: 15px; position: relative; z-index: 1; }
    .sub-detail { font-size: 24px; font-weight: 700; color: #222; margin-top: 10px; position: relative; z-index: 1; }

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

    <div class="dev-panel">
        <span style="font-size: 12px; align-self: center; font-weight: bold;">TEST DESIGN:</span>
        <button class="dev-btn" style="background: #e74c3c;" onclick="updateStars(1, false)">20% (1 Star)</button>
        <button class="dev-btn" style="background: #f39c12;" onclick="updateStars(3, false)">50% (3 Star)</button>
        <button class="dev-btn" style="background: #2ecc71;" onclick="updateStars(5, false)">90% (5 Star)</button>
        <button class="dev-btn" style="background: #D4AF37;" onclick="updateStars(5, true)">100% (Golden)</button>
    </div>

    <div id="certificate-node">
        <div class="logo-area">
            <img src="Assets/Smart test Logo.png" width="80" onerror="this.src='https://via.placeholder.com/80?text=LOGO'">
            <h1>SMART TEST</h1>
        </div>

        <div class="recipient-name">MOHAMMAD MOZZAMIL</div>
        
        <p class="msg">Congratulations! You have successfully completed your journey with SMART TEST.</p>
        
        <div class="sub-detail">Subject: MATHEMATICS (Mains Level)</div>

        <div class="stars-box" id="star-row">
            </div>

        <div class="signatures">
            <div class="sign">
                <img src="Assets/aayan signature.png" onerror="this.src='https://via.placeholder.com/150x60?text=Signature'">
                <br><b>Co-founder: Aayan Ahmad</b>
            </div>
            <div class="sign">
                <img src="Assets/mozzamil signature.png" onerror="this.src='https://via.placeholder.com/150x60?text=Signature'">
                <br><b>Director: Mozzamil Husain</b>
            </div>
        </div>
    </div>
    <button class="download-trigger" onclick="downloadImage()">Download PNG Prototype ⬇</button>

<script>
    // डिज़ाइन प्रीव्यू फंक्शन
    function updateStars(count, isGolden) {
        const node = document.getElementById('certificate-node');
        const starRow = document.getElementById('star-row');
        starRow.innerHTML = '';

        if(isGolden) {
            node.classList.add('golden-border');
        } else {
            node.classList.remove('golden-border');
        }

        for(let i=1; i<=5; i++) {
            let cls = "";
            if(i <= count) {
                cls = isGolden ? "golden" : "filled";
            }
            starRow.innerHTML += `<span class="star ${cls}">★</span>`;
        }
    }

    // इमेज डाउनलोड फंक्शन
    function downloadImage() {
        const btn = document.querySelector('.download-trigger');
        btn.innerText = "Processing...";
        html2canvas(document.querySelector("#certificate-node"), { scale: 2 }).then(canvas => {
            let link = document.createElement('a');
            link.download = 'SMART_TEST_DESIGN.png';
            link.href = canvas.toDataURL();
            link.click();
            btn.innerText = "Download PNG Prototype ⬇";
        });
    }

    // डिफ़ॉल्ट लुक (90%)
    window.onload = () => updateStars(5, false);
</script>

</body>
</html>