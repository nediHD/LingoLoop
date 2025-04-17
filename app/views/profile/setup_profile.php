<?php
require_once __DIR__ . '/../../../core/SessionManager.php';

SessionManager::startSession();

if (!SessionManager::isLoggedIn()) {
    header("Location: /lingoloop/public/?action=login");
    exit();
}

$username = $_SESSION['username'];
$userId = $_SESSION['user_id'];
?>
<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <title>Profil-Einrichtung | LingoLoop</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <!-- Koristimo Alpine.js samo za navigaciju wizardom -->
    <script src="https://cdn.jsdelivr.net/npm/alpinejs" defer></script>
    <style>
        /* Osnovni reset i stilovi */
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
        input[type="text"],
        input[type="date"],
        select,
        textarea {
            width: 100%;
            padding: 10px;
            border: 1px solid #333;
            border-radius: 4px;
            background-color: #222;
            color: #fff;
            font-size: 0.95em;
        }
        textarea { resize: vertical; min-height: 80px; }
        .nav-buttons {
            display: flex;
            justify-content: space-between;
            margin-top: 20px;
        }
        .nav-buttons button {
            border: none;
            padding: 10px 20px;
            border-radius: 4px;
            cursor: pointer;
            transition: background-color 0.3s;
            color: #fff;
        }
        #nextBtn { background-color: #28a745; } /* zelena */
        #nextBtn:hover { background-color: #218838; }
        #submitBtn { background-color: #dc3545; } /* crvena */
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
        <form id="profileForm" action="/lingoloop/public/SaveProfileController.php" method="POST" onsubmit="return validateForm()">
            <input type="hidden" name="user_id" value="<?= $userId ?>">
            
            <!-- Step 0: Einführung -->
            <div class="step" id="step0">
                <h2>Willkommen!</h2>
                <p style="margin-bottom: 20px;">
                    Um Ihnen das bestmögliche Lernerlebnis zu bieten, bitten wir Sie, einige kurze Fragen zu beantworten.
                    Ihre Antworten helfen uns, den Unterricht genau auf Ihre Interessen und Bedürfnisse abzustimmen.
                    Alle Daten sind vollständig privat und werden ausschließlich zur Personalisierung Ihres Lernens verwendet.
                    Vielen Dank für Ihre Zusammenarbeit!
                </p>
            </div>
            
            <!-- Step 1: Persönliche Daten -->
            <div class="step" id="step1">
                <h2>Schritt 1: Persönliche Daten</h2>
                <div class="form-group">
                    <label>Vorname (z.B. "Max")</label>
                    <input type="text" name="first_name" placeholder="Max" required>
                </div>
                <div class="form-group">
                    <label>Nachname (z.B. "Mustermann")</label>
                    <input type="text" name="last_name" placeholder="Mustermann" required>
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
            
            <!-- Step 2: Lernziele -->
            <div class="step" id="step2">
                <h2>Schritt 2: Lernziele</h2>
                <div class="form-group">
                    <label>Warum lernen Sie Englisch? (z.B. "Für die Arbeit")</label>
                    <input type="text" name="learning_goal" placeholder="Für die Arbeit" required>
                </div>
                <div class="form-group">
                    <label>Wie viel Zeit pro Tag? (z.B. "20 Minuten")</label>
                    <select name="learning_time_per_day" required>
                        <option value="">Bitte wählen</option>
                        <option value="10 Minuten">10 Minuten</option>
                        <option value="20 Minuten">20 Minuten</option>
                        <option value="30+ Minuten">30+ Minuten</option>
                    </select>
                </div>
            </div>
            
            <!-- Step 3: Lernstil -->
            <div class="step" id="step3">
                <h2>Schritt 3: Lernstil</h2>
                <div class="form-group">
                    <label>Bevorzugte Lernmethode (z.B. "Vokabeln üben, Videos anschauen, Lesen, Schreiben")</label>
                    <input type="text" name="learning_style" placeholder="Vokabeln üben, Videos anschauen, Lesen, Schreiben" required>
                </div>
                <div class="form-group">
                    <label>Apps, die Sie bisher genutzt haben (optional)</label>
                    <input type="text" name="previous_apps" placeholder="z.B. Duolingo">
                </div>
            </div>
            
            <!-- Step 4: Interessen & Hobbys -->
            <div class="step" id="step4">
                <h2>Schritt 4: Interessen & Hobbys</h2>
                <div class="form-group">
                    <label>Ihre Interessen, Hobbys und Leidenschaften</label>
                    <textarea name="interests" placeholder="z.B. Fußball, Lesen, Musik (mehrere mit Komma trennen)" required></textarea>
                </div>
                <div class="form-group">
                    <label>Was schauen oder hören Sie am liebsten?</label>
                    <textarea name="favorite_content" placeholder="z.B. YouTube-Kanäle, Podcasts" required></textarea>
                </div>
            </div>
            
            <!-- Navigation Buttons -->
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
            steps.forEach((step, i) => {
                step.classList.toggle('active', i === index);
            });
            prevBtn.disabled = (index === 0);
            if (index === steps.length - 1) {
                nextBtn.style.display = 'none';
                submitBtn.style.display = 'inline-block';
            } else {
                nextBtn.style.display = 'inline-block';
                submitBtn.style.display = 'none';
            }
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

        // Custom validation for current step (skip intro step)
        function validateCurrentStep() {
            if (currentStep === 0) return true;
            const currentFields = steps[currentStep].querySelectorAll('input[required], select[required], textarea[required]');
            for (const field of currentFields) {
                if (!field.value.trim()) {
                    alert("Bitte füllen Sie das Feld '" + field.previousElementSibling.textContent.trim() + "' aus.");
                    field.focus();
                    return false;
                }
            }
            return true;
        }

        function validateForm() {
            // Validate all steps before submitting
            for (let i = 1; i < steps.length; i++) {
                const fields = steps[i].querySelectorAll('input[required], select[required], textarea[required]');
                for (const field of fields) {
                    if (!field.value.trim()) {
                        alert("Bitte füllen Sie das Feld '" + field.previousElementSibling.textContent.trim() + "' aus.");
                        currentStep = i;
                        showStep(currentStep);
                        field.focus();
                        return false;
                    }
                }
            }
            return true;
        }

        // Initialize first step
        showStep(currentStep);
    </script>
    <script>
    window.addEventListener("beforeunload", function () {
        navigator.sendBeacon("/lingoloop/public/logout.php");
    });
    </script>
</body>
</html>
