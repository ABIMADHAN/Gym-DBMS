<?php
require_once("../includes/header.php");
if (!isCustomer()) exit();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Workout Plan</title>
    <style>
        body {
            font-family: 'Segoe UI', sans-serif;
            background: linear-gradient(135deg, #4dd0e1 0%, #1976d2 100%);
            padding: 40px;
            min-height: 100vh;
            margin: 0;
        }
        .container {
            max-width: 600px;
            margin: 60px auto;
            background: #ffffffcc;
            padding: 40px 30px 30px 30px;
            border-radius: 20px;
            box-shadow: 0 8px 32px 0 rgba(31, 38, 135, 0.37);
            position: relative;
            overflow: hidden;
            animation: popIn 1s cubic-bezier(.68,-0.55,.27,1.55);
        }
        @keyframes popIn {
            0% { transform: scale(0.7) rotate(-10deg); opacity: 0; }
            80% { transform: scale(1.05) rotate(2deg); opacity: 1; }
            100% { transform: scale(1) rotate(0); }
        }
        h2 {
            color: #1976d2;
            margin-bottom: 28px;
            text-align: center;
            font-size: 2.2em;
            letter-spacing: 1px;
            position: relative;
            z-index: 2;
        }
        ul {
            list-style: none;
            padding: 0;
            margin: 0;
        }
        li {
            display: flex;
            align-items: center;
            padding: 18px 0;
            border-bottom: 1px solid #e0e0e0;
            font-size: 1.18em;
            position: relative;
            opacity: 0;
            transform: translateX(-40px) scale(0.95);
            animation: slideIn 0.7s cubic-bezier(.68,-0.55,.27,1.55) forwards;
        }
        li:last-child {
            border-bottom: none;
        }
        .icon {
            font-size: 2em;
            margin-right: 18px;
            filter: drop-shadow(0 2px 4px #1976d2aa);
            animation: crazySpin 2.5s infinite linear;
        }
        @keyframes slideIn {
            to {
                opacity: 1;
                transform: translateX(0) scale(1);
            }
        }
        /* Staggered animation for each li */
        li:nth-child(1) { animation-delay: 0.1s; }
        li:nth-child(2) { animation-delay: 0.25s; }
        li:nth-child(3) { animation-delay: 0.4s; }
        li:nth-child(4) { animation-delay: 0.55s; }
        li:nth-child(5) { animation-delay: 0.7s; }
        li:nth-child(6) { animation-delay: 0.85s; }
        li:nth-child(7) { animation-delay: 1s; }
        /* Crazy icon animation */
        @keyframes crazySpin {
            0% { transform: rotate(0deg) scale(1);}
            20% { transform: rotate(20deg) scale(1.1);}
            40% { transform: rotate(-20deg) scale(1.2);}
            60% { transform: rotate(10deg) scale(1.1);}
            80% { transform: rotate(-10deg) scale(1);}
            100% { transform: rotate(0deg) scale(1);}
        }
        /* Fun confetti effect */
        .confetti {
            position: absolute;
            width: 12px;
            height: 12px;
            border-radius: 50%;
            opacity: 0.7;
            pointer-events: none;
            z-index: 1;
        }
        .desc {
            display: none;
            background: #e3f2fd;
            color: #1976d2;
            margin-top: 10px;
            padding: 14px 18px;
            border-radius: 10px;
            font-size: 1em;
            box-shadow: 0 2px 8px #1976d233;
            animation: fadeInDesc 0.5s;
        }
        li.active .desc {
            display: block;
        }
        @keyframes fadeInDesc {
            from { opacity: 0; transform: translateY(-10px);}
            to { opacity: 1; transform: translateY(0);}
        }
    </style>
</head>
<body>
<div class="container">
    <h2>🎯 Your Weekly Workout Plan</h2>
    <ul>
        <li onclick="showDesc(0)">
            <span class="icon">🏃‍♂️</span><strong>Monday:</strong> Cardio + Abs
            <div class="desc">Start your week with 30 minutes of running or cycling, followed by 15 minutes of core exercises like planks, crunches, and leg raises.</div>
        </li>
        <li onclick="showDesc(1)">
            <span class="icon">💪</span><strong>Tuesday:</strong> Upper Body Strength
            <div class="desc">Focus on push-ups, pull-ups, dumbbell presses, and rows. Aim for 3 sets of 10-12 reps for each exercise.</div>
        </li>
        <li onclick="showDesc(2)">
            <span class="icon">🧘‍♀️</span><strong>Wednesday:</strong> Yoga & Flexibility
            <div class="desc">Enjoy a yoga session with stretches for your back, hamstrings, and shoulders. Try to hold each stretch for at least 30 seconds.</div>
        </li>
        <li onclick="showDesc(3)">
            <span class="icon">🦵</span><strong>Thursday:</strong> Lower Body Strength
            <div class="desc">Squats, lunges, calf raises, and glute bridges. 3 sets of 12 reps each. Don’t forget to stretch after!</div>
        </li>
        <li onclick="showDesc(4)">
            <span class="icon">🔥</span><strong>Friday:</strong> HIIT
            <div class="desc">High-Intensity Interval Training: 20 seconds of burpees, mountain climbers, and jumping jacks, with 10 seconds rest, repeat for 15-20 minutes.</div>
        </li>
        <li onclick="showDesc(5)">
            <span class="icon">🤸‍♂️</span><strong>Saturday:</strong> Full Body Circuit
            <div class="desc">Combine upper, lower, and core moves in a circuit: push-ups, squats, planks, and jumping rope. 3 rounds, minimal rest.</div>
        </li>
        <li onclick="showDesc(6)">
            <span class="icon">😴</span><strong>Sunday:</strong> Rest
            <div class="desc">Take a break! Go for a walk, do some light stretching, and let your body recover for the week ahead.</div>
        </li>
    </ul>
</div>
<script>
    function randomColor() {
        const colors = ['#4dd0e1', '#1976d2', '#ffeb3b', '#ff4081', '#69f0ae', '#ffd740', '#ff5252'];
        return colors[Math.floor(Math.random() * colors.length)];
    }
    function createConfettiPiece(container) {
        const confetti = document.createElement('div');
        confetti.className = 'confetti';
        confetti.style.background = randomColor();
        confetti.style.left = Math.random() * 100 + '%';
        confetti.style.top = '-20px';
        confetti.style.opacity = Math.random() * 0.5 + 0.5;
        confetti.style.width = confetti.style.height = (Math.random() * 8 + 8) + 'px';
        confetti.style.transition = 'top 2.5s linear, left 2.5s linear, opacity 2.5s linear';
        container.appendChild(confetti);
        setTimeout(() => {
            confetti.style.top = (Math.random() * 80 + 20) + '%';
            confetti.style.left = (Math.random() * 100) + '%';
            confetti.style.opacity = 0;
        }, 10);
        setTimeout(() => {
            confetti.remove();
        }, 2600);
    }
    const container = document.querySelector('.container');
    for (let i = 0; i < 30; i++) setTimeout(() => createConfettiPiece(container), i * 60);
    container.addEventListener('click', () => {
        for (let i = 0; i < 20; i++) setTimeout(() => createConfettiPiece(container), i * 30);
    });
    function showDesc(idx) {
        const lis = document.querySelectorAll('.container ul li');
        lis.forEach((li, i) => {
            if (i === idx) {
                li.classList.toggle('active');
            } else {
                li.classList.remove('active');
            }
        });
    }
</script>
</body>
</html>
