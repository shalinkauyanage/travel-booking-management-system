<?php
// packages.php - GlobeTrek Adventures Tour Packages Page
// No database connection required for this page
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8"/>
<meta name="viewport" content="width=device-width, initial-scale=1.0"/>
<title>Tour Packages – GlobeTrek Adventures</title>
<link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700;900&family=DM+Sans:wght@300;400;500;600;700&display=swap" rel="stylesheet"/>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
<style>
    :root {
      --navy: #0a1628;
      --ocean: #0e3a6e;
      --sky: #1a6fb5;
      --azure: #2196f3;
      --aqua: #00b4d8;
      --white: #ffffff;
      --cream: #f8f4ef;
      --sand: #f0e6d3;
      --orange: #ff6b2b;
      --orange-dark: #e85a1b;
      --text-dark: #0a1628;
      --text-mid: #3a4a5c;
      --text-light: #7a8a9a;
      --card-shadow: 0 20px 40px rgba(10,22,40,0.08);
      --transition: all 0.3s cubic-bezier(0.25, 0.46, 0.45, 0.94);
    }
    
    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
    }

    body {
      font-family: 'DM Sans', sans-serif;
      line-height: 1.5;
      color: var(--text-dark);
      background-color: var(--white);
    }

    /* NAVIGATION */
    nav {
      position: fixed;
      top: 0; left: 0; right: 0;
      z-index: 100;
      display: flex;
      align-items: center;
      justify-content: space-between;
      padding: 18px 60px;
      background: rgba(10,22,40,0.95);
      backdrop-filter: blur(12px);
      border-bottom: 1px solid rgba(255,255,255,0.08);
      transition: var(--transition);
    }

    .logo {
      text-decoration: none;
      display: flex;
      align-items: center;
    }

    .logo-text {
      font-family: 'Playfair Display', serif;
      font-size: 1.5rem;
      font-weight: 800;
      color: var(--white);
      letter-spacing: -0.02em;
    }
    .logo-text span { color: var(--aqua); }

    .nav-cta {
      display: flex; gap: 12px;
    }
    .btn-outline-nav {
      padding: 8px 20px;
      border: 1.5px solid rgba(255,255,255,0.3);
      border-radius: 8px;
      color: var(--white);
      text-decoration: none;
      font-size: 0.875rem;
      font-weight: 500;
      transition: var(--transition);
    }
    .btn-outline-nav:hover { border-color: var(--aqua); color: var(--aqua); background: rgba(0,180,216,0.05); }
    .btn-fill-nav {
      padding: 8px 24px;
      background: var(--orange);
      border-radius: 8px;
      color: var(--white);
      text-decoration: none;
      font-size: 0.875rem;
      font-weight: 600;
      transition: var(--transition);
    }
    .btn-fill-nav:hover { background: var(--orange-dark); transform: translateY(-2px); }

    

    .section {
  padding: 140px 60px 80px;
  background: var(--white);
}

    .section-label {
      display: inline-flex;
      align-items: center;
      gap: 8px;
      font-size: 0.75rem;
      font-weight: 700;
      letter-spacing: 0.15em;
      text-transform: uppercase;
      color: var(--sky);
      margin-bottom: 16px;
    }
    .section-label::before { 
      content: ''; 
      width: 30px; 
      height: 2px; 
      background: var(--sky); 
      display: inline-block; 
    }

    .section-title {
      font-family: 'Playfair Display', serif;
      font-size: clamp(1.8rem, 3.5vw, 2.8rem);
      font-weight: 700;
      color: var(--navy);
      line-height: 1.2;
      letter-spacing: -0.02em;
      margin-bottom: 16px;
    }

    .section-sub {
      color: var(--text-mid);
      font-size: 1rem;
      max-width: 600px;
      line-height: 1.6;
    }

    .section-center {
      text-align: center;
      max-width: 700px;
      margin: 0 auto 40px;
    }

    /* SEARCH BAR */
    .search-bar-wrapper {
      max-width: 500px;
      margin: 0 auto 40px;
    }
    .search-bar {
      display: flex;
      gap: 12px;
      background: var(--white);
      border: 1px solid #e0e6ed;
      border-radius: 60px;
      padding: 6px 6px 6px 20px;
      box-shadow: 0 4px 12px rgba(0,0,0,0.04);
    }
    .search-bar input {
      flex: 1;
      border: none;
      padding: 12px 0;
      font-size: 0.95rem;
      font-family: 'DM Sans', sans-serif;
      outline: none;
      background: transparent;
    }
    .search-bar button {
      background: var(--orange);
      border: none;
      border-radius: 40px;
      padding: 8px 24px;
      color: var(--white);
      font-weight: 600;
      font-family: 'DM Sans', sans-serif;
      cursor: pointer;
      transition: var(--transition);
    }
    .search-bar button:hover {
      background: var(--orange-dark);
      transform: translateY(-2px);
    }

 

    /* PACKAGES GRID */
    .cards-grid {
      display: grid;
      grid-template-columns: repeat(3, 1fr);
      gap: 30px;
    }

    .pkg-card {
      background: var(--white);
      border-radius: 20px;
      overflow: hidden;
      box-shadow: var(--card-shadow);
      transition: var(--transition);
      cursor: pointer;
      border: 1px solid rgba(0,0,0,0.04);
    }
    .pkg-card:hover { 
      transform: translateY(-8px); 
      box-shadow: 0 30px 50px rgba(10,22,40,0.12); 
    }

    .card-img {
      position: relative;
      height: 220px;
      overflow: hidden;
    }
    .card-img img {
      width: 100%; height: 100%;
      object-fit: cover;
      transition: transform 0.5s;
    }
    .pkg-card:hover .card-img img { transform: scale(1.05); }

    .card-body { padding: 24px; }

    .card-title {
      font-family: 'Playfair Display', serif;
      font-size: 1.25rem;
      font-weight: 700;
      color: var(--navy);
      margin-bottom: 8px;
      line-height: 1.3;
    }
    
    .card-meta {
      color: var(--text-light);
      font-size: 0.85rem;
      margin-bottom: 16px;
    }
    .card-meta i { margin-right: 4px; }
    
    .card-footer {
      display: flex;
      align-items: center;
      justify-content: space-between;
      padding-top: 20px;
      border-top: 1px solid #eef2f7;
    }

    .card-price {
      font-family: 'Playfair Display', serif;
      font-size: 1.5rem;
      font-weight: 700;
      color: var(--navy);
    }
    .card-price sup {
      font-size: 0.7rem;
      font-weight: 500;
      color: var(--text-mid);
    }
    .card-price span {
      font-size: 0.75rem;
      color: var(--text-light);
    }

    .btn-book {
      padding: 8px 20px;
      background: linear-gradient(135deg, var(--azure), var(--sky));
      color: var(--white);
      border: none;
      border-radius: 10px;
      font-family: 'DM Sans', sans-serif;
      font-size: 0.85rem;
      font-weight: 600;
      cursor: pointer;
      transition: var(--transition);
    }
    .btn-book:hover { 
      transform: translateY(-2px); 
      box-shadow: 0 5px 15px rgba(33,150,243,0.3); 
    }

    /* NO RESULTS */
    .no-results {
      text-align: center;
      padding: 60px;
      color: var(--text-mid);
      font-size: 1.1rem;
      grid-column: 1 / -1;
    }

    /* NEWSLETTER SECTION */
    .newsletter-section {
      background: linear-gradient(135deg, var(--ocean) 0%, var(--navy) 100%);
      text-align: center;
      padding: 80px 60px;
      position: relative;
    }
    .newsletter-section .section-title { color: var(--white); }
    .newsletter-section .section-sub { color: rgba(255,255,255,0.7); margin: 0 auto 40px; }

    .newsletter-form {
      display: flex;
      gap: 12px;
      max-width: 500px;
      margin: 0 auto;
    }
    .newsletter-form input {
      flex: 1;
      padding: 14px 20px;
      border: none;
      border-radius: 12px;
      font-family: 'DM Sans', sans-serif;
      font-size: 0.95rem;
      outline: none;
    }
    .newsletter-form button {
      padding: 14px 28px;
      background: var(--orange);
      color: var(--white);
      border: none;
      border-radius: 12px;
      font-family: 'DM Sans', sans-serif;
      font-size: 0.95rem;
      font-weight: 600;
      cursor: pointer;
      transition: var(--transition);
    }
    .newsletter-form button:hover { background: var(--orange-dark); transform: translateY(-2px); }

    /* FOOTER */
    footer {
      background: var(--navy);
      padding: 30px 20px;
      text-align: center;
    }
    .footer-bottom {
      border-top: 1px solid rgba(255,255,255,0.08);
      padding-top: 24px;
      font-size: 0.8rem;
      color: rgba(255,255,255,0.4);
    }

    /* MODAL */
    .modal-overlay {
      display: none;
      position: fixed;
      inset: 0;
      z-index: 1000;
      background: rgba(10,22,40,0.8);
      backdrop-filter: blur(6px);
      align-items: center;
      justify-content: center;
    }
    .modal-overlay.open { display: flex; }

    .modal {
      background: var(--white);
      border-radius: 24px;
      padding: 40px;
      max-width: 440px;
      width: 90%;
      box-shadow: 0 40px 100px rgba(0,0,0,0.3);
      position: relative;
      animation: scaleIn 0.3s ease;
    }
    .modal-close {
      position: absolute;
      top: 16px; right: 16px;
      width: 32px; height: 32px;
      border-radius: 50%;
      background: var(--cream);
      border: none;
      cursor: pointer;
      font-size: 1.1rem;
      transition: var(--transition);
    }
    .modal-close:hover { background: var(--sand); }
    .modal h2 {
      font-family: 'Playfair Display', serif;
      font-size: 1.6rem;
      color: var(--navy);
      margin-bottom: 12px;
    }
    .modal p { color: var(--text-mid); margin-bottom: 24px; }
    .modal-btns { display: flex; gap: 12px; }
    .modal-btns a {
      flex: 1;
      padding: 12px;
      border-radius: 12px;
      text-align: center;
      font-weight: 600;
      text-decoration: none;
      transition: var(--transition);
    }
    .modal-btn-login { background: var(--navy); color: var(--white); }
    .modal-btn-login:hover { background: var(--ocean); }
    .modal-btn-register { background: var(--orange); color: var(--white); }
    .modal-btn-register:hover { background: var(--orange-dark); }

    /* BACK TO TOP */
    .back-to-top {
      position: fixed;
      bottom: 30px;
      right: 30px;
      width: 45px;
      height: 45px;
      background: var(--orange);
      border-radius: 50%;
      display: flex;
      align-items: center;
      justify-content: center;
      color: var(--white);
      cursor: pointer;
      opacity: 0;
      visibility: hidden;
      transition: var(--transition);
      z-index: 99;
      box-shadow: 0 4px 12px rgba(0,0,0,0.15);
    }
    .back-to-top.show {
      opacity: 1;
      visibility: visible;
    }
    .back-to-top:hover {
      background: var(--orange-dark);
      transform: translateY(-3px);
    }

    @keyframes scaleIn {
      from { opacity: 0; transform: scale(0.95); }
      to { opacity: 1; transform: scale(1); }
    }

    /* RESPONSIVE */
    @media (max-width: 1100px) {
      .cards-grid { grid-template-columns: repeat(2, 1fr); }
    }
    @media (max-width: 900px) {
      .section, .newsletter-section { padding: 60px 30px; }
      nav { padding: 15px 25px; }
      .nav-links { display: none; }
    }
    @media (max-width: 700px) {
      .cards-grid { grid-template-columns: 1fr; }
      .newsletter-form { flex-direction: column; }
      .filter-tabs { gap: 8px; }
      .filter-btn { padding: 6px 14px; font-size: 0.75rem; }
    }
