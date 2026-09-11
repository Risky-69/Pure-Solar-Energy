<?php
// Initialize default values
$panelPower     = isset($_POST['panel_power']) ? floatval($_POST['panel_power']) : '';
$panelVoltage   = isset($_POST['panel_voltage']) ? floatval($_POST['panel_voltage']) : '';
$batteryAh      = isset($_POST['battery_ah']) ? floatval($_POST['battery_ah']) : '';
$batteryVoltage = isset($_POST['battery_voltage']) ? floatval($_POST['battery_voltage']) : '';
$loadWh         = isset($_POST['load_wh']) ? floatval($_POST['load_wh']) : '';
$loadHours      = isset($_POST['load_hours']) ? floatval($_POST['load_hours']) : '';
$sunHours       = isset($_POST['sun_hours']) ? floatval($_POST['sun_hours']) : 4.5;

$calculated = ($_SERVER['REQUEST_METHOD'] === 'POST');

if ($calculated) {
    // Math Calculations
    $dailyLoadEnergy = $loadWh * ($loadHours > 0 ? $loadHours : 1); // Total Daily Required (Wh)
    $dailySolarGen   = $panelPower * $sunHours;                     // Total Daily Solar Production (Wh)
    $batteryStorage  = $batteryAh * $batteryVoltage;                // Total Battery Storage (Wh)

    // Feasibility Checks
    $solarSufficient   = $dailySolarGen >= $dailyLoadEnergy;
    $batterySufficient = $batteryStorage >= $dailyLoadEnergy;
    $isFeasible        = ($solarSufficient && $batterySufficient);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Solar Energy Calculator - Pure Solar Energy</title>
    <style>
        :root {
            --bg-main: #0a131c;
            --bg-card: #0e1c2a;
            --bg-sidebar: #060e17;
            --border-card: #1d3147;
            --text-primary: #ffffff;
            --text-secondary: #8fa0b5;
            --accent-cyan: #00ffd5;
            --accent-button: #21b6fa;
            --accent-blue: #00d2ff;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: var(--bg-main);
            color: var(--text-primary);
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            padding: 40px 20px;
            margin: 0;
            box-sizing: border-box;
        }

        .calculator-container {
            background: var(--bg-card);
            border: 1px solid var(--border-card);
            padding: 35px;
            border-radius: 12px;
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.4);
            max-width: 480px;
            width: 100%;
        }

        .calc-header {
            margin-bottom: 25px;
            text-align: left;
        }

        .calc-title {
            font-size: 1.6rem;
            font-weight: 700;
            color: #fff;
            margin: 0 0 10px 0;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .return-link {
            color: var(--accent-button);
            text-decoration: none;
            font-size: 0.85rem;
            font-weight: 600;
            display: inline-block;
            transition: color 0.2s;
        }

        .return-link:hover {
            color: var(--accent-cyan);
            text-decoration: underline;
        }

        .form-group {
            margin-bottom: 18px;
            display: flex;
            flex-direction: column;
        }

        label {
            margin-bottom: 8px;
            font-weight: 600;
            font-size: 0.88rem;
            color: var(--text-secondary);
            letter-spacing: 0.5px;
        }

        input, select {
            padding: 12px 14px;
            background-color: var(--bg-sidebar);
            border: 1px solid var(--border-card);
            border-radius: 6px;
            font-size: 0.95rem;
            color: var(--text-primary);
            outline: none;
            transition: border-color 0.2s ease, box-shadow 0.2s ease;
        }

        input::placeholder {
            color: #3b5066;
        }

        input:focus, select:focus {
            border-color: var(--accent-cyan);
            box-shadow: 0 0 8px rgba(0, 255, 213, 0.2);
        }

        select option {
            background-color: var(--bg-sidebar);
            color: var(--text-primary);
        }

        button {
            background-color: var(--accent-button);
            color: var(--text-primary);
            border: none;
            border-radius: 6px;
            padding: 14px;
            font-size: 1rem;
            font-weight: 700;
            cursor: pointer;
            margin-top: 10px;
            width: 100%;
            transition: background-color 0.2s ease, transform 0.1s ease;
        }

        button:hover {
            background-color: var(--accent-blue);
            transform: translateY(-1px);
        }

        .results-container {
            margin-top: 25px;
            padding-top: 20px;
            border-top: 1px solid var(--border-card);
        }

        .result-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 12px;
            margin-bottom: 15px;
        }

        .result-card {
            background: var(--bg-sidebar);
            border: 1px solid var(--border-card);
            padding: 12px;
            border-radius: 6px;
        }

        .result-card .lbl {
            font-size: 0.75rem;
            color: var(--text-secondary);
            margin-bottom: 4px;
        }

        .result-card .val {
            font-size: 1.1rem;
            font-weight: bold;
            color: var(--accent-cyan);
        }

        .status-box {
            margin-top: 15px;
            padding: 14px;
            border-radius: 6px;
            font-weight: 700;
            text-align: center;
            font-size: 0.95rem;
            line-height: 1.4;
        }

        .status-success {
            background-color: rgba(0, 255, 213, 0.12);
            border: 1px solid var(--accent-cyan);
            color: var(--accent-cyan);
        }

        .status-danger {
            background-color: rgba(255, 75, 75, 0.12);
            border: 1px solid #ff4b4b;
            color: #ff6b6b;
        }
    </style>
