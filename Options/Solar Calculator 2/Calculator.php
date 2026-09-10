<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Solar Calculator - PureSolar Energy</title>
    <!-- Link your CSS file relative to calculator.php location -->
    <link rel="stylesheet" href="CalculatorUI.css">
</head>
<body>
    <div class="calculator-container">
        <h2>Solar Energy Calculator</h2>
       <a href="../../MAIN.php" class="btn-return-home">🏠 Return to Home</a>
        
        <!-- Input Form -->
        <form id="solarForm">
            <div class="form-group">
                <label>Panel Power (Watts):</label>
                <input type="number" id="panelPower" value="" required>
            </div>
            
            <div class="form-group">
                <label>Panel Voltage (Vmp):</label>
                <input type="number" id="panelVoltage" value="" required>
            </div>

            <div class="form-group">
                <label>Battery Capacity (Ah):</label>
                <input type="number" id="batteryCapacity" value="" required>
            </div>

            <div class="form-group">
                <label>Battery Voltage (V):</label>
                <input type="number" id="batteryVoltage" value="" required>
            </div>

            <div class="form-group">
                <label>Load (Wh):</label>
                <input type="number" id="dailyLoad" value="" required>
            </div>

            <div class="form-group">
                <label>Load Time (H):</label>
                <input type="number" id="loadTime" value="" required>
            </div>

            <div class="form-group">
                <label>Peak Sun Hours / Day:</label>
                <input type="number" id="sunHours" value=4.5 step="0.1" required>
            </div>

            <div class="form-group">
                <label>Charge Controller Type:</label>
                <select id="controllerType">
                    <option value="MPPT">MPPT (High Efficiency)</option>
                    <option value="PWM">PWM (Standard)</option>
                </select>
            </div>

            <div class="form-group">
                <label>Battery Type:</label>
                <select id="batteryType">
                    <option value="Lifepo4">LifePo4 (High Efficiency)</option>
                    <option value="LeadAcid">Lead Acid (Standard)</option>
                </select>
            </div>

            <button type="button" onclick="calculateSolar()">Calculate System</button>
        </form>
        <!-- Results Display -->
        <div class="results-container" id="results" style="display:none;">
            <h3 id="summaryHeading">System Summary</h3>
            <p>Effective Panel Charging Power       :  <span id="outEffectivePower">0</span> W</p>
            <p>Daily Solar Generation               :  <span id="outGeneration">    0</span> Wh/Day</p>
            <p>Total Battery Capacity               :  <span id="outBatteryCap">    0</span> Wh</p>
            <p>Daily Energy Balance                 :  <span id="outBalance">       0</span> Wh</p>
            <p>Total Load                           :  <span id="totalLoad">        0</span> Wh/24H</p>
            <p>Battery Supply in Days               :  <span id="autonomyDays">     0</span></p>
            <p>Time to Charge                       :  <span id="chargeTime">       0</span></p>
            <div id="statusMessage" class="status-box"></div>
        </div>
    </div>

    <script src="script.js"></script>
</body>
</html>