</style>
</head>
<body>

<!-- NAVIGATION -->
<nav>
  <a href="index.php" class="logo">
    <span class="logo-text">Globe<span>Trek</span></span>
  </a>
  
  <div class="nav-cta">
    <a href="login.html" class="btn-outline-nav">Login</a>
    <a href="register.html" class="btn-fill-nav">Register</a>
  </div>
</nav>

<!-- PACKAGES SECTION -->
<section class="section" id="packages">
  <div class="section-center">
    
    <h2 class="section-title">Find Your Perfect Adventure</h2>
    <p class="section-sub">From cultural heritage to beach escapes — choose from our wide range of tour packages</p>
  </div>

  <!-- SEARCH BAR -->
  <div class="search-bar-wrapper">
    <div class="search-bar">
      <input type="text" id="searchInput" placeholder="Search packages by name or destination..." onkeyup="filterPackages()">
      <button onclick="filterPackages()">Search</button>
    </div>
  </div>

  <!-- PACKAGES GRID -->
  <div class="cards-grid" id="packagesGrid">
    <!-- Package 1: Sigiriya -->
    <div class="pkg-card" data-name="sigiriya rock fortress cultural tour" data-category="cultural">
      <div class="card-img">
        <img src="img/Sigiriya Rock.jpg">
      </div>
      <div class="card-body">
        <div class="card-title">Sigiriya Rock Fortress & Cultural Tour</div>
        <div class="card-meta"><i class="far fa-calendar-alt"></i> 3 Days | <i class="fas fa-clock"></i> Guided Tour</div>
        <div class="card-footer">
          <div class="card-price"><sup>LKR</sup>35,500 <span>/ person</span></div>
          <button class="btn-book" onclick="requireLogin('Sigiriya Rock Fortress & Cultural Tour', 35500)">Book Now</button>
        </div>
      </div>
    </div>

    <!-- Package 2: Ella -->
    <div class="pkg-card" data-name="ella scenic escape train mountains" data-category="nature">
      <div class="card-img">
        <img src="img/Ella.jpg">
      </div>
      <div class="card-body">
        <div class="card-title">Ella Scenic Escape – Train & Mountains</div>
        <div class="card-meta"><i class="far fa-calendar-alt"></i> 4 Days | <i class="fas fa-train"></i> Train Journey</div>
        <div class="card-footer">
          <div class="card-price"><sup>LKR</sup>42,900 <span>/ person</span></div>
          <button class="btn-book" onclick="requireLogin('Ella Scenic Escape – Train & Mountains', 42900)">Book Now</button>
        </div>
      </div>
    </div>

    <!-- Package 3: Mirissa -->
    <div class="pkg-card" data-name="mirissa beach getaway whale watching" data-category="beach">
      <div class="card-img">
        <img src="img/Mirissa tp.jpg">
      </div>
      <div class="card-body">
        <div class="card-title">Mirissa Beach Getaway & Whale Watching</div>
        <div class="card-meta"><i class="far fa-calendar-alt"></i> 2 Days | <i class="fas fa-ship"></i> Whale Watching</div>
        <div class="card-footer">
          <div class="card-price"><sup>LKR</sup>28,750 <span>/ person</span></div>
          <button class="btn-book" onclick="requireLogin('Mirissa Beach Getaway & Whale Watching', 28750)">Book Now</button>
        </div>
      </div>
    </div>

    <!-- Package 4: Kandy -->
    <div class="pkg-card" data-name="kandy heritage temple of the tooth tour" data-category="cultural">
      <div class="card-img">
        <img src="img/Kandy.jpg">
      </div>
      <div class="card-body">
        <div class="card-title">Kandy Heritage & Temple of the Tooth Tour</div>
        <div class="card-meta"><i class="far fa-calendar-alt"></i> 2 Days | <i class="fas fa-praying-hands"></i> Cultural Tour</div>
        <div class="card-footer">
          <div class="card-price"><sup>LKR</sup>26,500 <span>/ person</span></div>
          <button class="btn-book" onclick="requireLogin('Kandy Heritage & Temple of the Tooth Tour', 26500)">Book Now</button>
        </div>
      </div>
    </div>

    <!-- Package 5: Nuwara Eliya -->
    <div class="pkg-card" data-name="nuwara eliya tea country cool climate escape" data-category="nature">
      <div class="card-img">
        <img src="img/Nuwara Eliya tp.jpg">
      </div>
      <div class="card-body">
        <div class="card-title">Nuwara Eliya Tea Country & Cool Climate Escape</div>
        <div class="card-meta"><i class="far fa-calendar-alt"></i> 3 Days | <i class="fas fa-leaf"></i> Tea Plantation</div>
        <div class="card-footer">
          <div class="card-price"><sup>LKR</sup>38,900 <span>/ person</span></div>
          <button class="btn-book" onclick="requireLogin('Nuwara Eliya Tea Country & Cool Climate Escape', 38900)">Book Now</button>
        </div>
      </div>
    </div>

    <!-- Package 6: Yala -->
    <div class="pkg-card" data-name="yala national park safari adventure" data-category="nature">
      <div class="card-img">
        <img src="img/Yala tp.jpg">
      </div>
      <div class="card-body">
        <div class="card-title">Yala National Park Safari Adventure</div>
        <div class="card-meta"><i class="far fa-calendar-alt"></i> 2 Days | <i class="fas fa-paw"></i> Wildlife Safari</div>
        <div class="card-footer">
          <div class="card-price"><sup>LKR</sup>31,500 <span>/ person</span></div>
          <button class="btn-book" onclick="requireLogin('Yala National Park Safari Adventure', 31500)">Book Now</button>
        </div>
      </div>
    </div>

    <!-- Package 7: Galle -->
    <div class="pkg-card" data-name="galle fort southern coast experience" data-category="cultural">
      <div class="card-img">
        <img src="img/Galle.jpg">
      </div>
      <div class="card-body">
        <div class="card-title">Galle Fort & Southern Coast Experience</div>
        <div class="card-meta"><i class="far fa-calendar-alt"></i> 2 Days | <i class="fas fa-fort"></i> Heritage Walk</div>
        <div class="card-footer">
          <div class="card-price"><sup>LKR</sup>29,900 <span>/ person</span></div>
          <button class="btn-book" onclick="requireLogin('Galle Fort & Southern Coast Experience', 29900)">Book Now</button>
        </div>
      </div>
    </div>

    <!-- Package 8: Trincomalee -->
    <div class="pkg-card" data-name="trincomalee beach nilaveli island tour" data-category="beach">
      <div class="card-img">
        <img src="img/Trincomalee Beach.jpg">
      </div>
      <div class="card-body">
        <div class="card-title">Trincomalee Beach & Nilaveli Island Tour</div>
        <div class="card-meta"><i class="far fa-calendar-alt"></i> 3 Days | <i class="fas fa-umbrella-beach"></i> Beach Getaway</div>
        <div class="card-footer">
          <div class="card-price"><sup>LKR</sup>40,200 <span>/ person</span></div>
          <button class="btn-book" onclick="requireLogin('Trincomalee Beach & Nilaveli Island Tour', 40200)">Book Now</button>
        </div>
      </div>
    </div>

    <!-- Package 9: Arugam Bay -->
    <div class="pkg-card" data-name="arugam bay surfing beach adventure" data-category="adventure">
      <div class="card-img">
        <img src="img/Arugam Bay.jpg">
      </div>
      <div class="card-body">
        <div class="card-title">Arugam Bay Surfing & Beach Adventure</div>
        <div class="card-meta"><i class="far fa-calendar-alt"></i> 3 Days | <i class="fas fa-water"></i> Surfing Lessons</div>
        <div class="card-footer">
          <div class="card-price"><sup>LKR</sup>37,800 <span>/ person</span></div>
          <button class="btn-book" onclick="requireLogin('Arugam Bay Surfing & Beach Adventure', 37800)">Book Now</button>
        </div>
      </div>
    </div>

    <!-- Package 10: Anuradhapura -->
    <div class="pkg-card" data-name="anuradhapura ancient city sacred sites" data-category="cultural">
      <div class="card-img">
        <img src="img/Anuradhapura.jpg">
      </div>
      <div class="card-body">
        <div class="card-title">Anuradhapura Ancient City & Sacred Sites</div>
        <div class="card-meta"><i class="far fa-calendar-alt"></i> 2 Days | <i class="fas fa-landmark"></i> Historical Tour</div>
        <div class="card-footer">
          <div class="card-price"><sup>LKR</sup>27,900 <span>/ person</span></div>
          <button class="btn-book" onclick="requireLogin('Anuradhapura Ancient City & Sacred Sites', 27900)">Book Now</button>
        </div>
      </div>
    </div>

    <!-- Package 11: Polonnaruwa -->
    <div class="pkg-card" data-name="polonnaruwa ruins cycling heritage tour" data-category="cultural">
      <div class="card-img">
        <img src="img/Ancient Vatadage at Polonnaruwa.jpg">
      </div>
      <div class="card-body">
        <div class="card-title">Polonnaruwa Ruins & Cycling Heritage Tour</div>
        <div class="card-meta"><i class="far fa-calendar-alt"></i> 2 Days | <i class="fas fa-bicycle"></i> Cycling Tour</div>
        <div class="card-footer">
          <div class="card-price"><sup>LKR</sup>26,900 <span>/ person</span></div>
          <button class="btn-book" onclick="requireLogin('Polonnaruwa Ruins & Cycling Heritage Tour', 26900)">Book Now</button>
        </div>
      </div>
    </div>

    <!-- Package 12: Hikkaduwa -->
    <div class="pkg-card" data-name="hikkaduwa coral reef beach relaxation" data-category="beach">
      <div class="card-img">
        <img src="img/Hikkaduwa.jpg">
      </div>
      <div class="card-body">
        <div class="card-title">Hikkaduwa Coral Reef & Beach Relaxation</div>
        <div class="card-meta"><i class="far fa-calendar-alt"></i> 2 Days | <i class="fas fa-fish"></i> Snorkeling</div>
        <div class="card-footer">
          <div class="card-price"><sup>LKR</sup>25,500 <span>/ person</span></div>
          <button class="btn-book" onclick="requireLogin('Hikkaduwa Coral Reef & Beach Relaxation', 25500)">Book Now</button>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- NEWSLETTER SECTION -->
