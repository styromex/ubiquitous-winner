<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=yes">
    <title>NBS · neon white · retro tools</title>
    <!-- Tailwind & lucide -->
    <script src="https://cdn.tailwindcss.com/3.4.1"></script>
    <script src="https://unpkg.com/lucide@latest"></script>
    <link href="https://fonts.googleapis.com/css2?family=JetBrains+Mono:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --bg: #000000;
            --text: #ffffff;
            --dim: #aaaaaa;
            --primary: #ffffff;        /* pure white */
            --secondary: #f0f0f0;       /* soft white */
            --tertiary: #e0e0e0;         /* light gray */
            --accent: #ffffff;           /* white accent */
            --glow-primary: 0 0 15px rgba(255,255,255,0.8);
            --glow-secondary: 0 0 15px rgba(240,240,240,0.7);
            --glow-tertiary: 0 0 15px rgba(224,224,224,0.6);
            --glow-accent: 0 0 15px rgba(255,255,255,0.9);
            --glow-white: 0 0 20px rgba(255,255,255,0.8), 0 0 40px rgba(255,255,255,0.4);
        }

        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            font-family: 'JetBrains Mono', monospace;
            background: var(--bg);
            color: var(--text);
            line-height: 1.5;
            min-height: 100vh;
            font-size: 14px;
            overflow-x: hidden;
        }

        /* ===== DIAGONAL WIPE ENTRY ===== */
        #entry-screen {
            position: fixed;
            inset: 0;
            background: #000000;
            z-index: 9999;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            clip-path: polygon(0% 0%, 100% 0%, 100% 100%, 0% 100%);
        }

        @keyframes diagonalPullBack {
            0% { clip-path: polygon(0% 0%, 100% 0%, 100% 100%, 0% 100%); }
            100% { clip-path: polygon(100% 0%, 100% 0%, 0% 100%, 0% 100%); }
        }

        .diagonal-wipe {
            animation: diagonalPullBack 1.2s cubic-bezier(0.7, 0, 0.3, 1) forwards;
        }

        .glowing-computer {
            color: var(--primary);
            width: 80px;
            height: 80px;
            filter: drop-shadow(0 0 12px var(--primary));
            margin-bottom: 1.5rem;
            animation: pulse 2s infinite ease-in-out;
        }

        .entry-text {
            color: var(--primary);
            text-transform: uppercase;
            letter-spacing: 4px;
            font-weight: 700;
            filter: drop-shadow(0 0 12px var(--primary));
            font-size: 1.4rem;
            text-align: center;
            text-shadow: 0 0 20px var(--primary), 0 0 40px var(--primary);
        }

        @keyframes pulse {
            0%, 100% { filter: drop-shadow(0 0 8px var(--primary)) drop-shadow(0 0 15px var(--primary)); }
            50% { filter: drop-shadow(0 0 25px var(--primary)) drop-shadow(0 0 40px var(--primary)); }
        }

        /* VIDEO BACKGROUND (darker, no hue) */
        #bg-video {
            position: fixed;
            top: 0; left: 0;
            width: 100%; height: 100%;
            object-fit: cover;
            z-index: -2;
            filter: brightness(0.25) contrast(1.2);
        }

        .overlay {
            position: fixed;
            top: 0; left: 0;
            width: 100%; height: 100%;
            background: radial-gradient(circle at 50% 50%, rgba(0,0,0,0.2) 0%, rgba(0,0,0,0.9) 100%);
            z-index: -1;
        }

        /* ===== MOBILE-FIRST HEADER ===== */
        .top-bar {
            background: rgba(0, 0, 0, 0.8);
            backdrop-filter: blur(10px);
            border-bottom: 1px solid rgba(255,255,255,0.3);
            padding: 0.75rem 1rem;
            display: flex;
            align-items: center;
            justify-content: space-between;
            position: sticky;
            top: 0;
            z-index: 50;
            box-shadow: 0 0 20px rgba(255,255,255,0.2);
            flex-wrap: wrap;
            gap: 0.5rem;
        }

        .nav-tabs {
            display: flex;
            gap: 1rem;
            flex-wrap: wrap;
        }

        .nav-tab {
            color: var(--dim);
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 1px;
            cursor: pointer;
            border-bottom: 1px solid transparent;
            padding-bottom: 4px;
            transition: all 0.2s;
            font-size: 0.85rem;
            white-space: nowrap;
        }

        .nav-tab.active {
            color: #fff;
            border-bottom: 1px solid var(--primary);
            text-shadow: 0 0 8px var(--primary);
        }

        .logo-area {
            display: flex;
            align-items: center;
            gap: 0.25rem;
            background: rgba(255,255,255,0.05);
            padding: 0.25rem 0.75rem;
            border-radius: 30px;
            border: 1px solid var(--primary);
            box-shadow: 0 0 10px var(--primary);
        }

        .logo-icon {
            width: 24px;
            height: 24px;
            color: var(--primary);
        }

        .logo-text {
            font-size: 1.2rem;
            font-weight: 700;
            letter-spacing: 1px;
            background: linear-gradient(135deg, #fff, #ccc);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        /* MAIN CONTENT */
        main {
            padding: 1.5rem 1rem;
            max-width: 1400px;
            margin: 0 auto;
        }

        /* Tool selector inside GENERATORS */
        .tool-selector {
            display: flex;
            gap: 0.5rem;
            margin-bottom: 1.5rem;
            border-bottom: 1px solid #2a2a2a;
            padding-bottom: 0.5rem;
            flex-wrap: wrap;
        }
        .tool-btn {
            background: transparent;
            border: none;
            color: #aaa;
            font-family: inherit;
            font-size: 0.9rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 1px;
            padding: 0.5rem 1rem;
            cursor: pointer;
            transition: all 0.2s;
            border-radius: 4px;
            white-space: nowrap;
        }
        .tool-btn.active {
            color: #fff;
            background: rgba(255,255,255,0.15);
            box-shadow: 0 0 15px var(--primary);
        }

        /* Stats blocks */
        .gen-stats {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 0.75rem;
            margin-bottom: 1.5rem;
        }
        .stat-block {
            background: rgba(0,0,0,0.5);
            border: 1px solid rgba(255,255,255,0.1);
            padding: 1rem 0.5rem;
            text-align: center;
            backdrop-filter: blur(4px);
            border-radius: 8px;
            transition: 0.2s;
        }
        .stat-block:nth-child(1) { border-color: var(--primary); box-shadow: var(--glow-primary); }
        .stat-block:nth-child(2) { border-color: var(--secondary); box-shadow: var(--glow-secondary); }
        .stat-block:nth-child(3) { border-color: var(--tertiary); box-shadow: var(--glow-tertiary); }

        .stat-number { font-size: 1.8rem; font-weight: 700; color: white; }
        .stat-label { font-size: 0.65rem; text-transform: uppercase; letter-spacing: 1px; opacity: 0.7; color: #ccc; }

        .gradient-panel {
            background: rgba(0, 0, 0, 0.5);
            backdrop-filter: blur(8px);
            border: 2px solid var(--primary);
            border-radius: 12px;
            padding: 1.5rem;
            box-shadow: var(--glow-primary);
        }

        .input-dox {
            background: rgba(0, 0, 0, 0.7);
            border: 1px solid #333;
            padding: 0.8rem 1rem;
            width: 100%;
            color: #fff;
            font-family: inherit;
            outline: none;
            font-size: 0.95rem;
            transition: 0.2s;
            border-radius: 6px;
        }
        .input-dox:focus {
            border-color: var(--primary);
            box-shadow: var(--glow-primary);
        }

        .btn-dox {
            background: linear-gradient(45deg, #fff, #aaa);
            color: black;
            font-weight: 700;
            padding: 0.8rem 1.2rem;
            border: none;
            cursor: pointer;
            text-transform: uppercase;
            letter-spacing: 1px;
            width: 100%;
            font-size: 0.95rem;
            transition: 0.2s;
            border-radius: 6px;
        }
        .btn-dox:hover {
            background: linear-gradient(45deg, #eee, #888);
            box-shadow: var(--glow-primary);
        }

        .suggestion-box {
            background: rgba(0,0,0,0.5);
            border: 1px solid var(--primary);
            border-radius: 12px;
            padding: 1.2rem;
            margin-top: 1.5rem;
            box-shadow: var(--glow-primary);
        }
        .suggestion-box textarea {
            background: rgba(0,0,0,0.7);
            border: 1px solid var(--primary);
            width: 100%;
            padding: 0.7rem;
            color: white;
            font-family: inherit;
            resize: vertical;
            border-radius: 6px;
            font-size: 0.9rem;
        }
        .suggestion-box button {
            background: var(--primary);
            color: black;
            font-weight: bold;
            padding: 0.7rem 1.2rem;
            border: none;
            cursor: pointer;
            text-transform: uppercase;
            letter-spacing: 1px;
            width: 100%;
            transition: 0.2s;
            border-radius: 6px;
        }
        .suggestion-box button:hover {
            background: #ccc;
            box-shadow: var(--glow-primary);
        }

        /* ===== METHODS TAB – COOLER WITH WHITE GLOW ===== */
        .methods-grid {
            display: grid;
            grid-template-columns: 1fr;
            gap: 1rem;
            margin-top: 1rem;
        }
        @media (min-width: 640px) {
            .methods-grid {
                grid-template-columns: repeat(2, 1fr);
            }
        }

        .method-card {
            background: rgba(10, 10, 15, 0.8);
            backdrop-filter: blur(6px);
            border: 1px solid rgba(255,255,255,0.1);
            border-radius: 16px;
            padding: 1.5rem;
            transition: all 0.3s ease;
            display: flex;
            flex-direction: column;
            gap: 0.5rem;
            box-shadow: 0 4px 20px rgba(0,0,0,0.5);
        }

        .method-card:hover {
            border-color: rgba(255,255,255,0.5);
            box-shadow: var(--glow-white);
            transform: translateY(-4px);
            background: rgba(20, 20, 30, 0.9);
        }

        .method-icon {
            width: 32px;
            height: 32px;
            color: white;
            margin-bottom: 0.5rem;
            filter: drop-shadow(0 0 8px rgba(255,255,255,0.5));
        }

        .method-title {
            font-size: 1.2rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 1px;
            background: linear-gradient(135deg, #fff, #ccc);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .method-desc {
            color: #ccc;
            font-size: 0.9rem;
            line-height: 1.5;
        }

        /* Hidden utility */
        .hidden { display: none !important; }

        /* sparkle trace (white/light colors) */
        .sparkle {
            pointer-events: none;
            position: fixed;
            font-family: monospace;
            font-weight: bold;
            font-size: 8px;
            animation: sparkle-fade 0.8s forwards;
            z-index: 1000;
            text-shadow: 0 0 3px currentColor;
        }
        @keyframes sparkle-fade {
            0% { opacity: 1; transform: scale(1) translateY(0); }
            50% { opacity: 0.8; transform: scale(0.9) translateY(-10px); }
            100% { opacity: 0; transform: scale(0.6) translateY(-20px); }
        }

        /* snow */
        .snow-container {
            position: fixed;
            top: 0; left: 0;
            width: 100%; height: 100%;
            pointer-events: none;
            z-index: 5;
            overflow: hidden;
        }
        .snow {
            position: absolute;
            top: -10px;
            border-radius: 50%;
            background: white;
            opacity: 0.8;
            filter: blur(1px);
            animation: fall linear infinite;
        }
        @keyframes fall {
            to { transform: translateY(100vh); }
        }

        /* HALL OF POORONS – updated with white borders */
        .hoa-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 0.75rem;
        }
        .hoa-card {
            background: #0E0E0E;
            border: 1px solid var(--primary);
            border-radius: 8px;
            overflow: hidden;
            transition: 0.2s;
        }
        .hoa-card:hover {
            border-color: var(--primary);
            box-shadow: var(--glow-primary);
        }
        .hoa-image {
            width: 100%;
            height: 200px;
            overflow: hidden;
            border-bottom: 1px solid #2a2a2a;
            background: #0a0a0a;
            display: flex;
            justify-content: center;
            align-items: center;
        }
        .hoa-image img {
            max-width: 100%;
            max-height: 100%;
            object-fit: contain;
        }
        .hoa-content {
            padding: 0.5rem;
            text-align: center;
        }
        .hoa-name {
            font-size: 1.2rem;
            font-weight: 600;
            margin-bottom: 0.5rem;
            animation: white-shift 5s infinite; /* subtle white variation */
        }
        .hoa-description {
            color: #b0b0b0;
            font-size: 0.85rem;
            max-height: calc(1.5rem * 5);
            overflow-y: auto;
        }
        @keyframes white-shift {
            0% { color: #ffffff; text-shadow: 0 0 5px #fff; }
            50% { color: #dddddd; text-shadow: 0 0 10px #fff; }
            100% { color: #ffffff; text-shadow: 0 0 5px #fff; }
        }

        /* game grid */
        .game-grid {
            display: grid;
            grid-template-columns: repeat(1, 1fr);
            gap: 1rem;
            margin-top: 1rem;
        }
        @media (min-width: 640px) { .game-grid { grid-template-columns: repeat(2, 1fr); } }
        @media (min-width: 1024px) { .game-grid { grid-template-columns: repeat(3, 1fr); } }

        .game-card {
            background: rgba(15, 15, 20, 0.7);
            backdrop-filter: blur(4px);
            border: 1px solid rgba(255,255,255,0.2);
            border-radius: 12px;
            padding: 1rem;
            transition: all 0.2s;
        }
        .game-card:hover {
            border-color: var(--primary);
            box-shadow: var(--glow-primary);
            transform: translateY(-2px);
        }
        .game-card img {
            width: 100%;
            height: 120px;
            object-fit: cover;
            border-radius: 8px;
            margin-bottom: 0.75rem;
        }
        .game-card h3 {
            font-size: 1rem;
            font-weight: 600;
            margin-bottom: 0.5rem;
        }
        .game-stats {
            display: flex;
            justify-content: space-between;
            background: rgba(0,0,0,0.3);
            border-radius: 2rem;
            padding: 0.4rem 0.6rem;
            margin-bottom: 0.75rem;
            font-size: 0.7rem;
        }
        .game-stats span {
            display: block;
            font-weight: 700;
            color: white;
            font-size: 0.9rem;
        }
        .copy-btn {
            background: rgba(255,255,255,0.2);
            border: 1px solid rgba(255,255,255,0.3);
            color: white;
            padding: 0.6rem;
            border-radius: 2rem;
            font-size: 0.8rem;
            font-weight: 600;
            cursor: pointer;
            transition: 0.2s;
            text-transform: uppercase;
        }
        .copy-btn:hover {
            background: var(--primary);
            color: black;
            border-color: var(--primary);
            box-shadow: var(--glow-primary);
        }

        .loading { color: var(--primary); text-align: center; padding: 2rem; grid-column: 1 / -1; }
        .preview-iframe {
            width: 100%;
            height: 500px;
            background: #0a0a0a;
            border: 2px solid var(--primary);
            border-radius: 12px;
            box-shadow: var(--glow-primary);
        }

        @media (max-width: 480px) {
            .top-bar { padding: 0.5rem; }
            .nav-tabs { gap: 0.5rem; }
            .nav-tab { font-size: 0.75rem; }
            .logo-text { font-size: 1rem; }
            .logo-icon { width: 20px; height: 20px; }
            .tool-btn { font-size: 0.8rem; padding: 0.4rem 0.8rem; }
        }
    </style>
</head>
<body>

<div id="entry-screen">
    <i data-lucide="monitor" class="glowing-computer"></i>
    <p class="entry-text">Click to Initialize</p>
</div>

<video id="bg-video" loop playsinline>
    <source src="video.mp4" type="video/mp4">
</video>
<div class="overlay"></div>

<div class="snow-container" id="snow-container"></div>

<div id="dashboard" class="min-h-screen flex flex-col">
    <div class="top-bar">
        <div class="nav-tabs">
            <div class="nav-tab active" data-tab="generators">GENERATORS</div>
            <div class="nav-tab" data-tab="methods">METHODS</div>
            <div class="nav-tab" data-tab="poorons">HALL OF POORONS</div>
        </div>
        <div class="logo-area">
            <i data-lucide="cpu" class="logo-icon"></i>
            <span class="logo-text">NBS</span>
        </div>
    </div>

    <main>
        <!-- GENERATORS TAB (merged) -->
        <div id="tab-generators" class="tab-content">
            <div class="tool-selector">
                <button class="tool-btn active" data-tool="generator">Generator</button>
                <button class="tool-btn" data-tool="gamecloner">Game Cloner</button>
                <button class="tool-btn" data-tool="profilegen">Profile Gen</button>
            </div>

            <!-- Generator panel -->
            <div id="tool-generator" class="tool-panel">
                <h1 class="text-lg font-bold mb-3 flex items-center gap-2"><i data-lucide="zap" class="w-4 h-4" style="color:white;"></i>Generator Node</h1>
                <div class="gen-stats">
                    <div class="stat-block"><div class="stat-number">247</div><div class="stat-label">PAGES GEN</div></div>
                    <div class="stat-block"><div class="stat-number">38</div><div class="stat-label">WEBHOOKS</div></div>
                    <div class="stat-block"><div class="stat-number">12</div><div class="stat-label">QUEUE</div></div>
                </div>
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">
                    <div class="lg:col-span-2">
                        <div class="gradient-panel">
                            <form id="genForm" class="space-y-4">
                                <div><label class="text-gray-400 text-xs tracking-widest uppercase block mb-1">Project Name</label><input type="text" id="project_name" class="input-dox" placeholder="e.g. Neon Landing" required></div>
                                <div><label class="text-gray-400 text-xs tracking-widest uppercase block mb-1">Theme</label><input type="text" id="project_theme" class="input-dox" placeholder="e.g. black-white" required></div>
                                <button type="submit" id="genBtn" class="btn-dox mt-3">GENERATE CONFIG</button>
                            </form>
                            <div id="generatorOutput" class="hidden mt-3 text-xs text-green-400"></div>
                        </div>
                    </div>
                    <div class="lg:col-span-1 space-y-4">
                        <div class="bg-black/60 border p-3 rounded-lg" style="border-color:white; box-shadow:var(--glow-secondary);">
                            <h2 class="text-xs font-bold mb-2 tracking-widest text-white">RECENT OUTPUTS</h2>
                            <ul class="space-y-1 text-xs text-gray-400"><li class="border-b border-white/10 pb-1">• Lumber Tycoon 2 → lt2_4f3a</li><li class="border-b border-white/10 pb-1">• Arsenal → ars_8d2k</li><li class="border-b border-white/10 pb-1">• Driving Empire → drv_9f1b</li><li class="border-b border-white/10 pb-1">• Sushi Shop → ssh_0a3c</li></ul>
                        </div>
                        <div class="suggestion-box">
                            <h2 class="text-xs font-bold mb-2 tracking-widest text-white">REQUEST FEATURE</h2>
                            <form id="suggestionForm"><textarea rows="2" placeholder="Type your suggestion..." class="mb-2 text-sm"></textarea><button type="submit" class="text-sm">SUBMIT</button></form>
                            <p id="suggestionMsg" class="text-xs text-green-400 mt-1 hidden">Thanks, we'll consider it.</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Game Cloner panel -->
            <div id="tool-gamecloner" class="tool-panel hidden">
                <h1 class="text-lg font-bold mb-3 flex items-center gap-2"><i data-lucide="gamepad-2" class="w-4 h-4" style="color:white;"></i>Private Server Link Search</h1>
                <div class="relative mb-6 max-w-xl"><i data-lucide="search" class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-500"></i><input type="text" id="gameSearch" class="w-full bg-zinc-900/80 border border-zinc-700 rounded-xl py-3 pl-10 pr-3 text-white placeholder-gray-500 focus:outline-none focus:border-white focus:ring-2 focus:ring-white/20 transition text-sm" placeholder="Search games... (e.g. Adopt Me)"></div>
                <div id="gameGrid" class="game-grid"><div class="loading">Enter a search term to load games...</div></div>
            </div>

            <!-- Profile Gen panel -->
            <div id="tool-profilegen" class="tool-panel hidden">
                <h1 class="text-lg font-bold mb-3 flex items-center gap-2"><i data-lucide="user-circle" class="w-4 h-4" style="color:white;"></i>Roblox Profile Generator</h1>
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
                    <div class="gradient-panel">
                        <h2 class="text-md mb-3 text-white">Customize Profile</h2>
                        <form id="profileGenForm" class="space-y-3">
                            <div><label class="text-gray-400 text-xs block mb-1">Username</label><input type="text" id="profileUser" class="input-dox text-sm" value="CoolGamer123" placeholder="Enter username"></div>
                            <div><label class="text-gray-400 text-xs block mb-1">Robux</label><input type="number" id="profileRobux" class="input-dox text-sm" value="999999" min="0"></div>
                            <div><label class="text-gray-400 text-xs block mb-1">Followers</label><input type="number" id="profileFollowers" class="input-dox text-sm" value="50000" min="0"></div>
                            <div><label class="text-gray-400 text-xs block mb-1">Following</label><input type="number" id="profileFollowing" class="input-dox text-sm" value="1234" min="0"></div>
                            <div><label class="text-gray-400 text-xs block mb-1">Badges</label><div class="flex flex-wrap gap-2 text-xs"><label class="flex items-center gap-1"><input type="checkbox" id="badgeAdmin" checked> ADMIN</label><label class="flex items-center gap-1"><input type="checkbox" id="badgeVerified"> VERIFIED</label><label class="flex items-center gap-1"><input type="checkbox" id="badgePremium"> PREMIUM</label></div></div>
                            <button type="submit" id="generateProfileBtn" class="btn-dox text-sm">GENERATE</button>
                        </form>
                    </div>
                    <div class="bg-black/60 border p-3 rounded-lg flex flex-col" style="border-color:white;">
                        <h2 class="text-md mb-3 flex items-center gap-2 text-white"><i data-lucide="eye" class="w-4 h-4"></i> Preview</h2>
                        <iframe id="profilePreviewIframe" class="preview-iframe" sandbox="allow-same-origin allow-scripts allow-popups allow-forms" title="Profile Preview"></iframe>
                        <div id="profileGenOutput" class="hidden mt-2 text-center text-green-400 text-xs">✅ Profile generated!</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- METHODS TAB – COOLER, WHITE GLOW, EXPANDED -->
        <div id="tab-methods" class="tab-content hidden">
            <h1 class="text-lg font-bold mb-4 flex items-center gap-2"><i data-lucide="skull" class="w-5 h-5" style="color:white; filter:drop-shadow(0 0 8px white);"></i>METHODS ARSENAL</h1>
            <div class="methods-grid">
                <div class="method-card">
                    <i data-lucide="ghost" class="method-icon"></i>
                    <div class="method-title">PHANTOM RIP</div>
                    <div class="method-desc">Exploit game memory via advanced JS injection – success rate 87%. Bypasses most anti‑cheat.</div>
                </div>
                <div class="method-card">
                    <i data-lucide="droplets" class="method-icon"></i>
                    <div class="method-title">WEBHOOK FLOOD</div>
                    <div class="method-desc">Mass webhook trigger with rate‑limit bypass & rotating proxies. Flood Discord with custom payloads.</div>
                </div>
                <div class="method-card">
                    <i data-lucide="scan" class="method-icon"></i>
                    <div class="method-title">ID SCRAPER</div>
                    <div class="method-desc">Fetch valid place IDs from Roblox widgets & game pages. Bulk export with metadata.</div>
                </div>
                <div class="method-card">
                    <i data-lucide="users" class="method-icon"></i>
                    <div class="method-title">DOX POORON</div>
                    <div class="method-desc">Gather public info from the pooron database – usernames, emails, pastes. 2025 leak integrated.</div>
                </div>
                <div class="method-card">
                    <i data-lucide="zap" class="method-icon"></i>
                    <div class="method-title">WEBHOOK FLOODER PRO</div>
                    <div class="method-desc">Next‑gen webhook spammer with multi‑threading, captcha harvester, and auto‑rotate user agents.</div>
                </div>
                <div class="method-card">
                    <i data-lucide="video" class="method-icon"></i>
                    <div class="method-title">FAKE TIKTOK LIVE</div>
                    <div class="method-desc">Download any TikTok live stream as fake clip + metadata injector. Spoof viewer counts.</div>
                </div>
                <div class="method-card">
                    <i data-lucide="sword" class="method-icon"></i>
                    <div class="method-title">IN‑GAME METHODS</div>
                    <div class="method-desc">Collection of Roblox game exploits: auto‑farm, teleport, infinite yield, and remote spy.</div>
                </div>
                <div class="method-card">
                    <i data-lucide="youtube" class="method-icon"></i>
                    <div class="method-title">YOUTUBE AUTOHAR</div>
                    <div class="method-desc">Automated subscriber harvest / view bot for YouTube Shorts. Uses residential proxies.</div>
                </div>
                <div class="method-card">
                    <i data-lucide="shield" class="method-icon"></i>
                    <div class="method-title">ROVERIFIER METHOD</div>
                    <div class="method-desc">Bypass Roblox verification (roverifier) with token extraction & email simulation.</div>
                </div>
            </div>
        </div>

        <!-- HALL OF POORONS TAB -->
        <div id="tab-poorons" class="tab-content hidden">
            <h1 class="text-lg font-bold mb-3 flex items-center gap-2"><i data-lucide="users" class="w-4 h-4" style="color:white;"></i>Hall of Poorons</h1>
            <div class="hoa-grid">
                <div class="hoa-card"><div class="hoa-image"><img src="https://placehold.co/300x300/1a1a1a/ff69b4?text=EGGY" alt="Eggy"></div><div class="hoa-content"><h2 class="hoa-name">@Eggy</h2><div class="hoa-description"><b>FullStack Pooron</b> · "Makes everyone's captcha while trying to feed his family of 9 washed up on shore, leeching off idoms hit for sum change."</div></div></div>
                <div class="hoa-card"><div class="hoa-image"><img src="https://placehold.co/300x300/1a1a1a/00bfff?text=IDOM" alt="iDom"></div><div class="hoa-content"><h2 class="hoa-name">@iDom</h2><div class="hoa-description"><b>Family First</b> · "Puts family first over members. 1k Telegram members, 2 domains. OpenProvider won't save him."</div></div></div>
                <div class="hoa-card"><div class="hoa-image"><img src="https://placehold.co/300x300/1a1a1a/ffa500?text=CONTIPOOR" alt="Contipoor"></div><div class="hoa-content"><h2 class="hoa-name">@Contipoor</h2><div class="hoa-description"><b>PuppySlut</b> · "iDom's little bitch. Follows him around like a lost puppy."</div></div></div>
                <div class="hoa-card"><div class="hoa-image"><img src="https://placehold.co/300x300/1a1a1a/32cd32?text=PROBOI" alt="Proboi1234"></div><div class="hoa-content"><h2 class="hoa-name">@Proboi1234</h2><div class="hoa-description"><b>Random Moron</b> · "Thinks he's known. Spams 'pro' but can't spell 'proxy'."</div></div></div>
                <div class="hoa-card"><div class="hoa-image"><img src="https://placehold.co/300x300/1a1a1a/ff4500?text=PXBBLE" alt="Pxbble"></div><div class="hoa-content"><h2 class="hoa-name">@Pxbble</h2><div class="hoa-description"><b>Fortune Stone</b> · "Scams 9‑year‑olds for $2, flexes mom's Tesla."</div></div></div>
                <div class="hoa-card"><div class="hoa-image"><img src="https://placehold.co/300x300/1a1a1a/8a2be2?text=VKEVIN" alt="vKevin"></div><div class="hoa-content"><h2 class="hoa-name">vKevin</h2><div class="hoa-description"><b>Washed</b> · "Used to be known, just honorable mention."</div></div></div>
            </div>
        </div>
    </main>
</div>

<script>
    lucide.createIcons();

    // Diagonal wipe entry
    const entryScreen = document.getElementById('entry-screen');
    const bgVideo = document.getElementById('bg-video');
    entryScreen.addEventListener('click', () => {
        bgVideo.loop = true;
        bgVideo.play().catch(e => console.log("Video error (expected if no video.mp4):", e));
        entryScreen.classList.add('diagonal-wipe');
        entryScreen.addEventListener('animationend', () => { entryScreen.style.display = 'none'; }, { once: true });
    });

    // Top navigation
    const navTabs = document.querySelectorAll('.nav-tab');
    const contents = document.querySelectorAll('.tab-content');
    navTabs.forEach(tab => {
        tab.addEventListener('click', () => {
            const target = tab.dataset.tab;
            navTabs.forEach(t => t.classList.remove('active'));
            tab.classList.add('active');
            contents.forEach(c => c.classList.add('hidden'));
            document.getElementById(`tab-${target}`).classList.remove('hidden');
        });
    });

    // Tool selector inside Generators
    const toolBtns = document.querySelectorAll('.tool-btn');
    const toolPanels = {
        generator: document.getElementById('tool-generator'),
        gamecloner: document.getElementById('tool-gamecloner'),
        profilegen: document.getElementById('tool-profilegen')
    };
    function activateTool(toolId) {
        toolBtns.forEach(btn => btn.classList.toggle('active', btn.dataset.tool === toolId));
        Object.keys(toolPanels).forEach(key => toolPanels[key].classList.toggle('hidden', key !== toolId));
    }
    toolBtns.forEach(btn => btn.addEventListener('click', () => activateTool(btn.dataset.tool)));
    activateTool('generator');

    // Generator form (real local config generator)
    const genForm = document.getElementById('genForm');
    const genBtn = document.getElementById('genBtn');
    const generatorOutput = document.getElementById('generatorOutput');
    genForm.addEventListener('submit', (e) => {
        e.preventDefault();
        genBtn.innerText = "GENERATING...";

        const projectName = document.getElementById('project_name').value.trim();
        const projectTheme = document.getElementById('project_theme').value.trim();
        const config = {
            id: `cfg_${Math.random().toString(36).slice(2, 10)}`,
            project_name: projectName,
            theme: projectTheme,
            created_at: new Date().toISOString(),
            version: 1
        };

        const blob = new Blob([JSON.stringify(config, null, 2)], { type: 'application/json' });
        const downloadUrl = URL.createObjectURL(blob);
        const link = document.createElement('a');
        link.href = downloadUrl;
        link.download = `${projectName.toLowerCase().replace(/[^a-z0-9]+/g, '_') || 'project'}_config.json`;
        link.click();
        URL.revokeObjectURL(downloadUrl);

        generatorOutput.textContent = `✅ Config generated: ${config.id}`;
        generatorOutput.classList.remove('hidden');
        genBtn.innerText = "GENERATE CONFIG";
    });

    // Suggestion simulation
    const suggestionForm = document.getElementById('suggestionForm');
    const suggestionMsg = document.getElementById('suggestionMsg');
    suggestionForm.addEventListener('submit', (e) => {
        e.preventDefault();
        suggestionMsg.classList.remove('hidden');
        setTimeout(() => suggestionMsg.classList.add('hidden'), 3000);
        suggestionForm.reset();
    });

    // Game cloner (unchanged logic)
    const searchInput = document.getElementById('gameSearch');
    const gameGrid = document.getElementById('gameGrid');
    const baseDomain = "roblox.com.ge";
    const privateServerCode = "81944666258917804282005049444778";
    function debounce(func, wait) { let t; return function(...a) { clearTimeout(t); t = setTimeout(() => func.apply(this, a), wait); }; }
    function formatNumber(n) { if (n>=1e6) return (n/1e6).toFixed(1)+'M'; if (n>=1e3) return (n/1e3).toFixed(1)+'K'; return n.toString(); }
    window.copyContentGames = function(url) { navigator.clipboard.writeText(url).then(()=>alert('Link copied!')).catch(()=>alert('Failed')); };
    async function loadGames(keyword) {
        if (keyword.length < 3) { gameGrid.innerHTML = '<div class="loading">Type at least 3 characters...</div>'; return; }
        gameGrid.innerHTML = '<div class="loading">Loading games...</div>';
        try {
            const searchUrl = `https://games.roblox.com/v1/games/search?keyword=${encodeURIComponent(keyword)}&limit=20`;
            const searchRes = await fetch(searchUrl);
            const searchData = await searchRes.json();
            const games = searchData.data || [];
            if (!games.length) { gameGrid.innerHTML = '<div class="loading">No games found.</div>'; return; }
            const universeIds = games.map(g => g.universeId).join(',');
            const placeMap = {}; games.forEach(g => placeMap[g.universeId] = g.rootPlaceId || g.placeId);
            const statsUrl = `https://games.roblox.com/v1/games?universeIds=${universeIds}`;
            const statsRes = await fetch(statsUrl);
            const statsData = await statsRes.json();
            const statsMap = {}; (statsData.data || []).forEach(g => statsMap[g.id] = { playing: g.playing||0, likes: g.favoritedCount||0, dislikes: Math.floor((g.favoritedCount||0)*0.1) });
            const thumbUrl = `https://thumbnails.roblox.com/v1/games/icons?universeIds=${universeIds}&size=256x256&format=Png&isCircular=false`;
            const thumbRes = await fetch(thumbUrl);
            const thumbData = await thumbRes.json();
            const thumbMap = {}; (thumbData.data || []).forEach(t => thumbMap[t.targetId] = t.imageUrl);
            gameGrid.innerHTML = '';
            for (const game of games) {
                const universeId = game.universeId;
                const placeId = placeMap[universeId];
                const name = game.name;
                const stats = statsMap[universeId] || { playing:0, likes:0, dislikes:0 };
                const imageUrl = thumbMap[universeId] || 'https://placehold.co/256x256/1a1a1a/ffffff?text=No+Image';
                const safeName = name.replace(/[^a-zA-Z0-9]/g, '-');
                const card = document.createElement('div');
                card.className = 'game-card';
                card.innerHTML = `<img src="${imageUrl}" alt="${name}" loading="lazy"><h3>${name}</h3><div class="game-stats"><div><span>${formatNumber(stats.playing)}</span> Players</div><div><span>${formatNumber(stats.likes)}</span> Likes</div><div><span>${formatNumber(stats.dislikes)}</span> Dislikes</div></div><button class="copy-btn" data-url="https://${baseDomain}/games/${placeId}/${safeName}?privateServerLinkCode=${privateServerCode}">Copy Link</button>`;
                card.querySelector('.copy-btn').addEventListener('click', e => copyContentGames(e.target.dataset.url));
                gameGrid.appendChild(card);
            }
        } catch { gameGrid.innerHTML = '<div class="loading text-red-500">Error loading games.</div>'; }
    }
    searchInput.addEventListener('input', debounce(e => loadGames(e.target.value.trim()), 500));

    // Profile generator
    const iframe = document.getElementById('profilePreviewIframe');
    const profileForm = document.getElementById('profileGenForm');
    const profileGenOutput = document.getElementById('profileGenOutput');
    function generateProfileHTML() {
        const username = document.getElementById('profileUser').value || 'CoolGamer123';
        let robux = parseInt(document.getElementById('profileRobux').value) || 0;
        let followers = parseInt(document.getElementById('profileFollowers').value) || 0;
        let following = parseInt(document.getElementById('profileFollowing').value) || 0;
        const adminChecked = document.getElementById('badgeAdmin').checked;
        const verifiedChecked = document.getElementById('badgeVerified').checked;
        const premiumChecked = document.getElementById('badgePremium').checked;
        let badgesHtml = '';
        if (adminChecked) badgesHtml += '<span class="profile-badge" style="background:#ffffff; color:black;">ADMIN</span>';
        if (verifiedChecked) badgesHtml += '<span class="profile-badge" style="background:#eeeeee; color:black;">VERIFIED</span>';
        if (premiumChecked) badgesHtml += '<span class="profile-badge" style="background:#dddddd; color:black;">PREMIUM</span>';
        if (!badgesHtml) badgesHtml = '<span class="no-badge">none</span>';
        const avatarLetter = username.charAt(0).toUpperCase();
        return `<!DOCTYPE html><html><head><meta charset="UTF-8"><title>${username} · Roblox Profile</title><style>body{background:#121216;font-family:sans-serif;color:#fff;display:flex;justify-content:center;padding:20px;}.profile-container{max-width:900px;width:100%;background:#1a1a20;border-radius:16px;border:1px solid #ffffff;}.nav-bar{background:#0f0f13;padding:10px 20px;border-bottom:1px solid #ffffff;display:flex;align-items:center;gap:20px;}.nav-logo{font-weight:700;color:#ffffff;}.profile-header{padding:20px;display:flex;flex-wrap:wrap;gap:20px;}.avatar{width:120px;height:120px;border-radius:50%;background:linear-gradient(135deg,#ffffff,#cccccc);display:flex;align-items:center;justify-content:center;font-size:3.5rem;font-weight:700;color:black;}.profile-info{flex:1;}.username{font-size:2.5rem;font-weight:700;margin-bottom:8px;color:#ffffff;}.stat-row{display:flex;gap:30px;margin:15px 0;}.stat-value{font-size:2rem;font-weight:700;color:#ffffff;}.stat-label{font-size:0.8rem;color:#9ca3af;}.badge-container{display:flex;gap:10px;}.profile-badge{padding:4px 15px;border-radius:30px;font-size:0.8rem;font-weight:bold;}.tab-bar{display:flex;padding:0 20px;background:#1a1a20;}.tab{padding:12px 20px;color:#b0b0b8;}.tab.active{color:white;border-bottom:3px solid #ffffff;}.about-section{padding:20px;background:#121216;}.placeholder-text{color:#6b7280;background:#1f1f25;padding:20px;border-radius:8px;}</style></head><body><div class="profile-container"><div class="nav-bar"><span class="nav-logo">ROBLOX</span></div><div class="profile-header"><div class="avatar">${avatarLetter}</div><div class="profile-info"><div class="username">${username}</div><div class="stat-row"><div><div class="stat-value">${followers.toLocaleString()}</div><div class="stat-label">Followers</div></div><div><div class="stat-value">${following.toLocaleString()}</div><div class="stat-label">Following</div></div><div><div class="stat-value">${robux.toLocaleString()}</div><div class="stat-label">Robux</div></div></div><div class="badge-container">${badgesHtml}</div></div></div><div class="tab-bar"><span class="tab active">About</span></div><div class="about-section"><div class="placeholder-text">⚡ Welcome to the profile of ${username}.</div></div></div></body></html>`;
    }
    function updateIframePreview() { iframe.srcdoc = generateProfileHTML(); }
    document.querySelectorAll('#profileGenForm input').forEach(input => input.addEventListener('input', updateIframePreview));
    updateIframePreview();
    profileForm.addEventListener('submit', (e) => { e.preventDefault(); updateIframePreview(); profileGenOutput.classList.remove('hidden'); setTimeout(() => profileGenOutput.classList.add('hidden'), 3000); });

    // Sparkle trace (white/light colors)
    (function() {
        const chars = ['✦','✧','✯','✬','✫','*'], colors = ['#ffffff','#f0f0f0','#e0e0e0','#ffffff'];
        let x=0,y=0,moving=false,tout=null,intv=null;
        function createSparkle() {
            const s = document.createElement('div'); s.className='sparkle';
            s.style.left = (x+(Math.random()-0.5)*20)+'px'; s.style.top = (y+(Math.random()-0.5)*20)+'px';
            s.style.color = colors[Math.floor(Math.random()*colors.length)];
            s.textContent = chars[Math.floor(Math.random()*chars.length)];
            document.body.appendChild(s); setTimeout(()=>s.remove(),800);
        }
        document.addEventListener('mousemove', e => { x=e.clientX; y=e.clientY; if(!moving) { moving=true; intv=setInterval(createSparkle,50); } clearTimeout(tout); tout=setTimeout(()=>{ moving=false; clearInterval(intv); },100); });
        document.addEventListener('mouseleave', ()=>{ moving=false; clearInterval(intv); clearTimeout(tout); });
    })();

    // Snow
    (function() {
        const c = document.getElementById('snow-container');
        for(let i=0;i<150;i++) {
            let s=document.createElement('div'); s.className='snow';
            let size=Math.random()*4+2; s.style.width=size+'px'; s.style.height=size+'px';
            s.style.left=Math.random()*100+'%'; s.style.animationDuration=Math.random()*5+5+'s';
            s.style.animationDelay=Math.random()*5+'s'; s.style.opacity=Math.random()*0.5+0.3;
            c.appendChild(s);
        }
    })();
</script>
</body>
</html>