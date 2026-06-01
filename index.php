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
    <title>Game Dashboard</title>
    <style>
        body { font-family: 'Courier New', Courier, monospace; background: #111; color: #fff; text-align: center; margin: 0; padding: 20px; }
        .nav { display: flex; justify-content: space-between; max-width: 600px; margin: 0 auto 20px auto; align-items: center; }
        a { color: #ff6b6b; text-decoration: none; font-weight: bold; }

        /* --- NEW MENU LAYOUT --- */
        .main-menu-wrapper { display: flex; justify-content: center; align-items: center; gap: 50px; margin-top: 50px; max-width: 600px; margin-left: auto; margin-right: auto; }
        
        /* High Score Board Styling */
        .highscore-board { background: #222; padding: 20px; border-radius: 8px; border: 2px solid #444; text-align: left; min-width: 150px; box-shadow: 0 4px 10px rgba(0,0,0,0.5); }
        .highscore-board h2 { margin-top: 0; color: #ffcc00; border-bottom: 2px solid #444; padding-bottom: 10px; font-size: 20px; text-shadow: 1px 1px 0 #000; }
        .highscore-board p { font-size: 16px; margin: 15px 0 5px 0; color: #aaa; }
        .highscore-board span { font-weight: bold; font-size: 22px; color: #fff; float: right; margin-left: 20px; }

        .menu-buttons { display: flex; flex-direction: column; gap: 20px; }
        
        .btn-game { padding: 15px 30px; font-size: 18px; border: none; border-radius: 5px; cursor: pointer; font-weight: bold; transition: 0.2s; width: 100%; }
        
        .btn-tetris { background: #007bff; color: white; box-shadow: 0 4px 0 #0056b3; }
        .btn-tetris:hover { background: #0056b3; transform: translateY(2px); box-shadow: 0 2px 0 #0056b3; }

        .btn-snake { background: #9bc400; color: #111; box-shadow: 0 4px 0 #5a7300; }
        .btn-snake:hover { background: #aee600; transform: translateY(2px); box-shadow: 0 2px 0 #5a7300; }

        .btn-back { margin-bottom: 20px; padding: 10px 20px; background: #333; color: white; border: none; border-radius: 5px; cursor: pointer; display: none; }
        .btn-back:hover { background: #555; }

        #tetris-container, #snake-container { display: none; margin-top: 20px; }
        
        /* Canvas Styles */
        #tetris { background: #000; border: 4px solid #333; display: block; margin: 0 auto; }
        #snake { background: #9bc400; border: 10px solid #333; border-radius: 10px; display: block; margin: 0 auto; box-shadow: inset 0 0 10px rgba(0,0,0,0.2); }
        
        .score { font-size: 24px; margin-bottom: 15px; font-weight: bold; }
    </style>
</head>
<body>

    <div class="nav">
        <h3>Welcome, <?php echo htmlspecialchars($_SESSION['username']); ?>!</h3>
        <a href="logout.php">Logout</a>
    </div>

    <button class="btn-back" id="back-btn" onclick="location.reload()">Back to Menu</button>

    <div class="main-menu-wrapper" id="menu">
        
        <div class="highscore-board">
            <h2>HIGH SCORE</h2>
            <p>Snake: <span id="hs-snake-display">0</span></p>
            <p>Tetris: <span id="hs-tetris-display">0</span></p>
        </div>

        <div class="menu-buttons">
            <button class="btn-game btn-tetris" onclick="showTetris()">Play Tetris</button>
            <button class="btn-game btn-snake" onclick="showSnake()">Play Snake</button>
        </div>

    </div>

    <div id="tetris-container">
        <div class="score" id="tetris-score">Score: 0</div>
        <canvas id="tetris" width="240" height="400"></canvas>
    </div>

    <div id="snake-container">
        <div class="score" id="snake-score" style="color: #9bc400;">Score: 0</div>
        <canvas id="snake" width="400" height="400"></canvas>
    </div>

    <script>
        // --- LOAD HIGH SCORES FROM BROWSER STORAGE ---
        let highscoreSnake = localStorage.getItem('hs_snake') || 0;
        let highscoreTetris = localStorage.getItem('hs_tetris') || 0;

        document.getElementById('hs-snake-display').innerText = highscoreSnake;
        document.getElementById('hs-tetris-display').innerText = highscoreTetris;

        // --- UI NAVIGATION LOGIC ---
        function showTetris() {
            document.getElementById('menu').style.display = 'none';
            document.getElementById('back-btn').style.display = 'inline-block';
            document.getElementById('tetris-container').style.display = 'block';
            runTetris();
        }

        function showSnake() {
            document.getElementById('menu').style.display = 'none';
            document.getElementById('back-btn').style.display = 'inline-block';
            document.getElementById('snake-container').style.display = 'block';
            runSnake();
        }

        // ==========================================
        //             TETRIS ENGINE
        // ==========================================
        function runTetris() {
            const canvas = document.getElementById('tetris');
            const context = canvas.getContext('2d');
            context.scale(20, 20);

            const arena = createMatrix(12, 20);
            const player = { pos: {x: 0, y: 0}, matrix: null, score: 0 };
            const pieces = 'ILJOTSZ';
            
            function createMatrix(w, h) {
                const matrix = [];
                while (h--) { matrix.push(new Array(w).fill(0)); }
                return matrix;
            }

            function createPiece(type) {
                if (type === 'T') return [[0,0,0],[1,1,1],[0,1,0]];
                else if (type === 'O') return [[2,2],[2,2]];
                else if (type === 'L') return [[0,3,0],[0,3,0],[0,3,3]];
                else if (type === 'J') return [[0,4,0],[0,4,0],[4,4,0]];
                else if (type === 'I') return [[0,5,0,0],[0,5,0,0],[0,5,0,0],[0,5,0,0]];
                else if (type === 'S') return [[0,6,6],[6,6,0],[0,0,0]];
                else if (type === 'Z') return [[7,7,0],[0,7,7],[0,0,0]];
            }

            const colors = [null, '#9b5de5', '#f15bb5', '#fee440', '#00bbf9', '#00f5d4', '#ff1493', '#ff4500'];

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
                        if (value !== 0) {
                            arena[y + player.pos.y][x + player.pos.x] = value;
                        }
                    });
                });
            }

            function collide(arena, player) {
                const [m, o] = [player.matrix, player.pos];
                for (let y = 0; y < m.length; ++y) {
                    for (let x = 0; x < m[y].length; ++x) {
                        if (m[y][x] !== 0 && (arena[y + o.y] && arena[y + o.y][x + o.x]) !== 0) {
                            return true;
                        }
                    }
                }
                return false;
            }

            function arenaSweep() {
                let rowCount = 1;
                outer: for (let y = arena.length - 1; y > 0; --y) {
                    for (let x = 0; x < arena[y].length; ++x) {
                        if (arena[y][x] === 0) { continue outer; }
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
                if (collide(arena, player)) { player.pos.x -= dir; }
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

            function rotate(matrix, dir) {
                for (let y = 0; y < matrix.length; ++y) {
                    for (let x = 0; x < y; ++x) {
                        [matrix[x][y], matrix[y][x]] = [matrix[y][x], matrix[x][y]];
                    }
                }
                if (dir > 0) { matrix.forEach(row => row.reverse()); } 
                else { matrix.reverse(); }
            }

            function playerRotate(dir) {
                const pos = player.pos.x;
                let offset = 1;
                rotate(player.matrix, dir);
                while (collide(arena, player)) {
                    player.pos.x += offset;
                    offset = -(offset + (offset > 0 ? 1 : -1));
                    if (offset > player.matrix[0].length) {
                        rotate(player.matrix, -dir);
                        player.pos.x = pos;
                        return;
                    }
                }
            }

            let dropCounter = 0;
            let dropInterval = 1000;
            let lastTime = 0;

            function update(time = 0) {
                const deltaTime = time - lastTime;
                lastTime = time;
                dropCounter += deltaTime;
                if (dropCounter > dropInterval) { playerDrop(); }
                draw();
                requestAnimationFrame(update);
            }

            function updateScore() {
                document.getElementById('tetris-score').innerText = "Score: " + player.score;
                
                // SAVE HIGH SCORE
                if (player.score > highscoreTetris) {
                    highscoreTetris = player.score;
                    localStorage.setItem('hs_tetris', highscoreTetris);
                }
            }

            document.addEventListener('keydown', event => {
                if (document.getElementById('tetris-container').style.display === 'block') {
                    if (event.keyCode === 37) { playerMove(-1); }
                    else if (event.keyCode === 39) { playerMove(1); }
                    else if (event.keyCode === 40) { playerDrop(); }
                    else if (event.keyCode === 81) { playerRotate(-1); }
                    else if (event.keyCode === 87) { playerRotate(1); }
                }
            });

            playerReset();
            updateScore();
            update();
        }

        // ==========================================
        //             SNAKE ENGINE
        // ==========================================
        function runSnake() {
            const canvas = document.getElementById('snake');
            const context = canvas.getContext('2d');
            
            const grid = 20;
            let count = 0;
            let score = 0;

            let snake = { x: 160, y: 160, dx: grid, dy: 0, cells: [], maxCells: 4 };
            let food = { x: 320, y: 320 };

            function getRandomInt(min, max) {
                return Math.floor(Math.random() * (max - min)) + min;
            }

            function resetGame() {
                snake.x = 160; snake.y = 160; snake.cells = []; snake.maxCells = 4;
                snake.dx = grid; snake.dy = 0; score = 0;
                document.getElementById('snake-score').innerText = "Score: " + score;
                placeFood();
            }

            function placeFood() {
                food.x = getRandomInt(0, 20) * grid;
                food.y = getRandomInt(0, 20) * grid;
            }

            function loop() {
                requestAnimationFrame(loop);

                if (++count < 4) return;
                count = 0;

                context.clearRect(0, 0, canvas.width, canvas.height);
                snake.x += snake.dx;
                snake.y += snake.dy;

                if (snake.x < 0 || snake.x >= canvas.width || snake.y < 0 || snake.y >= canvas.height) { resetGame(); }

                snake.cells.unshift({x: snake.x, y: snake.y});
                if (snake.cells.length > snake.maxCells) { snake.cells.pop(); }

                context.fillStyle = '#43523d';
                context.fillRect(food.x, food.y, grid - 1, grid - 1);

                context.fillStyle = '#43523d';
                snake.cells.forEach(function(cell, index) {
                    context.fillRect(cell.x, cell.y, grid - 1, grid - 1);

                    if (cell.x === food.x && cell.y === food.y) {
                        snake.maxCells++;
                        score += 10;
                        document.getElementById('snake-score').innerText = "Score: " + score;
                        
                        // SAVE HIGH SCORE
                        if (score > highscoreSnake) {
                            highscoreSnake = score;
                            localStorage.setItem('hs_snake', highscoreSnake);
                        }
                        
                        placeFood();
                    }

                    for (let i = index + 1; i < snake.cells.length; i++) {
                        if (cell.x === snake.cells[i].x && cell.y === snake.cells[i].y) { resetGame(); }
                    }
                });
            }

            document.addEventListener('keydown', function(e) {
                if (document.getElementById('snake-container').style.display === 'block') {
                    if (e.which === 37 && snake.dx === 0) { snake.dx = -grid; snake.dy = 0; }
                    else if (e.which === 38 && snake.dy === 0) { snake.dy = -grid; snake.dx = 0; }
                    else if (e.which === 39 && snake.dx === 0) { snake.dx = grid; snake.dy = 0; }
                    else if (e.which === 40 && snake.dy === 0) { snake.dy = grid; snake.dx = 0; }
                }
            });

            requestAnimationFrame(loop);
        }
    </script>
</body>
</html>