<section class="newsletter-section">
  <div class="section-label" style="justify-content:center; color:var(--aqua);">Stay Inspired</div>
  <h2 class="section-title">Get Exclusive Deals & Travel Inspiration</h2>
  <p class="section-sub">Join 50,000+ travelers who receive our weekly deals, destination guides, and insider tips.</p>
  <div class="newsletter-form">
    <input type="email" id="newsletterEmail" placeholder="Enter your email address...">
    <button onclick="handleNewsletter()">Subscribe Free</button>
  </div>
</section>

<!-- FOOTER -->
<footer>
  <div class="footer-bottom">
    <span>© 2025 GlobeTrek Adventures (Pvt) Ltd. All rights reserved. | Crafted with ❤️ for Sri Lanka travel</span>
  </div>
</footer>

<!-- LOGIN REQUIRED MODAL -->
<div class="modal-overlay" id="loginModal">
  <div class="modal">
    <button class="modal-close" onclick="closeModal()">✕</button>
    <h2>Ready to Book?</h2>
    <p id="modalPackageInfo">You need to be logged in to book a package. Create a free account or sign in to continue.</p>
    <div class="modal-btns">
      <a href="login.html" class="modal-btn-login">Login</a>
            <a href="register.html" class="modal-btn-register">Register</a>
    </div>
  </div>
