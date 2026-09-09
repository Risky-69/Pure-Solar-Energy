

function searchCatalog() {
  const query = document.getElementById('catalog-search').value.toLowerCase();
  const currentTitle = document.getElementById('view-title').innerText;
  let currentDataset = [];

  // 1. Identify which tab's dataset we are currently searching through
  if (currentTitle === "All Products Catalog") {
    currentDataset = getAllProducts();
  } else {
    const currentCategory = currentTitle.replace(' Catalog', '');
    currentDataset = database[currentCategory] || [];
  }

  // 2. Clear any active "Filter by" tags since search takes precedence
  document.querySelectorAll('.filter-link').forEach(link => link.classList.remove('active'));
  const allTypesLink = document.querySelector('.filter-link[onclick*="All"]');
  if (allTypesLink) allTypesLink.classList.add('active');

  // 3. Filter products by matching the name parameter string values
  const filteredResults = currentDataset.filter(product => 
    product.name.toLowerCase().includes(query) || 
    product.type.toLowerCase().includes(query)
  );

    renderGrid(filteredResults);
}


const database = {
  "Solar Panels": [
    { name: "SunPower Maxeon 6 (400W)", type: "Monocrystalline", eff: "22.8%", voc: "45.2V", price: "$330" },
    { name: "Canadian Solar HiKu (450W)", type: "Monocrystalline", eff: "20.9%", voc: "49.4V", price: "$280" },
    { name: "Jinko Solar Tiger (330W)", type: "Polycrystalline", eff: "17.5%", voc: "43.1V", price: "$210" },
    { name: "Flexible Sun-Flex (100W)", type: "Thin-Film", eff: "14.0%", voc: "21.0V", price: "$190" }
  ],
  "Inverters": [
    { name: "Enphase IQ8 Plus Microinverter", type: "Microinverter", eff: "97.5%", voc: "60V Max", price: "$185" },
    { name: "Fronius Primo 5.0-1", type: "String Inverter", eff: "97.8%", voc: "1000V Max", price: "$1,650" }
  ],
  "Batteries": [
    { name: "Tesla Powerwall 2", type: "Lithium-Ion", eff: "90.0%", voc: "50V Nom", price: "$7,500" },
    { name: "Enphase IQ Battery 10T", type: "LFP", eff: "89.0%", voc: "67V Nom", price: "$9,200" }
  ],
  "Charge Controllers": [
    { name: "Victron SmartSolar MPPT", type: "MPPT", eff: "98.0%", voc: "150V Max", price: "$220" }
  ]
};

// HELPER FUNCTION TO GET ALL PRODUCTS COMBINED
function getAllProducts() {
  return [
    ...database["Solar Panels"],
    ...database["Inverters"],
    ...database["Batteries"],
    ...database["Charge Controllers"]
  ];
}



// INTERACTIVE SIDEBAR TAB CONTROL
function switchTab(button, category) {
  // Remove active glow from all buttons
  document.querySelectorAll('.nav-btn').forEach(btn => btn.classList.remove('active'));
  
  // Set glow to clicked button
  button.classList.add('active');
  
  const filterBar = document.getElementById('catalog-filters');
  const grid = document.getElementById('catalog-grid');
  if (!grid) return;

  // Reset the horizontal filter styling links back to "All Types" whenever switching main categories
  document.querySelectorAll('.filter-link').forEach(link => link.classList.remove('active'));
  const allTypesLink = document.querySelector('.filter-link[onclick*="All"]');
  if (allTypesLink) allTypesLink.classList.add('active');

  // Handle Home screen viewing combination logic
  if (category === 'Home') {
    document.getElementById('view-title').innerText = "All Products Catalog";
    if (filterBar) filterBar.style.display = 'flex';
    renderGrid(getAllProducts());
  } 
  // Handle specific product category views
  else if (database[category]) {
    document.getElementById('view-title').innerText = `${category} Catalog`;
    if (filterBar) filterBar.style.display = 'flex';
    renderGrid(database[category]);
  } 
  // Handle interactive non-catalog view frames
  else {
    if (filterBar) filterBar.style.display = 'none';
    document.getElementById('view-title').innerText = category;
    
    if (category === 'Contact Us') {
      grid.innerHTML = `
        <div style="grid-column: 1/-1; padding: 40px; background: #112d3d; border-radius: 8px; max-width: 500px;">
          <h3 style="margin-bottom: 20px;">Submit Project Request</h3>
          <div style="display: flex; flex-direction: column; gap: 15px;">
            <input type="text" placeholder="Your Name" style="padding: 12px; background: #0b1a24; border: 1px solid rgba(255,255,255,0.1); color: white; border-radius: 4px;">
            <input type="email" placeholder="Your Email Address" style="padding: 12px; background: #0b1a24; border: 1px solid rgba(255,255,255,0.1); color: white; border-radius: 4px;">
            <textarea placeholder="Message Description" rows="4" style="padding: 12px; background: #0b1a24; border: 1px solid rgba(255,255,255,0.1); color: white; border-radius: 4px; resize: none;"></textarea>
            <button class="btn-quote" style="width: 100%; padding: 12px;" onclick="alert('Message Sent!')">Send Message</button>
          </div>
        </div>`;
    } else if (category === 'System Builder') {
      grid.innerHTML = `
        <div style="grid-column: 1/-1; padding: 40px; background: #112d3d; border-radius: 8px; max-width: 500px;">
          <h3 style="margin-bottom: 15px;">Estimated Daily Usage Calculator</h3>
          <div style="display: flex; flex-direction: column; gap: 15px;">
            <label style="font-size: 13px; color: var(--text-muted);">Target Load (kWh per Day):</label>
            <input type="number" id="calc-load" value="15" style="padding: 12px; background: #0b1a24; border: 1px solid rgba(255,255,255,0.1); color: white; border-radius: 4px;">
            <button class="btn-spec" style="padding: 12px;" onclick="const val = document.getElementById('calc-load').value; alert('Recommended Configuration: ' + Math.ceil((val * 1000) / (400 * 4)) + ' SunPower Maxeon 6 Modules.')">Calculate System Requirements</button>
          </div>
        </div>`;
    }
  }
}

