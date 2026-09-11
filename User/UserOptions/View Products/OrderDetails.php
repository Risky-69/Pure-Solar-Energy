<?php
if (session_status() === PHP_SESSION_NONE) session_start();

// Get order ID from URL query string
$orderId = $_GET['id'] ?? '260906BMJKD16Y';

// TODO: Query database to fetch specific order details using $orderId
// Example database mock data:
$orderStatus = "YOUR ORDER IS ON THE WAY";
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Detail - Pure Solar Energy</title>
    <style>
        :root {
            --bg-main: #070d18;
            --bg-card: #0d1726;
            --border-card: #1b2a40;
            --solar-cyan: #00f2fe;
            --text-primary: #ffffff;
            --text-secondary: #94a3b8;
        }

        body {
            background-color: var(--bg-main);
            color: var(--text-primary);
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            padding: 30px;
            margin: 0;
        }

        .order-detail-container {
            max-width: 900px;
            margin: 0 auto;
            background: var(--bg-card);
            border: 1px solid var(--border-card);
            border-radius: 12px;
            padding: 24px;
        }

        .back-nav {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            color: var(--solar-cyan);
            text-decoration: none;
            font-size: 0.9rem;
            font-weight: 600;
            margin-bottom: 20px;
        }

        .order-meta-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 1px solid var(--border-card);
            padding-bottom: 15px;
            margin-bottom: 30px;
        }

        /* TRACKER STEPPER STYLING */
        .order-tracker {
            display: flex;
            justify-content: space-between;
            align-items: center;
            position: relative;
            margin: 40px 20px;
        }

        .tracker-line {
            position: absolute;
            top: 25px;
            left: 5%;
            width: 90%;
            height: 3px;
            background: var(--border-card);
            z-index: 1;
        }

        .tracker-progress {
            position: absolute;
            top: 25px;
            left: 5%;
            width: 50%; /* Adjust percentage dynamically based on status */
            height: 3px;
            background: var(--solar-cyan);
            z-index: 1;
        }

        .tracker-step {
            position: relative;
            z-index: 2;
            text-align: center;
            flex: 1;
        }

        .step-icon {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            background: var(--bg-main);
            border: 2px solid var(--border-card);
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 10px;
            font-size: 1.2rem;
            color: var(--text-secondary);
            transition: all 0.3s ease;
        }

        .tracker-step.completed .step-icon {
            border-color: var(--solar-cyan);
            background: rgba(0, 242, 254, 0.15);
            color: var(--solar-cyan);
        }

        .step-label {
            font-size: 0.85rem;
            font-weight: 600;
        }

        .step-time {
            font-size: 0.75rem;
            color: var(--text-secondary);
            margin-top: 4px;
        }

        /* ACTIONS PANEL */
        .order-actions {
            display: flex;
            justify-content: flex-end;
            gap: 12px;
            border-top: 1px solid var(--border-card);
            padding-top: 20px;
            margin-top: 30px;
        }

        .btn-action {
            padding: 10px 20px;
            border-radius: 6px;
            font-weight: 600;
            cursor: pointer;
            border: 1px solid var(--border-card);
            background: #111a2e;
            color: var(--text-primary);
        }

        .btn-action.primary {
            background: linear-gradient(90deg, #00f2fe 0%, #4facfe 100%);
            color: #000;
            border: none;
        }
    </style>
</head>
<body>

<div class="order-detail-container">
    <a href="MyAccount.php" class="back-nav">‹ BACK</a>

    <div class="order-meta-header">
        <span>ORDER ID: <strong><?= htmlspecialchars($orderId) ?></strong></span>
        <span style="color: var(--solar-cyan); font-weight: bold;"><?= htmlspecialchars($orderStatus) ?></span>
    </div>

    <!-- SHOPEE-STYLE PROGRESS TRACKER -->
    <div class="order-tracker">
        <div class="tracker-line"></div>
        <div class="tracker-progress"></div>

        <div class="tracker-step completed">
            <div class="step-icon">📋</div>
            <div class="step-label">Order Placed</div>
            <div class="step-time">09/06/2026 21:20</div>
        </div>

        <div class="tracker-step completed">
            <div class="step-icon">💳</div>
            <div class="step-label">Order Paid</div>
            <div class="step-time">09/06/2026 21:28</div>
        </div>

        <div class="tracker-step completed">
            <div class="step-icon">🚚</div>
            <div class="step-label">Shipped Out</div>
            <div class="step-time">09/07/2026 14:05</div>
        </div>

        <div class="tracker-step">
            <div class="step-icon">📥</div>
            <div class="step-label">To Receive</div>
            <div class="step-time">--</div>
        </div>

        <div class="tracker-step">
            <div class="step-icon">⭐</div>
            <div class="step-label">To Rate</div>
            <div class="step-time">--</div>
        </div>
    </div>

    <!-- ACTION BUTTONS -->
    <div class="order-actions">
        <button class="btn-action">Contact Seller</button>
        <button class="btn-action primary">Order Received</button>
    </div>
</div>

</body>
</html>