</div>

<!-- BACK TO TOP BUTTON -->
<div class="back-to-top" id="backToTop" onclick="scrollToTop()">
  <i class="fas fa-arrow-up"></i>
</div>

<script>
  // ========================
  // PACKAGE FILTERING & SEARCH
  // ========================
  function filterPackages() {
    const searchTerm = document.getElementById('searchInput').value.toLowerCase();
    const activeFilter = document.querySelector('.filter-btn.active');
    const category = activeFilter ? activeFilter.getAttribute('data-filter') : 'all';
    
    const cards = document.querySelectorAll('.pkg-card');
    let hasVisible = false;
    
    cards.forEach(card => {
      const cardName = card.getAttribute('data-name') || '';
      const cardCategory = card.getAttribute('data-category') || '';
      
      const matchesSearch = cardName.includes(searchTerm);
      const matchesCategory = (category === 'all') || (cardCategory === category);
      
      if (matchesSearch && matchesCategory) {
        card.style.display = 'block';
        hasVisible = true;
      } else {
        card.style.display = 'none';
      }
    });
    
    // Show "no results" message if needed
    const grid = document.getElementById('packagesGrid');
    let noResultsDiv = document.getElementById('noResultsMsg');
    
    if (!hasVisible) {
      if (!noResultsDiv) {
        noResultsDiv = document.createElement('div');
        noResultsDiv.id = 'noResultsMsg';
        noResultsDiv.className = 'no-results';
        noResultsDiv.innerHTML = '<i class="fas fa-map-marked-alt" style="font-size: 2rem; margin-bottom: 12px; display: block;"></i>No packages match your search. Try a different destination or category!';
        grid.appendChild(noResultsDiv);
      }
    } else {
      if (noResultsDiv) noResultsDiv.remove();
    }
  }
  
 
  
  // ========================
  // SEARCH INPUT (real-time)
  // ========================
  const searchInput = document.getElementById('searchInput');
  if (searchInput) {
    searchInput.addEventListener('keyup', function(e) {
      filterPackages();
    });
  }
  
  // ========================
  // LOGIN MODAL HANDLING
  // ========================
  let pendingPackage = null;
  let pendingPrice = null;
  
  function requireLogin(packageName, price) {
    pendingPackage = packageName;
    pendingPrice = price;
    const modalPackageInfo = document.getElementById('modalPackageInfo');
    if (modalPackageInfo) {
      modalPackageInfo.innerHTML = `You need to be logged in to book <strong>${packageName}</strong> (LKR ${price.toLocaleString()}/person).<br>Create a free account or sign in to continue.`;
    }
    document.getElementById('loginModal').classList.add('open');
    document.body.style.overflow = 'hidden';
  }
  
  function closeModal() {
    document.getElementById('loginModal').classList.remove('open');
    document.body.style.overflow = '';
    pendingPackage = null;
    pendingPrice = null;
  }
  
  // Close modal when clicking outside the modal content
  document.getElementById('loginModal').addEventListener('click', function(e) {
    if (e.target === this) {
      closeModal();
    }
  });
  
  // Escape key to close modal
  document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
      const modal = document.getElementById('loginModal');
      if (modal.classList.contains('open')) {
        closeModal();
      }
    }
  });
  
  // ========================
  // NEWSLETTER SUBSCRIPTION
  // ========================
  function handleNewsletter() {
    const emailInput = document.getElementById('newsletterEmail');
    const email = emailInput.value.trim();
    const emailRegex = /^[^\s@]+@([^\s@.,]+\.)+[^\s@.,]{2,}$/;
    
    if (!email) {
      alert('❌ Please enter your email address.');
      return;
    }
    if (!emailRegex.test(email)) {
      alert('❌ Please enter a valid email address (e.g., name@example.com).');
      return;
    }
    
    // Simulate successful subscription
    alert(`✅ Thanks for subscribing! ${email} will receive exclusive Sri Lanka travel deals.`);
    emailInput.value = '';
  }
  
 
  
  // ========================
  // SMOOTH SCROLL FOR NAV LINKS
  // ========================
  document.querySelectorAll('a[href^="#"]').forEach(anchor => {
    anchor.addEventListener('click', function(e) {
      const href = this.getAttribute('href');
      if (href === '#' || href === '') return;
      
      const targetElement = document.querySelector(href);
      if (targetElement) {
        e.preventDefault();
        targetElement.scrollIntoView({
          behavior: 'smooth',
          block: 'start'
        });
      }
    });
  });
  
  
  // ========================
  // CARD CLICK (optional - view details)
  // ========================
  const packageCards = document.querySelectorAll('.pkg-card');
  packageCards.forEach(card => {
    card.addEventListener('click', function(e) {
      // Don't trigger if clicking on button
      if (e.target.classList.contains('btn-book') || e.target.closest('.btn-book')) return;
      
      const title = this.querySelector('.card-title')?.innerText || 'Package';
      const price = this.querySelector('.card-price')?.innerText || 'LKR';
      const duration = this.querySelector('.card-meta')?.innerText || 'Various durations';
      
      alert(`✨ ${title}\n💰 ${price}\n📅 ${duration}\n\nContact us or click "Book Now" to reserve your adventure!`);
    });
  });
  
  // ========================
  // INITIAL FILTER & MOBILE NAV PLACEHOLDER
  // ========================
  document.addEventListener('DOMContentLoaded', function() {
    filterPackages();
    
    // Simple mobile menu note (no actual mobile menu built but prevents errors)
    console.log('GlobeTrek Adventures - Tour Packages Loaded');
  });
</script>

</body>
</html>