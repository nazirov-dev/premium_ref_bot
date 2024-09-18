<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Jasur Premium Bot | Verification</title>
    <script src="https://telegram.org/js/telegram-web-app.js"></script>
    <script src="{{ asset('js/lottie-web.min.js') }}"></script>
    <script src="{{ asset('js/bundle.js') }}"></script>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: var(--tg-theme-bg-color, #ffffff);
            color: var(--tg-theme-text-color, #000000);
            margin: 0;
            padding: 0;
            height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            transition: background-color 0.3s, color 0.3s;
        }

        .container {
            background-color: var(--tg-theme-secondary-bg-color, #f0f0f0);
            padding: 20px;
            border-radius: 16px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
            width: 90%;
            max-width: 400px;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            height: 90%;
        }

        .header {
            background-color: var(--tg-theme-hint-color, #999999);
            padding: 10px;
            border-radius: 12px 12px 0 0;
            text-align: center;
        }

        .title {
            font-size: 24px;
            font-weight: bold;
            margin: 10px 0;
            color: var(--tg-theme-text-color, #000000);
        }

        .hint {
            font-size: 16px;
            color: var(--tg-theme-hint-color, #999999);
        }

        .btn {
            padding: 12px;
            font-size: 16px;
            color: var(--tg-theme-button-text-color, #ffffff);
            background-color: var(--tg-theme-button-color, #0088cc);
            border: none;
            border-radius: 8px;
            cursor: pointer;
            width: 100%;
            transition: background-color 0.3s;
            display: none;
        }

        .btn:hover {
            background-color: #006999;
        }

        #sticker-container {
            width: 200px;
            height: 200px;
            margin: 20px auto;
            overflow: hidden;
        }

        .content {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            flex: 1;
        }

        .captcha {
            margin: 20px 0;
            text-align: center;
            font-size: 18px;
        }

        .captcha input {
            padding: 8px;
            font-size: 16px;
            width: 100%;
            box-sizing: border-box;
            text-align: center;
        }
    </style>
</head>

<body>
    <div class="container">
        <div class="header">
            <span class="title">✈️</span>
            <span class="hint">Jasur Premium Bot</span>
        </div>
        <div class="content">
            <div id="sticker-container"></div>
            <div class="captcha">
                <p id="captcha-question"></p>
                <input type="text" id="captcha-answer" placeholder="Answer here" />
                <p id="captcha-error" style="color:red;display:none;">Captcha javobi noto'g'ri qaytadan urinib ko'ring!
                </p>
            </div>
            <button class="btn" id="main-button" disabled>Tasdiqlash ✅</button>
        </div>
    </div>

    <script>
        var visitorId = '';
        async function getClientInfo() {
            visitorId = await get_visitor_id();
            let ipAddress = '1.1.1.1';
            try {
                const ipResponse = await fetch('https://api.ipify.org?format=json');
                const ipData = await ipResponse.json();
                ipAddress = ipData.ip;
            } catch (error) {
                console.error('Error fetching IP address:', error);
            }

            const info = {
                timeOpened: new Date().toLocaleString(),
                timezone: (new Date()).getTimezoneOffset() / 60,
                browserLanguage: navigator.language,
                browserPlatform: navigator.platform,
                sizeScreenW: screen.width,
                sizeScreenH: screen.height,
                sizeAvailW: screen.availWidth,
                sizeAvailH: screen.availHeight,
            };

            const userAgent = navigator.userAgent;
            info.ipAddress = ipAddress;
            info.userAgent = userAgent;
            info.fingerprint = visitorId;
            return info;
        }

        document.addEventListener('DOMContentLoaded', async function() {
            const tg = window.Telegram.WebApp;

            tg.ready();
            tg.expand();
            tg.MainButton.disable();
            const info = await getClientInfo();
            document.body.style.backgroundColor = tg.themeParams.bg_color || '#ffffff';
            document.body.style.color = tg.themeParams.text_color || '#000000';
            document.querySelector('.container').style.backgroundColor = tg.themeParams.secondary_bg_color ||
                '#f0f0f0';
            document.querySelector('.header').style.backgroundColor = tg.themeParams.header_bg_color ||
                '#ffffff';

            const animation = lottie.loadAnimation({
                container: document.getElementById('sticker-container'),
                renderer: 'svg',
                loop: true,
                autoplay: true,
                path: window.location.origin + '/js/lottie.json'
            });

            // Generate captcha
            const num1 = Math.floor(Math.random() * 10) + 1;
            const num2 = Math.floor(Math.random() * 10) + 1;
            const captchaAnswer = num1 + num2;
            document.getElementById('captcha-question').textContent =
                `Quyidagi matematik amalning javobini kiriting: ${num1} + ${num2}?`;

            // Verify captcha answer
            document.getElementById('captcha-answer').addEventListener('input', function() {
                const userAnswer = parseInt(this.value);
                if (userAnswer === captchaAnswer) {
                    tg.MainButton.disable();
                    document.getElementById('captcha-error').style.display = 'none';
                } else {
                    tg.MainButton.enable();
                    document.getElementById('captcha-error').style.display = 'block';
                }
            });

            tg.MainButton.onClick(function() {
                let data = {
                    info: info
                }
                tg.sendData(JSON.stringify(data));
                tg.close();
            });
        });
    </script>
</body>

</html>
