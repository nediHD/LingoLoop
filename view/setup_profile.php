<?php
define('BASE_PATH', dirname(__DIR__));
require_once BASE_PATH . '/models/SessionManager.php';
require_once BASE_PATH . '/models/Database.php';
require_once BASE_PATH . '/models/SaveProfile.php';

SessionManager::startSession();

if (!SessionManager::isLoggedIn()) {
    header("Location: /lingoloop/view/login.php");
    exit();
}

$userId = $_SESSION['user_id'];
$db = Database::getInstance();
$saveProfile = new SaveProfile($db);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = [
        'user_id' => $userId,
        'first_name' => trim($_POST['first_name']),
        'last_name' => trim($_POST['last_name']),
        'birth_date' => trim($_POST['birth_date']),
        'country' => trim($_POST['country']),
        'english_level' => trim($_POST['english_level']),
        'learning_goal' => trim($_POST['learning_goal']),
        'learning_time_per_day' => trim($_POST['learning_time_per_day']),
        'learning_style' => trim($_POST['learning_style']),
        'previous_apps' => trim($_POST['previous_apps']),
        'interests' => trim($_POST['interests']),
        'favorite_content' => trim($_POST['favorite_content']),
        'target_language' => trim($_POST['target_language']) // ✅ Dodano
    ];
    $saveProfile->create($data);
}
?>
<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <title>Profil-Einrichtung | LingoLoop</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="https://cdn.jsdelivr.net/npm/alpinejs" defer></script>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            background-color: #000;
            color: #fff;
            font-family: Arial, sans-serif;
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            padding: 20px;
        }
        .container {
            background-color: #111;
            padding: 20px;
            border-radius: 8px;
            width: 100%;
            max-width: 600px;
            box-shadow: 0 4px 8px rgba(0,0,0,0.5);
        }
        h2 { margin-bottom: 20px; text-align: center; }
        .step { display: none; opacity: 0; transition: opacity 0.5s ease-in-out; }
        .step.active { display: block; opacity: 1; }
        .form-group { margin-bottom: 15px; }
        label { display: block; margin-bottom: 5px; font-size: 0.9em; }
        input[type="text"], input[type="date"], select, textarea {
            width: 100%; padding: 10px; border: 1px solid #333; border-radius: 4px;
            background-color: #222; color: #fff; font-size: 0.95em;
        }
        textarea { resize: vertical; min-height: 80px; }
        .nav-buttons {
            display: flex; justify-content: space-between; margin-top: 20px;
        }
        .nav-buttons button {
            border: none; padding: 10px 20px; border-radius: 4px;
            cursor: pointer; transition: background-color 0.3s; color: #fff;
        }
        #nextBtn { background-color: #28a745; }
        #nextBtn:hover { background-color: #218838; }
        #submitBtn { background-color: #dc3545; }
        #submitBtn:hover { background-color: #c82333; }
        #prevBtn { background-color: #333; }
        #prevBtn:hover { background-color: #555; }
        @media (max-width: 600px) {
            .nav-buttons { flex-direction: column; }
            .nav-buttons button { width: 100%; margin-bottom: 10px; }
        }
    </style>
