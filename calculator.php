<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Calculator</title>
    <style>
        body { font-family: 'Courier New', monospace; background: #1a1a1a; color: #fff; display:flex; align-items:center; justify-content:center; height:100vh; margin:0; }
        .calc { background:#111; padding:20px; border-radius:12px; box-shadow:0 10px 30px rgba(0,0,0,0.6); width:320px; }
        .display { background:#000; color:#0ff; font-size:32px; padding:12px; border-radius:6px; text-align:right; margin-bottom:12px; min-height:48px; }
        .keys { display:grid; grid-template-columns: repeat(4, 1fr); gap:10px; }
        button { padding:18px; font-size:18px; border-radius:8px; border:none; cursor:pointer; background:#222; color:#fff; }
        button.op { background:#ff6b6b; }
        .nav { display:flex; justify-content:space-between; margin-bottom:12px; }
        a { color:#ff6b6b; text-decoration:none; font-weight:bold; }
    </style>
</head>
<body>
    <div class="calc">
        <div class="nav">
            <a href="index.php">← Back</a>
            <a href="logout.php">Logout</a>
        </div>
        <div id="display" class="display">0</div>
        <div id="result" style="color:#0ff; font-size:18px; text-align:right; margin-top:6px;"></div>
        <div class="keys">
            <button type="button" onclick="press('7')">7</button>
            <button type="button" onclick="press('8')">8</button>
            <button type="button" onclick="press('9')">9</button>
            <button type="button" style="visibility:hidden"></button>

            <button type="button" onclick="press('4')">4</button>
            <button type="button" onclick="press('5')">5</button>
            <button type="button" onclick="press('6')">6</button>
            <button type="button" class="op" onclick="press('*')">×</button>

            <button type="button" onclick="press('1')">1</button>
            <button type="button" onclick="press('2')">2</button>
            <button type="button" onclick="press('3')">3</button>
            <button type="button" class="op" onclick="press('-')">−</button>

            <button type="button" onclick="press('0')">0</button>
            <button type="button" onclick="press('.')">.</button>
            <button type="button" onclick="evaluate()">=</button>
            <button type="button" class="op" onclick="press('+')">+</button>

            <button type="button" onclick="clearAll()">C</button>
            <button type="button" onclick="backspace()">⌫</button>
            <button type="button" onclick="press('(')">(</button>
            <button type="button" onclick="press(')')">)</button>
        </div>
    </div>

    <script>
        const display = document.getElementById('display');
        const resultDiv = document.getElementById('result');
        let expr = '';

        function press(val) {
            // clear previous computed result when user starts typing
            resultDiv.innerText = '';
            if (expr === '0' && val !== '.') expr = val;
            else expr += val;
            update();
        }

        function update() {
            display.innerText = expr === '' ? '0' : expr;
        }

        function clearAll() {
            expr = '';
            resultDiv.innerText = '';
            update();
        }

        function backspace() {
            expr = expr.slice(0, -1);
            resultDiv.innerText = '';
            update();
        }

        function evaluate() {
            if (!expr) return;
            // allow only numbers, operators (+ - *), parentheses, dot, and spaces
            const safe = /^[0-9+\-*().\s]+$/;
            if (!safe.test(expr)) {
                display.innerText = 'Error';
                expr = '';
                resultDiv.innerText = '';
                return;
            }
            try {
                // eslint-disable-next-line no-eval
                const result = eval(expr);
                resultDiv.innerText = '= ' + result;
                expr = String(result);
                update();
            } catch (e) {
                display.innerText = 'Error';
                expr = '';
                resultDiv.innerText = '';
            }
        }

        // keyboard support
        window.addEventListener('keydown', (e) => {
            if ((e.key >= '0' && e.key <= '9') || '+-*().'.includes(e.key)) {
                press(e.key);
            } else if (e.key === 'Enter' || e.key === '=') {
                evaluate();
            } else if (e.key === 'Backspace') {
                backspace();
            } else if (e.key.toLowerCase() === 'c') {
                clearAll();
            }
        });
    </script>
</body>
</html>
