async function loadSolarData() {
  try {
    const response = await fetch('http://localhost:127.0.0.1/puresolarenergy');
    const data = await response.json();
    
    console.log('Database records received:', data);
    // You can now display this data on your HTML page!
  } catch (error) {
    console.error('Error fetching solar data:', error);
  }
}

loadSolarData();

<button class="btn-journey" onclick="document.getElementById('footer-section').scrollIntoView({ behavior: 'smooth' });">
    START YOUR SOLAR JOURNEY
</button>

// Initialization Event Listener
document.addEventListener("DOMContentLoaded", () => {
    console.log("PureSolar Energy Platform Module Initialized.");
    setupInverterInteractions();
    loadSolarData();
});


// Primary CTA Callout action
function initiateSolarAssessment() {
    alert("Thank you for your interest! Connecting you with a PureSolar specialist to custom fit your panel setup layout.");
}

// Battery Tech Choice logger
function selectBattery(techType) {
    console.log(`User preferred technology layer: ${techType}`);
    alert(`You selected the ${techType} system template layout options. Perfect for reliable load balance management.`);
}

// Inverter Showcase Card interaction logic
function setupInverterInteractions() {
    const inverterCards = document.querySelectorAll(".inverter-item");
    
    inverterCards.forEach(card => {
        card.style.cursor = "pointer";
        card.addEventListener("click", () => {
            const brand = card.getAttribute("data-brand");
            alert(`Inverter Configuration Option Selected: ${brand}. This unit optimizes conversion to grid-ready AC currents.`);
        });
    });
}

// Auth routing control layer 
function handleAuth(flowType) {
    if (flowType === 'signin') {
        console.log("Routing destination: Secure login system.");
        alert("Opening the customer login portal panel screen.");
    } else if (flowType === 'signup') {
        console.log("Routing destination: Registration workflow.");
        alert("Initializing new user profile creation setup framework.");
    }
}

function selectOption(techType) {
    console.log(`User preferred technology layer: ${techType}`);
    alert(`You selected the ${techType} system template layout options. Perfect for reliable load balance management.`);
}

function openAuthModal(mode) {
    const modal = document.getElementById('authModal');
    const title = document.getElementById('modalTitle');
    const subtitle = document.getElementById('modalSubtitle');
    const submitBtn = document.getElementById('modalSubmitBtn');

    if (modal && title && subtitle && submitBtn) {
        if (mode === 'signup') {
            title.textContent = 'Sign Up';
            subtitle.textContent = 'Access your PureSolar energy dashboard account.';
            submitBtn.textContent = 'Proceed';
        } else {
            title.textContent = 'Log In';
            subtitle.textContent = 'Log in to your PureSolar account.';
            submitBtn.textContent = 'Log In';
        }

        modal.classList.add('active');
    }
}

// Modal Toggle Functions for Cart Authentication
function showLoginModal() {
    const modal = document.getElementById('loginModal');
    if (modal) {
        modal.classList.add('active');
    } else {
        console.error("Element with ID 'loginModal' not found.");
    }
}

function closeLoginModal() {
    const modal = document.getElementById('loginModal');
    if (modal) {
        modal.classList.remove('active');
    }
}

function addToCart(productId) {
    console.log("Adding product " + productId + " to cart.");
}

// Close modal when clicking outside the content box
window.addEventListener("click", (event) => {
    const modal = document.getElementById('loginModal');
    if (event.target === modal) {
        closeLoginModal();
    }
});

// Close modal when clicking outside the content box
window.onclick = function(event) {
    const modal = document.getElementById('loginModal');
    if (event.target === modal) {
        closeLoginModal();
    }
};

function togglePasswordVisibility(fieldId, btnElement) {
    const field = document.getElementById(fieldId);
    if (!field) return;

    if (field.type === 'password') {
        field.type = 'text';
        btnElement.textContent = '🙈';
    } else {
        field.type = 'password';
        btnElement.textContent = '👁️';
    }
}