</head>
<body>

    <div class="calculator-container">
        
        <div class="calc-header">
            <h1 class="calc-title">Solar Energy Calculator</h1>
            <a href="../../MAIN.php" class="return-link">🏠 Return to Home</a>
        </div>

        <form method="POST" action="calculator.php">
            
            <div class="form-group">
                <label for="panel_power">Panel Power (Watts):</label>
                <input type="number" step="any" id="panel_power" name="panel_power" placeholder="e.g. 400" value="<?= htmlspecialchars($panelPower) ?>" required>
            </div>

            <div class="form-group">
                <label for="panel_voltage">Panel Voltage (Vmp):</label>
                <input type="number" step="any" id="panel_voltage" name="panel_voltage" placeholder="e.g. 30" value="<?= htmlspecialchars($panelVoltage) ?>" required>
            </div>

            <div class="form-group">
                <label for="battery_ah">Battery Capacity (Ah):</label>
                <input type="number" step="any" id="battery_ah" name="battery_ah" placeholder="e.g. 100" value="<?= htmlspecialchars($batteryAh) ?>" required>
            </div>

            <div class="form-group">
                <label for="battery_voltage">Battery Voltage (V):</label>
                <input type="number" step="any" id="battery_voltage" name="battery_voltage" placeholder="e.g. 12" value="<?= htmlspecialchars($batteryVoltage) ?>" required>
            </div>

            <div class="form-group">
                <label for="load_wh">Load (Wh):</label>
                <input type="number" step="any" id="load_wh" name="load_wh" placeholder="e.g. 150" value="<?= htmlspecialchars($loadWh) ?>" required>
            </div>

            <div class="form-group">
                <label for="load_hours">Load Time (H):</label>
                <input type="number" step="any" id="load_hours" name="load_hours" placeholder="e.g. 8" value="<?= htmlspecialchars($loadHours) ?>" required>
            </div>

            <div class="form-group">
                <label for="sun_hours">Peak Sun Hours / Day:</label>
                <input type="number" step="0.1" id="sun_hours" name="sun_hours" value="<?= htmlspecialchars($sunHours) ?>" required>
            </div>

            <button type="submit">Calculate System</button>
        </form>

        <!-- RESULTS SECTION -->
        <?php if ($calculated): ?>
            <div class="results-container">
                
                <div class="result-grid">
                    <div class="result-card">
                        <div class="lbl">Daily Solar Output</div>
                        <div class="val"><?= number_format($dailySolarGen, 1) ?> Wh</div>
                    </div>
                    <div class="result-card">
                        <div class="lbl">Battery Storage</div>
                        <div class="val"><?= number_format($batteryStorage, 1) ?> Wh</div>
                    </div>
                    <div class="result-card" style="grid-column: span 2;">
                        <div class="lbl">Total Daily Required Load</div>
                        <div class="val" style="color: #ffffff;"><?= number_format($dailyLoadEnergy, 1) ?> Wh</div>
                    </div>
                </div>

                <?php if ($isFeasible): ?>
                    <div class="status-box status-success">
                        ✅ System Feasible!<br>
                        <span style="font-size: 0.8rem; font-weight: normal;">Your solar panel and battery setup meet your daily energy requirement.</span>
                    </div>
                <?php else: ?>
                    <div class="status-box status-danger">
                        ⚠️ System Deficit!<br>
                        <span style="font-size: 0.8rem; font-weight: normal;">
                            <?php if (!$solarSufficient && !$batterySufficient): ?>
                                Both daily solar generation and battery storage are insufficient for this load.
                            <?php elseif (!$solarSufficient): ?>
                                Solar generation (<?= number_format($dailySolarGen, 1) ?> Wh) is below daily load (<?= number_format($dailyLoadEnergy, 1) ?> Wh).
                            <?php else: ?>
                                Battery storage (<?= number_format($batteryStorage, 1) ?> Wh) is too small to power load for <?= $loadHours ?> hours.
                            <?php endif; ?>
                        </span>
                    </div>
                <?php endif; ?>

            </div>
        <?php endif; ?>

    </div>

</body>
</html>