</head>
<body>
    <div class="container" x-data="wizard()">
        <form id="profileForm" action="/lingoloop/view/setup_profile.php" method="POST" onsubmit="return validateForm()">
            <input type="hidden" name="user_id" value="<?= $userId ?>">

            <!-- Step 0 -->
            <div class="step" id="step0">
                <h2>Willkommen!</h2>
                <p style="margin-bottom: 20px;">
                    Um Ihnen das bestmögliche Lernerlebnis zu bieten, beantworten Sie bitte einige Fragen.
                </p>
            </div>

            <!-- Step 1 -->
            <div class="step" id="step1">
                <h2>Persönliche Daten</h2>
                <div class="form-group">
                    <label>Vorname</label>
                    <input type="text" name="first_name" required>
                </div>
                <div class="form-group">
                    <label>Nachname</label>
                    <input type="text" name="last_name" required>
                </div>
                <div class="form-group">
                    <label>Geburtsdatum</label>
                    <input type="date" name="birth_date" required>
                </div>
                <div class="form-group">
                    <label>Land</label>
                    <select name="country" required>
                        <option value="">Bitte wählen</option>
                        <option value="Deutschland">Deutschland</option>
                        <option value="Österreich">Österreich</option>
                        <option value="Schweiz">Schweiz</option>
                        <option value="Andere">Andere</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Englisch Niveau (A1–C2)</label>
                    <select name="english_level" required>
                        <option value="">Bitte wählen</option>
                        <option value="A1">A1</option>
                        <option value="A2">A2</option>
                        <option value="B1">B1</option>
                        <option value="B2">B2</option>
                        <option value="C1">C1</option>
                        <option value="C2">C2</option>
                    </select>
                </div>
            </div>

            <!-- Step 2 -->
            <div class="step" id="step2">
                <h2>Lernziele</h2>
                <div class="form-group">
                    <label>Warum lernen Sie Englisch?</label>
                    <input type="text" name="learning_goal" required>
                </div>
                <div class="form-group">
                    <label>Wie viel Zeit pro Tag?</label>
                    <select name="learning_time_per_day" required>
                        <option value="">Bitte wählen</option>
                        <option value="10 Minuten">10 Minuten</option>
                        <option value="20 Minuten">20 Minuten</option>
                        <option value="30+ Minuten">30+ Minuten</option>
                    </select>
                </div>
            </div>

            <!-- Step 3 -->
            <div class="step" id="step3">
                <h2>Lernstil</h2>
                <div class="form-group">
                    <label>Bevorzugte Lernmethode</label>
                    <input type="text" name="learning_style" required>
                </div>
                <div class="form-group">
                    <label>Apps, die Sie bisher genutzt haben</label>
                    <input type="text" name="previous_apps">
                </div>
            </div>

            <!-- Step 4 -->
            <div class="step" id="step4">
                <h2>Interessen & Hobbys</h2>
                <div class="form-group">
                    <label>Ihre Interessen</label>
                    <textarea name="interests" required></textarea>
                </div>
                <div class="form-group">
                    <label>Lieblingsinhalte (YouTube, Podcasts...)</label>
                    <textarea name="favorite_content" required></textarea>
                </div>
            </div>

            <!-- Step 5: Neuer Schritt -->
            <div class="step" id="step5">
                <h2>Ziel-Sprache</h2>
                <div class="form-group">
                    <label>Welche Sprache möchten Sie lernen?</label>
                    <select name="target_language" required>
                        <option value="">Bitte wählen</option>
                        <option value="EN">Englisch</option>
                        <option value="FR">Französisch</option>
                        <option value="ES">Spanisch</option>
                    </select>
                </div>
            </div>

            <!-- Buttons -->
            <div class="nav-buttons">
                <button type="button" id="prevBtn" onclick="prevStep()" disabled>Zurück</button>
                <button type="button" id="nextBtn" onclick="nextStep()">Weiter</button>
                <button type="submit" id="submitBtn" style="display: none;">Fertig</button>
            </div>
        </form>
    </div>

    <script>
        let currentStep = 0;
        const steps = document.querySelectorAll('.step');
        const prevBtn = document.getElementById('prevBtn');
        const nextBtn = document.getElementById('nextBtn');
        const submitBtn = document.getElementById('submitBtn');

        function showStep(index) {
            steps.forEach((step, i) => step.classList.toggle('active', i === index));
            prevBtn.disabled = index === 0;
            nextBtn.style.display = index === steps.length - 1 ? 'none' : 'inline-block';
            submitBtn.style.display = index === steps.length - 1 ? 'inline-block' : 'none';
        }

        function nextStep() {
            if (validateCurrentStep()) {
                if (currentStep < steps.length - 1) {
                    currentStep++;
                    showStep(currentStep);
                }
            }
        }

        function prevStep() {
            if (currentStep > 0) {
                currentStep--;
                showStep(currentStep);
            }
        }

        function validateCurrentStep() {
            if (currentStep === 0) return true;
            const fields = steps[currentStep].querySelectorAll('[required]');
            for (const field of fields) {
                if (!field.value.trim()) {
                    alert("Bitte ausfüllen: " + field.previousElementSibling.textContent);
                    field.focus();
                    return false;
                }
            }
            return true;
        }

        function validateForm() {
            for (let i = 1; i < steps.length; i++) {
                const fields = steps[i].querySelectorAll('[required]');
                for (const field of fields) {
                    if (!field.value.trim()) {
                        alert("Bitte ausfüllen: " + field.previousElementSibling.textContent);
                        currentStep = i;
                        showStep(currentStep);
                        field.focus();
                        return false;
                    }
                }
            }
            return true;
        }

        showStep(currentStep);
    </script>
</body>
</html>
