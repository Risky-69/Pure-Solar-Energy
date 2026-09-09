function calculateSolar() {
    // 1. Get input values
    const panelPower        = parseFloat(document.getElementById('panelPower').value);
    const panelVoltage      = parseFloat(document.getElementById('panelVoltage').value);
    const batteryCapacity   = parseFloat(document.getElementById('batteryCapacity').value);
    const batteryVoltage    = parseFloat(document.getElementById('batteryVoltage').value);
    const dailyLoad         = parseFloat(document.getElementById('dailyLoad').value);
    const sunHours          = parseFloat(document.getElementById('sunHours').value);
    const loadTime          = parseFloat(document.getElementById('loadTime').value);
    const controllerType    = document.getElementById('controllerType').value;
    const batteryType       = document.getElementById('batteryType').value;
  

    // Validation check
    if (panelVoltage <= batteryVoltage && controllerType === 'PWM') {
        alert("Warning: For a PWM controller, panel voltage must be higher than battery voltage to charge.");
        return;
    }

    // 2. Determine Effective Panel Power based on Controller Type
    let effectivePower = panelPower;
    if (controllerType === 'PWM') {
        // PWM clips panel voltage down to battery voltage level
        effectivePower = panelPower * (batteryVoltage / panelVoltage);
    } else {
        // MPPT converts voltage efficiently (assume ~95% transfer efficiency)
        effectivePower = panelPower * 0.95;
    }
    

    // 2. Determine Effective Panel Power based on Controller Type
    let BatteryType = batteryType;
    if (batteryType === 'LifePo4') {
        // lifePo4 80% usable battery
        BatteryType = batteryCapacity * 0.80;
    } else {
        // Lead Acid 60% usable battery
        BatteryType = batteryCapacity * 0.60;
    }
    


        // 3. Compute Solar Generation and Battery Capacity
        const dailyGenerationWh = effectivePower * sunHours; // solar panel
        const totalBatteryWh = batteryCapacity * BatteryType; // battery
        const totalLoad = dailyLoad * loadTime;
        const netBalanceWh = dailyGenerationWh - totalLoad;
        const daysOfAutonomy = totalBatteryWh / totalLoad;
        const chargeTime = totalBatteryWh / effectivePower;

        // 4. Update the UI Text fields
        document.getElementById('outEffectivePower').innerText = Math.round(effectivePower);
        document.getElementById('outGeneration').innerText = Math.round(dailyGenerationWh);
        document.getElementById('outBatteryCap').innerText = Math.round(totalBatteryWh);
        document.getElementById('outBalance').innerText = Math.round(netBalanceWh);
        document.getElementById('totalLoad').innerText = Math.round(totalLoad);
        document.getElementById('autonomyDays').innerText = daysOfAutonomy.toFixed(1) + " Days";

        // 5. Update Status Message styling
        const statusBox = document.getElementById('statusMessage');
        if (netBalanceWh >= 0) {
            statusBox.innerText = "System is Sustainable! Your panels generate enough daily power.";
            statusBox.className = "status-box status-success";
        } else {
            statusBox.innerText = "System Deficit! Your load exceeds daily solar production. Expand panels or reduce load.";
            statusBox.className = "status-box status-danger";
        }

        // 6. Compute and Inject Charge Time Text
        let chargeTimeText = "0 Hours";
        if (effectivePower > 0) {
            const chargeTimeHours = totalBatteryWh / effectivePower;
            chargeTimeText = chargeTimeHours.toFixed(1) + " Hours";
        } else {
            chargeTimeText = "No Solar Input";
        }
        
        // FIX: THIS LINE WAS MISSING! This pushes the value into your HTML container
        document.getElementById('chargeTime').innerText = chargeTimeText;

        // 7. Make visible and execute your smooth scroll setup
        document.getElementById('results').style.display = 'block';
        setTimeout(function() {
            document.getElementById('summaryHeading').scrollIntoView({ 
                behavior: 'smooth', 
                block: 'start' 
            });
        }, 50);
    } // This closes your calculateSolar() function
