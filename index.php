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
    <title>Retro Arcade Dashboard</title>
    <style>
        body { font-family: 'Courier New', Courier, monospace; background: #1a1a1a; color: #fff; text-align: center; padding: 20px; margin: 0; }
        .nav { display: flex; justify-content: space-between; max-width: 800px; margin: 0 auto 30px; align-items: center; }
        a { color: #ff6b6b; font-weight: bold; text-decoration: none; padding: 10px; border: 1px solid #ff6b6b; border-radius: 5px; }
        a:hover { background: #ff6b6b; color: #111; }
        
        .main-menu-wrapper { display: flex; justify-content: center; gap: 50px; margin-top: 50px; flex-wrap: wrap; }
        .highscore-board { background: #222; padding: 20px; border-radius: 8px; border: 2px solid #444; text-align: left; min-width: 200px; }
        .highscore-board h2 { color: #00f5d4; margin-top: 0; }
        
        .menu-buttons { display: flex; flex-direction: column; gap: 10px; }
        .btn-game { padding: 15px; width: 220px; cursor: pointer; font-size: 16px; font-weight: bold; border: 2px solid #444; border-radius: 5px; background: #333; color: white; transition: 0.2s; }
        .btn-game:hover { background: #555; transform: scale(1.05); }
        
        .btn-back { padding: 10px 20px; background: #ff1493; color: white; font-weight: bold; border: none; border-radius: 5px; cursor: pointer; display: none; margin: 0 auto 20px auto; }
        .btn-back:hover { background: #c1106d; }
        
        .game-container { display: none; margin-top: 20px; }
        canvas { background: #000; display: block; margin: 0 auto; box-shadow: 0 0 15px rgba(255, 255, 255, 0.2); }
        #tetris { border: 5px solid #9b5de5; }
        #snake { border: 5px solid #9bc400; background: #111; }
        
        /* Tic Tac Toe Styles */
        #ttt-board { display: grid; grid-template-columns: repeat(3, 100px); gap: 5px; justify-content: center; margin: 20px auto; }
        .cell { width: 100px; height: 100px; background: #222; border: 2px solid #555; font-size: 60px; display: flex; align-items: center; justify-content: center; cursor: pointer; color: #fee440; }
        .cell:hover { background: #333; }
        #ttt-status { font-size: 24px; margin-bottom: 10px; color: #00bbf9; }
        
        /* Inputs for Mini-games */
        input { padding: 10px; margin: 5px; font-size: 16px; font-family: inherit; background: #222; color: #fff; border: 1px solid #555; border-radius: 4px; }
        input:focus { outline: none; border-color: #00f5d4; }
        .score-display { font-size: 24px; margin-bottom: 15px; font-weight: bold; }
    </style>
</head>
<body>

    <div class="nav">
        <h3>Welcome, <?php echo htmlspecialchars($_SESSION['username']); ?>!</h3>
        <a href="logout.php">Logout</a>
    </div>

    <button class="btn-back" id="back-btn" onclick="location.reload()">Back to Arcade Menu</button>

    <div class="main-menu-wrapper" id="menu">
        <div class="highscore-board">
            <h2>HIGH SCORES</h2>
            <p>Snake: <span id="hs-snake">0</span></p>
            <p>Tetris: <span id="hs-tetris">0</span></p>
        </div>
        <div class="menu-buttons">
            <button class="btn-game" onclick="showGame('tetris')">🕹️ Play Tetris</button>
            <button class="btn-game" onclick="showGame('snake')">🐍 Play Snake</button>
            <button class="btn-game" onclick="showGame('ttt')">❌ Tic Tac Toe ⭕</button>
            <button class="btn-game" onclick="showGame('madlibs')">📝 Mad Libs</button>
            <button class="btn-game" onclick="showGame('guess')">🔢 Guess Number</button>
        </div>
    </div>

    <!-- Tetris -->
    <div id="tetris-container" class="game-container">
        <div class="score-display" id="tetris-score" style="color: #9b5de5;">Score: 0</div>
        <canvas id="tetris" width="240" height="400" style="width: 360px; height: 600px;"></canvas>
    </div>

    <!-- Snake -->
    <div id="snake-container" class="game-container">
        <div class="score-display" id="snake-score" style="color: #9bc400;">Score: 0</div>
        <canvas id="snake" width="400" height="400"></canvas>
    </div>

    <!-- Tic Tac Toe -->
    <div id="ttt-container" class="game-container">
        <div id="ttt-status">Player X's Turn</div>
        <div id="ttt-board"></div>
        <button class="btn-game" onclick="resetTTT()">Restart Game</button>
    </div>
    
    <!-- Mad Libs -->
    <div id="madlibs-container" class="game-container">
        <h3>Create a Story!</h3>
        <input id="ml-adj" placeholder="Adjective (e.g. smelly)">
        <input id="ml-noun" placeholder="Noun (e.g. banana)">
        <br>
        <input id="ml-verb" placeholder="Past Tense Verb (e.g. ran)">
        <input id="ml-place" placeholder="Place (e.g. Walmart)">
        <br><br>
        <button class="btn-game" onclick="playMadLibs()">Generate Story</button>
        <p id="story-result" style="font-size: 24px; color: #f15bb5; max-width: 600px; margin: 20px auto; line-height: 1.5;"></p>
    </div>

    <!-- Guess the Number -->
    <div id="guess-container" class="game-container">
        <h3>I am thinking of a number between 1 and 100...</h3>
        <input type="number" id="guess-input" placeholder="Enter your guess">
        <button class="btn-game" style="width: 120px;" onclick="checkGuess()">Guess</button>
        <button class="btn-game" style="width: 120px;" onclick="resetGuess()">Reset</button>
        <p id="guess-result" style="font-size: 24px; color: #00f5d4; margin-top: 20px;"></p>
        <p id="guess-attempts" style="color: #aaa;"></p>
    </div>

    <script>
        // --- SYSTEM SETTINGS ---
        document.getElementById('hs-snake').innerText = localStorage.getItem('hs_snake') || 0;
        document.getElementById('hs-tetris').innerText = localStorage.getItem('hs_tetris') || 0;
        let currentGame = null;

        function showGame(game) {
            document.getElementById('menu').style.display = 'none';
            document.getElementById('back-btn').style.display = 'block';
            document.getElementById(game + '-container').style.display = 'block';
            currentGame = game;
            
            if(game === 'tetris') runTetris();
            if(game === 'snake') runSnake();
            if(game === 'ttt') resetTTT();
            if(game === 'guess') resetGuess();
        }

        // --- ENHANCED TIC TAC TOE ---
        let tttBoard = ['', '', '', '', '', '', '', '', ''];
        let tttTurn = 'X';
        let tttActive = true;
        const winConditions = [ [0,1,2], [3,4,5], [6,7,8], [0,3,6], [1,4,7], [2,5,8], [0,4,8], [2,4,6] ];

        function resetTTT() {
            tttBoard = ['', '', '', '', '', '', '', '', ''];
            tttTurn = 'X';
            tttActive = true;
            document.getElementById('ttt-status').innerText = "Player X's Turn";
            document.getElementById('ttt-status').style.color = "#00bbf9";
            const boardDiv = document.getElementById('ttt-board');
            boardDiv.innerHTML = '';
            for(let i=0; i<9; i++) {
                let cell = document.createElement('div');
                cell.className = 'cell';
                cell.onclick = () => handleTTTClick(cell, i);
                boardDiv.appendChild(cell);
            }
        }

        function handleTTTClick(cell, index) {
            if(tttBoard[index] !== '' || !tttActive) return;
            
            tttBoard[index] = tttTurn;
            cell.innerText = tttTurn;
            cell.style.color = tttTurn === 'X' ? '#fee440' : '#ff1493';
            
            checkTTTWin();
        }

        function checkTTTWin() {
            let won = false;
            for(let condition of winConditions) {
                let [a, b, c] = condition;
                if(tttBoard[a] && tttBoard[a] === tttBoard[b] && tttBoard[a] === tttBoard[c]) {
                    won = true;
                    break;
                }
            }

            if(won) {
                document.getElementById('ttt-status').innerText = `Player ${tttTurn} Wins! 🎉`;
                document.getElementById('ttt-status').style.color = "#00f5d4";
                tttActive = false;
                return;
            }

            if(!tttBoard.includes('')) {
                document.getElementById('ttt-status').innerText = "It's a Draw! 🤝";
                document.getElementById('ttt-status').style.color = "#fff";
                tttActive = false;
                return;
            }

            tttTurn = tttTurn === 'X' ? 'O' : 'X';
            document.getElementById('ttt-status').innerText = `Player ${tttTurn}'s Turn`;
        }

        // --- ENHANCED MAD LIBS ---
        function playMadLibs() { 
            const adj = document.getElementById('ml-adj').value || "mysterious";
            const noun = document.getElementById('ml-noun').value || "potato";
            const verb = document.getElementById('ml-verb').value || "danced";
            const place = document.getElementById('ml-place').value || "the moon";
            
            const story = `One day, a very ${adj} ${noun} decided it was time for an adventure. It completely lost its mind and ${verb} all the way to ${place}!`;
            document.getElementById('story-result').innerText = story; 
        }

        // --- ENHANCED GUESS THE NUMBER ---
        let guessTarget = 0;
        let guessAttempts = 0;

        function resetGuess() {
            guessTarget = Math.floor(Math.random() * 100) + 1;
            guessAttempts = 0;
            document.getElementById('guess-input').value = '';
            document.getElementById('guess-result').innerText = '';
            document.getElementById('guess-attempts').innerText = 'Attempts: 0';
        }

        function checkGuess() {
            let g = parseInt(document.getElementById('guess-input').value);
            if(isNaN(g)) return;
            
            guessAttempts++;
            let res = document.getElementById('guess-result');
            let att = document.getElementById('guess-attempts');
            
            att.innerText = 'Attempts: ' + guessAttempts;
            
            if(g === guessTarget) {
                res.innerText = `🎉 Correct! You found the number ${guessTarget} in ${guessAttempts} tries!`;
                res.style.color = "#00f5d4";
            } else if (g < guessTarget) {
                res.innerText = "Too Low! Try going higher. ⬆️";
                res.style.color = "#ffb703";
            } else {
                res.innerText = "Too High! Try going lower. ⬇️";
                res.style.color = "#ffb703";
            }
        }

        // --- COMPLETE TETRIS ENGINE ---
        function runTetris() {
            const canvas = document.getElementById('tetris');
            const context = canvas.getContext('2d');
            context.scale(20, 20);

            const arena = createMatrix(12, 20);
            const player = { pos: {x: 0, y: 0}, matrix: null, score: 0 };
            const pieces = 'ILJOTSZ';
            const colors = [null, '#9b5de5', '#f15bb5', '#fee440', '#00bbf9', '#00f5d4', '#ff1493', '#ff4500'];
            
            let dropCounter = 0;
            let dropInterval = 1000;
            let lastTime = 0;
            let animationId;

            function createMatrix(w, h) {
                const matrix = [];
                while (h--) matrix.push(new Array(w).fill(0));
                return matrix;
            }

            function createPiece(type) {
                if (type === 'T') return [[0,0,0],[1,1,1],[0,1,0]];
                if (type === 'O') return [[2,2],[2,2]];
                if (type === 'L') return [[0,3,0],[0,3,0],[0,3,3]];
                if (type === 'J') return [[0,4,0],[0,4,0],[4,4,0]];
                if (type === 'I') return [[0,5,0,0],[0,5,0,0],[0,5,0,0],[0,5,0,0]];
                if (type === 'S') return [[0,6,6],[6,6,0],[0,0,0]];
                if (type === 'Z') return [[7,7,0],[0,7,7],[0,0,0]];
            }

            function drawMatrix(matrix, offset) {
                matrix.forEach((row, y) => {
                    row.forEach((value, x) => {
                        if (value !== 0) {
                            context.fillStyle = colors[value];
                            context.fillRect(x + offset.x, y + offset.y, 1, 1);
                        }
                    });
                });
            }

            function draw() {
                context.fillStyle = '#000';
                context.fillRect(0, 0, canvas.width, canvas.height);
                drawMatrix(arena, {x: 0, y: 0});
                drawMatrix(player.matrix, player.pos);
            }

            function merge(arena, player) {
                player.matrix.forEach((row, y) => {
                    row.forEach((value, x) => {
                        if (value !== 0) arena[y + player.pos.y][x + player.pos.x] = value;
                    });
                });
            }

            function collide(arena, player) {
                const [m, o] = [player.matrix, player.pos];
                for (let y = 0; y < m.length; ++y) {
                    for (let x = 0; x < m[y].length; ++x) {
                        if (m[y][x] !== 0 && (arena[y + o.y] && arena[y + o.y][x + o.x]) !== 0) return true;
                    }
                }
                return false;
            }

            function arenaSweep() {
                let rowCount = 1;
                outer: for (let y = arena.length - 1; y > 0; --y) {
                    for (let x = 0; x < arena[y].length; ++x) {
                        if (arena[y][x] === 0) continue outer;
                    }
                    const row = arena.splice(y, 1)[0].fill(0);
                    arena.unshift(row);
                    ++y;
                    player.score += rowCount * 10;
                    rowCount *= 2;
                }
            }

            function playerDrop() {
                player.pos.y++;
                if (collide(arena, player)) {
                    player.pos.y--;
                    merge(arena, player);
                    playerReset();
                    arenaSweep();
                    updateScore();
                }
                dropCounter = 0;
            }

            function playerMove(dir) {
                player.pos.x += dir;
                if (collide(arena, player)) player.pos.x -= dir;
            }

            function playerReset() {
                player.matrix = createPiece(pieces[pieces.length * Math.random() | 0]);
                player.pos.y = 0;
                player.pos.x = (arena[0].length / 2 | 0) - (player.matrix[0].length / 2 | 0);
                if (collide(arena, player)) {
                    arena.forEach(row => row.fill(0));
                    player.score = 0;
                    updateScore();
                }
            }

            function playerRotate(dir) {
                const pos = player.pos.x;
                let offset = 1;
                const matrix = player.matrix;
                for (let y = 0; y < matrix.length; ++y) {
                    for (let x = 0; x < y; ++x) {
                        [matrix[x][y], matrix[y][x]] = [matrix[y][x], matrix[x][y]];
                    }
                }
                if (dir > 0) matrix.forEach(row => row.reverse());
                else matrix.reverse();
                
                while (collide(arena, player)) {
                    player.pos.x += offset;
                    offset = -(offset + (offset > 0 ? 1 : -1));
                    if (offset > player.matrix[0].length) {
                        if (dir > 0) matrix.reverse(); else matrix.forEach(row => row.reverse());
                        for (let y = 0; y < matrix.length; ++y) {
                            for (let x = 0; x < y; ++x) {
                                [matrix[x][y], matrix[y][x]] = [matrix[y][x], matrix[x][y]];
                            }
                        }
                        player.pos.x = pos;
                        return;
                    }
                }
            }

            function update(time = 0) {
                if (currentGame !== 'tetris') return; // Stop animation if left game
                const deltaTime = time - lastTime;
                lastTime = time;
                dropCounter += deltaTime;
                if (dropCounter > dropInterval) playerDrop();
                draw();
                animationId = requestAnimationFrame(update);
            }

            function updateScore() {
                document.getElementById('tetris-score').innerText = "Score: " + player.score;
                let hs = parseInt(localStorage.getItem('hs_tetris') || 0);
                if (player.score > hs) {
                    localStorage.setItem('hs_tetris', player.score);
                    document.getElementById('hs-tetris').innerText = player.score;
                }
            }

            document.addEventListener('keydown', event => {
                if (currentGame !== 'tetris') return;
                if (event.keyCode === 37) playerMove(-1);
                else if (event.keyCode === 39) playerMove(1);
                else if (event.keyCode === 40) playerDrop();
                else if (event.keyCode === 81) playerRotate(-1);
                else if (event.keyCode === 87) playerRotate(1);
            });

            playerReset();
            updateScore();
            update();
        }

        // --- COMPLETE SNAKE ENGINE ---
        function runSnake() {
            const canvas = document.getElementById('snake');
            const context = canvas.getContext('2d');
            const grid = 20;
            let count = 0;
            let score = 0;
            let animationId;

            let snake = { x: 160, y: 160, dx: grid, dy: 0, cells: [], maxCells: 4 };
            let food = { x: 320, y: 320 };

            function getRandomInt(min, max) { return Math.floor(Math.random() * (max - min)) + min; }
            function placeFood() { food.x = getRandomInt(0, 20) * grid; food.y = getRandomInt(0, 20) * grid; }

            function resetGame() {
                snake.x = 160; snake.y = 160; snake.cells = []; snake.maxCells = 4;
                snake.dx = grid; snake.dy = 0; score = 0;
                document.getElementById('snake-score').innerText = "Score: " + score;
                placeFood();
            }

            function loop() {
                if (currentGame !== 'snake') return; // Stop animation if left game
                animationId = requestAnimationFrame(loop);
                if (++count < 4) return;
                count = 0;
                context.clearRect(0, 0, canvas.width, canvas.height);
                
                snake.x += snake.dx;
                snake.y += snake.dy;
                if (snake.x < 0 || snake.x >= canvas.width || snake.y < 0 || snake.y >= canvas.height) resetGame();

                snake.cells.unshift({x: snake.x, y: snake.y});
                if (snake.cells.length > snake.maxCells) snake.cells.pop();

                context.fillStyle = '#ff1493'; // Food color
                context.fillRect(food.x, food.y, grid - 1, grid - 1);

                context.fillStyle = '#9bc400'; // Snake color
                snake.cells.forEach(function(cell, index) {
                    context.fillRect(cell.x, cell.y, grid - 1, grid - 1);
                    if (cell.x === food.x && cell.y === food.y) {
                        snake.maxCells++;
                        score += 10;
                        document.getElementById('snake-score').innerText = "Score: " + score;
                        let hs = parseInt(localStorage.getItem('hs_snake') || 0);
                        if (score > hs) {
                            localStorage.setItem('hs_snake', score);
                            document.getElementById('hs-snake').innerText = score;
                        }
                        placeFood();
                    }
                    for (let i = index + 1; i < snake.cells.length; i++) {
                        if (cell.x === snake.cells[i].x && cell.y === snake.cells[i].y) resetGame();
                    }
                });
            }

            document.addEventListener('keydown', function(e) {
                if (currentGame !== 'snake') return;
                if (e.which === 37 && snake.dx === 0) { snake.dx = -grid; snake.dy = 0; }
                else if (e.which === 38 && snake.dy === 0) { snake.dy = -grid; snake.dx = 0; }
                else if (e.which === 39 && snake.dx === 0) { snake.dx = grid; snake.dy = 0; }
                else if (e.which === 40 && snake.dy === 0) { snake.dy = grid; snake.dx = 0; }
            });

            resetGame();
            animationId = requestAnimationFrame(loop);
        }
    </script>
</body>
</html>