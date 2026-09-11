<footer class="site-footer">
  <style>
    /* Global CSS Variable Fallbacks */
    :root {
      --bg-sidebar: #080e1e;
      --bg-card: #0f172a;
      --border-card: #1e293b;
      --text-primary: #ffffff;
      --text-secondary: #94a3b8;
      --accent-cyan: #00f2fe;
      --accent-yellow: #f59e0b;
      --accent-blue: #38bdf8;
    }

    /* Site Footer Structure */
    .site-footer {
      background-color: var(--bg-sidebar, #080e1e);
      color: var(--text-primary, #ffffff);
      padding: 40px 40px 20px;
      border-top: 1px solid var(--border-card, #1e293b);
      box-sizing: border-box;
    }

    .footer-container {
      max-width: 1200px;
      margin: 0 auto;
      display: grid;
      grid-template-columns: 1.5fr 1fr 1fr 1.5fr;  
      gap: 40px;
      align-items: start;
    }
    
    .footer-col h3 {
      font-size: 1.15rem;
      font-weight: 700;
      margin-bottom: 12px;
      color: var(--accent-cyan, #00f2fe);
      letter-spacing: 0.5px;
    }

    .footer-col h4 {
      font-size: 1.05rem;
      font-weight: 700;
      margin-bottom: 15px;
      color: var(--text-primary, #ffffff);
    }
    
    .footer-col ul {
      list-style: none;
      padding: 0;
      margin: 0;
    }
    
    .footer-col ul li {
      margin-bottom: 10px;
    }
    
    .footer-col a {
      color: var(--text-secondary, #94a3b8);
      text-decoration: none;
      font-size: 0.9rem;
      transition: color 0.2s ease, padding-left 0.2s ease;
    }
    
    .footer-col a:hover {
      color: var(--accent-cyan, #00f2fe);
      padding-left: 4px;
    }
    
    /* Promo Text & Cards */
    .promo-text {
      font-size: 0.88rem;
      line-height: 1.5;
      margin-bottom: 15px;
      color: var(--text-secondary, #94a3b8);
    }

    .rewards-card {
      background: var(--bg-card, #0f172a);
      color: var(--accent-yellow, #f59e0b);
      border: 1px solid var(--border-card, #1e293b);
      padding: 18px;
      border-radius: 8px;
      text-align: center;
      font-weight: 800;
      font-size: 1.3rem;
      margin-bottom: 15px;
      box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
      display: flex;
      flex-direction: column;
      justify-content: center;
    }

    .rewards-card small {
      font-size: 0.75rem;
      color: var(--text-secondary, #94a3b8);
      letter-spacing: 1.5px;
      margin-top: 4px;
    }

    .app-buttons {
      display: flex;
      gap: 10px;
    }

    .app-badge {
      background: var(--bg-card, #0f172a);
      border: 1px solid var(--border-card, #1e293b);
      color: var(--text-primary, #ffffff) !important;
      padding: 8px 12px;
      border-radius: 6px;
      display: flex;
      flex-direction: column;
      font-size: 0.65rem;
      text-decoration: none !important;
      min-width: 100px;
      transition: border-color 0.2s ease, transform 0.2s ease;
    }

    .app-badge:hover {
      border-color: var(--accent-blue, #38bdf8);
      transform: translateY(-2px);
    }

    .app-badge strong {
      font-size: 0.85rem;
      color: var(--accent-cyan, #00f2fe);
    }

    /* Payment Badges */
    .payment-badges {
      display: grid;
      grid-template-columns: repeat(3, 1fr);
      gap: 8px;
      margin-bottom: 20px;
    }

    .payment-badges .badge {
      background: var(--bg-card, #0f172a);
      border: 1px solid var(--border-card, #1e293b);
      color: var(--text-secondary, #94a3b8);
      padding: 8px 4px;
      border-radius: 6px;
      font-size: 0.75rem;
      font-weight: 700;
      text-align: center;
    }

    /* Social Icons */
    .social-heading {
      margin-top: 15px;
      margin-bottom: 12px;
    }

    .social-icons {
      display: flex;
      gap: 12px;
    }

    .social-icons a {
      background: var(--bg-card, #0f172a);
      border: 1px solid var(--border-card, #1e293b);
      color: var(--accent-cyan, #00f2fe);
      width: 36px;
      height: 36px;
      border-radius: 50%;
      display: flex;
      align-items: center;
      justify-content: center;
      font-weight: bold;
      text-decoration: none !important;
      transition: background-color 0.2s ease, border-color 0.2s ease, transform 0.2s ease;
    }

    .social-icons a:hover {
      background: var(--border-card, #1e293b);
      border-color: var(--accent-cyan, #00f2fe);
      transform: translateY(-2px);
    }

    .footer-divider {
      border: 0;
      border-top: 1px solid var(--border-card, #1e293b);
      margin: 40px auto 20px;
      max-width: 1200px;
    }

    .footer-bottom {
      text-align: center;
      font-size: 0.85rem;
      color: var(--text-secondary, #94a3b8);
    }

    /* Responsive */
    @media (max-width: 900px) {
      .footer-container {
        grid-template-columns: 1fr 1fr;
      }
    }

    @media (max-width: 600px) {
      .site-footer {
        padding: 40px 20px 20px;
      }
      .footer-container {
        grid-template-columns: 1fr;
        gap: 30px;
      }
    }
  </style>

  <div class="footer-container">
    <!-- Column 1 -->
    <div class="footer-col col-promo">
      <h3>Earn Rewards, Save More.</h3>
      <p class="promo-text">Join Pure Solar Rewards and enjoy exclusive deals, points, and member perks.</p>
      
      <div class="rewards-card">
        <span>PURE SOLAR</span>
        <small>REWARDS</small>
      </div>

      <div class="app-buttons">
        <a href="#" class="app-badge google-play">
          <span>GET IT ON</span>
          <strong>Google Play</strong>
        </a>
        <a href="#" class="app-badge app-store">
          <span>Download on the</span>
          <strong>App Store</strong>
        </a>
      </div>
    </div>

    <!-- Column 2 -->
    <div class="footer-col">
      <h4>Get to Know Us</h4>
      <ul>
        <li><a href="#about">About Us</a></li>
        <li><a href="#careers">Careers</a></li>
        <li><a href="#store">Find Our Store</a></li>
        <li><a href="#contact">Contact Us</a></li>
      </ul>
    </div>

    <!-- Column 3 -->
    <div class="footer-col">
      <h4>Support</h4>
      <ul>
        <li><a href="#terms">Terms and Conditions</a></li>
        <li><a href="#privacy">Privacy Policy</a></li>
        <li><a href="#faq">FAQ & Help</a></li>
      </ul>
    </div>

    <!-- Column 4 -->
    <div class="footer-col col-payments">
      <h4>We Accept:</h4>
      <div class="payment-badges">
        <span class="badge">MasterCard</span>
        <span class="badge">VISA</span>
        <span class="badge">BancNet</span>
        <span class="badge">GCash</span>
        <span class="badge">Maya</span>
        <span class="badge">Billease</span>
      </div>

      <h4 class="social-heading">Stay Connected with Us:</h4>
      <div class="social-icons">
        <a href="#" aria-label="Facebook">f</a>
        <a href="#" aria-label="TikTok">♪</a>
        <a href="#" aria-label="Instagram">📷</a>
        <a href="#" aria-label="LinkedIn">in</a>
      </div>
    </div>
  </div>

  <hr class="footer-divider">

  <div class="footer-bottom">
    <p>Copyright &copy; <?php echo date('Y'); ?> Pure Power Systems. All Rights Reserved</p>
  </div>
</footer> 