// LIST RENDERING MODULE
function renderGrid(products) {
  const grid = document.getElementById('catalog-grid');
  if (!grid) return;
  
  if (products.length === 0) {
    grid.innerHTML = `<div style="grid-column: 1/-1; text-align: center; color: var(--text-muted); padding: 40px;">No items match this selection filters.</div>`;
    return;
  }

  grid.innerHTML = products.map((p) => `
    <div class="product-card">
      <div class="image-placeholder"></div>
      <div class="product-info">
        <h3>${p.name}</h3>
        <div class="product-specs">
          Type: ${p.type} | Efficiency: ${p.eff} | Voc: ${p.voc} | Price: ${p.price}
        </div>
      </div>
      <div class="action-group">
        <button class="btn-spec" onclick="openSpec('${p.name}', '${p.type}', '${p.eff}', '${p.voc}', '${p.price}')">View Specs</button>
        <button class="btn-quote" onclick="triggerToast()">+ Add To Quote</button>
      </div>
    </div>
  `).join('');
}

// TOP BAR LIVE FILTERING LOGIC (Fixed to avoid clearing out other category catalogs)
function filterCatalog(type) {
  document.querySelectorAll('.filter-link').forEach(link => link.classList.remove('active'));
  if (event && event.target) {
    event.target.classList.add('active');
  }

  const currentTitle = document.getElementById('view-title').innerText;
  let data = [];

  // 1. Figure out our active working category dataset
  if (currentTitle === "All Products Catalog") {
    data = getAllProducts();
  } else {
    const currentCategory = currentTitle.replace(' Catalog', '');
    data = database[currentCategory] || [];
  }

  // 2. Safely apply filtering parameters without causing blank errors
  if (type === 'All') {
    renderGrid(data);
  } else {
    const filtered = data.filter(p => p.type.toLowerCase() === type.toLowerCase());
    renderGrid(filtered);
  }
}

// MODAL WINDOW INTERACTION WRAPPERS
function openSpec(name, type, eff, voc, price) {
  document.getElementById('modal-title').innerText = name;
  document.getElementById('modal-body').innerHTML = `
    <p><strong>Equipment Architecture:</strong> ${type} Cell Grid Structure</p>
    <p><strong>Module Operational Efficiency:</strong> ${eff}</p>
    <p><strong>Open-Circuit Voltage (Voc):</strong> ${voc}</p>
    <p><strong>MSRP Price Point Baseline:</strong> ${price}</p>
  `;
  document.getElementById('spec-modal').classList.add('active');
}

function closeModal() {
  document.getElementById('spec-modal').classList.remove('active');
}

// TOAST NOTIFICATION HANDLER
function triggerToast() {
  const toast = document.getElementById('toast-banner');
  if (toast) {
    toast.classList.add('show');
    setTimeout(() => toast.classList.remove('show'), 2500);
  }
}

// INITIAL LAUNCH STATE (Loads all combined items on launch)
window.addEventListener('DOMContentLoaded', () => {
  renderGrid(getAllProducts());
});

