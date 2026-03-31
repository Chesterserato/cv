<?php
// 1. Logic & Security
if ($_SERVER["REQUEST_METHOD"] != "POST") {
    header("Location: index.html");
    exit();
}

$fullname  = htmlspecialchars($_POST['fullname'] ?? 'SYSTEM_USER');
$email     = htmlspecialchars($_POST['email'] ?? 'NULL');
$phone     = htmlspecialchars($_POST['phone'] ?? 'NULL');
$address   = htmlspecialchars($_POST['address'] ?? 'NULL');
$summary   = htmlspecialchars($_POST['summary'] ?? '');
$education = htmlspecialchars($_POST['education'] ?? '');
$skills    = htmlspecialchars($_POST['skills'] ?? '');

// 2. Image Processing
$photoData = "";
if (isset($_FILES['profile_pic']) && $_FILES['profile_pic']['error'] == 0) {
    $imgBinary = file_get_contents($_FILES['profile_pic']['tmp_name']);
    $photoData = "data:" . $_FILES['profile_pic']['type'] . ";base64," . base64_encode($imgBinary);
} else {
    // Terminal-style fallback avatar
    $photoData = "https://ui-avatars.com/api/?name=" . urlencode($fullname) . "&size=300&background=0d1117&color=4ade80";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>./build_cv.sh --run <?php echo strtolower(str_replace(' ', '_', $fullname)); ?></title>
    <link href="https://fonts.googleapis.com/css2?family=JetBrains+Mono:wght@300;400;700&family=Fira+Code:wght@400;500&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        :root {
            --bg-dark: #0d1117;       /* Terminal Background */
            --bg-panel: #161b22;      /* Window Background */
            --green: #4ade80;         /* Execution Green */
            --blue: #58a6ff;          /* Variable Blue */
            --border: #30363d;
            --text-dim: #8b949e;
            --text-main: #c9d1d9;
        }

        * { box-sizing: border-box; margin: 0; padding: 0; }
        
        body {
            font-family: 'JetBrains Mono', 'Fira Code', monospace;
            background-color: var(--bg-dark);
            color: var(--text-main);
            padding: 40px 20px;
            line-height: 1.6;
        }

        /* Terminal Window Frame */
        .terminal-window {
            max-width: 900px;
            margin: 0 auto;
            background: var(--bg-panel);
            border: 1px solid var(--border);
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 30px 60px rgba(0,0,0,0.5);
        }

        /* Top Bar mimicking VS Code/Terminal tabs */
        .terminal-header {
            background: #21262d;
            padding: 12px 20px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            border-bottom: 1px solid var(--border);
        }

        .window-controls { display: flex; gap: 8px; }
        .control { width: 12px; height: 12px; border-radius: 50%; }
        .red { background: #ff5f56; }
        .yellow { background: #ffbd2e; }
        .green { background: #27c93f; }

        .tab-title {
            font-size: 12px;
            color: var(--text-dim);
            font-weight: 500;
        }

        /* Content Area */
        .terminal-body {
            padding: 40px 60px;
        }

        .command-line {
            margin-bottom: 30px;
            font-size: 18px;
        }

        .prompt { color: var(--green); font-weight: bold; }
        .command { color: var(--blue); }

        /* Identity Section */
        .identity-row {
            display: grid;
            grid-template-columns: 180px 1fr;
            gap: 40px;
            margin-bottom: 40px;
            padding-bottom: 30px;
            border-bottom: 1px dashed var(--border);
        }

        .avatar-frame {
            width: 180px;
            height: 180px;
            border: 2px solid var(--blue);
            background: var(--bg-dark);
            padding: 5px;
        }

        .avatar-frame img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            filter: grayscale(0.5) contrast(1.2);
        }

        .bio-data h1 {
            font-size: 32px;
            color: var(--green);
            margin-bottom: 10px;
            text-transform: uppercase;
        }

        .label { color: var(--blue); font-weight: bold; font-size: 11px; text-transform: uppercase; display: block; margin-bottom: 4px; }
        .value { color: var(--text-main); font-size: 14px; margin-bottom: 15px; display: block; }

        /* Grid Layout for Sections */
        .info-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 40px;
        }

        .section-header {
            color: var(--blue);
            font-size: 14px;
            border-bottom: 1px solid var(--border);
            margin-bottom: 20px;
            padding-bottom: 5px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .section-header::before { content: ">"; color: var(--green); }

        .block-content {
            font-size: 13px;
            background: rgba(0,0,0,0.2);
            padding: 15px;
            border-left: 2px solid var(--green);
            white-space: pre-line;
            color: var(--text-dim);
        }

        /* Skills Tags */
        .skills-wrap {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
        }

        .skill-node {
            background: #23863622;
            border: 1px solid #238636;
            color: var(--green);
            padding: 5px 12px;
            border-radius: 4px;
            font-size: 11px;
        }

        /* Execution Buttons */
        .footer-actions {
            margin-top: 40px;
            display: flex;
            gap: 15px;
            justify-content: center;
        }

        .btn-exec {
            background: var(--green);
            color: var(--bg-dark);
            border: none;
            padding: 12px 25px;
            font-family: 'JetBrains Mono', monospace;
            font-weight: bold;
            text-transform: uppercase;
            cursor: pointer;
            border-radius: 4px;
        }

        .btn-abort {
            background: transparent;
            border: 1px solid #da3633;
            color: #da3633;
            padding: 12px 25px;
            font-family: 'JetBrains Mono', monospace;
            border-radius: 4px;
            text-decoration: none;
        }

        @media print {
            body { background: white; color: black; padding: 0; }
            .terminal-window { box-shadow: none; border: 1px solid #ccc; width: 100%; }
            .footer-actions, .window-controls { display: none; }
            .terminal-header { background: #eee; border-bottom: 1px solid #ccc; }
        }
    </style>
</head>
<body>

    <div class="terminal-window">
        <div class="terminal-header">
            <div class="window-controls">
                <div class="control red"></div>
                <div class="control yellow"></div>
                <div class="control green"></div>
            </div>
            <div class="tab-title">runtime_profile.log</div>
            <div></div> </div>

        <div class="terminal-body">
            <div class="command-line">
                <span class="prompt">$</span> <span class="command">./build_cv.sh</span> --user "<?php echo $fullname; ?>"
            </div>

            <div class="identity-row">
                <div class="avatar-frame">
                    <img src="<?php echo $photoData; ?>" alt="System Avatar">
                </div>
                <div class="bio-data">
                    <span class="label">SYS_FULLNAME:</span>
                    <h1><?php echo $fullname; ?></h1>
                    
                    <div style="display: grid; grid-template-columns: 1fr 1fr; margin-top: 20px;">
                        <div>
                            <span class="label">NETWORK_EMAIL:</span>
                            <span class="value"><?php echo $email; ?></span>
                        </div>
                        <div>
                            <span class="label">COMM_PORT:</span>
                            <span class="value"><?php echo $phone; ?></span>
                        </div>
                    </div>
                    <span class="label">HOST_LOCATION:</span>
                    <span class="value"><?php echo $address; ?></span>
                </div>
            </div>

            <div class="info-grid">
                <div class="main-stream">
                    <section class="section-box">
                        <div class="section-header">RUNTIME_SUMMARY</div>
                        <div class="block-content"><?php echo $summary; ?></div>
                    </section>

                    <section class="section-box" style="margin-top: 30px;">
                        <div class="section-header">KNOWLEDGE_BASE</div>
                        <div class="block-content"><?php echo $education; ?></div>
                    </section>
                </div>

                <div class="side-stream">
                    <section class="section-box">
                        <div class="section-header">DEPENDENCIES (Skills)</div>
                        <div class="skills-wrap">
                            <?php 
                            $skillsArr = explode(',', $skills);
                            foreach($skillsArr as $skill) {
                                if(trim($skill) != "") {
                                    echo '<div class="skill-node">[' . htmlspecialchars(trim($skill)) . ']</div>';
                                }
                            }
                            ?>
                        </div>
                    </section>
                </div>
            </div>
        </div>
    </div>

    <div class="footer-actions">
        <button class="btn-exec" onclick="window.print()">[ Execute Print ]</button>
        <a href="index.html" class="btn-abort">Abort Build</a>
    </div>

</body>
</html>
