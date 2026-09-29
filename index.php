<?php

?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1.0"/>

<title>GlobeTrek Adventures – Explore the beauty of Sri Lanka</title>
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

    .nav-links {
      display: flex;
      align-items: center;
      gap: 40px;
      list-style: none;
    }
    .nav-links a {
      color: rgba(255,255,255,0.8);
      text-decoration: none;
      font-size: 0.95rem;
      font-weight: 500;
      transition: color 0.2s;
      position: relative;
    }
    .nav-links a::after {
      content: '';
      position: absolute;
      bottom: -4px; left: 0; right: 0;
      height: 2px;
      background: var(--aqua);
      transform: scaleX(0);
      transition: transform 0.2s;
    }
    .nav-links a:hover { color: var(--white); }
    .nav-links a:hover::after { transform: scaleX(1); }
  
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

    /* HERO SECTION */
    .hero {
      min-height: 90vh;
      background: 
        linear-gradient(135deg, rgba(10,22,40,0.85) 0%, rgba(14,58,110,0.6) 100%),
        url('img/UI.jpg') center/cover no-repeat;
      display: flex;
      flex-direction: column;
      align-items: center;
      justify-content: center;
      padding: 140px 20px 80px;
      position: relative;
    }

    .hero::before {
      content: '';
      position: absolute;
      bottom: 0; left: 0; right: 0;
      height: 100px;
      background: linear-gradient(to top, var(--white), transparent);
    }

    .hero h1 {
      font-family: 'Playfair Display', serif;
      font-size: clamp(2.5rem, 6vw, 5rem);
      font-weight: 900;
      color: var(--white);
      text-align: center;
      line-height: 1.1;
      letter-spacing: -0.02em;
      max-width: 800px;
      margin-bottom: 24px;
      animation: fadeDown 0.6s ease both;
    }

    .hero-sub {
      color: rgba(255,255,255,0.85);
      font-size: 1.2rem;
      text-align: center;
      max-width: 550px;
      margin-bottom: 48px;
      animation: fadeDown 0.6s 0.1s ease both;
    }

    /* SEARCH BOX - FIXED */
    .search-box {
      background: rgba(255,255,255,0.98);
      backdrop-filter: blur(16px);
      border-radius: 24px;
      padding: 28px 32px;
      width: 100%;
      max-width: 900px;
      box-shadow: 0 30px 60px rgba(0,0,0,0.2);
      animation: fadeUp 0.6s 0.2s ease both;
    }

    .search-row {
      display: grid;
      grid-template-columns: 1fr 1fr 1fr auto;
      gap: 20px;
      align-items: end;
    }

    .search-field {
      display: flex;
      flex-direction: column;
    }

    .search-field label {
      font-size: 0.75rem;
      font-weight: 700;
      color: var(--text-light);
      text-transform: uppercase;
      letter-spacing: 0.08em;
      margin-bottom: 6px;
    }

    .search-field input,
    .search-field select {
      width: 100%;
      padding: 10px 14px;
      border: 1px solid #e0e6ed;
      border-radius: 12px;
      font-size: 0.95rem;
      font-family: 'DM Sans', sans-serif;
      background: #fff;
      outline: none;
      transition: var(--transition);
    }

    .search-field input:focus,
    .search-field select:focus {
      border-color: var(--orange);
      box-shadow: 0 0 0 3px rgba(255,107,43,0.1);
    }

    .btn-search {
      padding: 10px 28px;
      background: var(--orange);
      color: var(--white);
      border: none;
      border-radius: 12px;
      font-family: 'DM Sans', sans-serif;
      font-size: 0.95rem;
      font-weight: 600;
      cursor: pointer;
      transition: var(--transition);
      height: 44px;
    }
    
    .btn-search:hover {
      background: var(--orange-dark);
      transform: translateY(-2px);
      box-shadow: 0 8px 20px rgba(255,107,43,0.3);
    }

    /* SECTION STYLES */
    .section {
      padding: 80px 60px;
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
      margin: 0 auto;
    }

    .section-center .section-label {
      justify-content: center;
    }

    .view-all-wrapper {
      display: flex;
      justify-content: flex-end;
      margin: 20px 0 30px;
    }

    .view-all {
      color: var(--sky);
      font-weight: 600;
      text-decoration: none;
      font-size: 0.9rem;
      transition: var(--transition);
    }
    
    .view-all:hover {
      color: var(--orange);
      transform: translateX(4px);
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

    /* DESTINATIONS SECTION */
    .destinations-section { 
      background: var(--navy); 
      padding: 80px 60px; 
    }

    .destinations-section .section-label {
      color: var(--aqua);
      justify-content: center;
    }
    .destinations-section .section-label::before { background: var(--aqua); }
    .destinations-section .section-title { color: var(--white); text-align: center; }
    .destinations-section .section-sub { color: rgba(255,255,255,0.6); text-align: center; margin: 0 auto; }

    .dest-grid {
      display: grid;
      grid-template-columns: repeat(6, 1fr);
      gap: 20px;
      margin-top: 48px;
    }

    .dest-card {
      position: relative;
      border-radius: 16px;
      overflow: hidden;
      aspect-ratio: 3/4;
      cursor: pointer;
    }

    .dest-card img {
      width: 100%; height: 100%;
      object-fit: cover;
      transition: transform 0.5s;
    }
    .dest-card:hover img { transform: scale(1.1); }

    .dest-overlay {
      position: absolute;
      inset: 0;
      background: linear-gradient(to top, rgba(10,22,40,0.9) 0%, transparent 50%);
      display: flex;
      flex-direction: column;
      justify-content: flex-end;
      padding: 20px;
    }
    .dest-name {
      font-family: 'Playfair Display', serif;
      font-size: 1.1rem;
      font-weight: 700;
      color: var(--white);
    }
    .dest-count {
      font-size: 0.7rem;
      color: rgba(255,255,255,0.7);
    }

    /* FEATURES GRID */
    .features-grid {
      display: grid;
      grid-template-columns: repeat(4, 1fr);
      gap: 30px;
      margin-top: 56px;
    }
    .feature-card {
      text-align: center;
      padding: 32px 24px;
      border-radius: 20px;
      background: var(--cream);
      transition: var(--transition);
    }
    .feature-card:hover { 
      transform: translateY(-6px); 
      box-shadow: var(--card-shadow); 
      background: var(--white); 
    }

    .feature-icon {
      width: 70px; height: 70px;
      border-radius: 20px;
      margin: 0 auto 20px;
      display: flex; align-items: center; justify-content: center;
      font-size: 32px;
    }
    .fi-blue { background: #e3f2fd; }
    .fi-orange { background: #fff3e0; }
    .fi-green { background: #e8f5e9; }
    .fi-purple { background: #f3e5f5; }

    .feature-title {
      font-family: 'Playfair Display', serif;
      font-size: 1.15rem;
      font-weight: 700;
      color: var(--navy);
      margin-bottom: 12px;
    }
    .feature-desc {
      font-size: 0.85rem;
      color: var(--text-mid);
      line-height: 1.6;
    }

    /* TESTIMONIALS SECTION */
    .testimonials-section {
      padding: 80px 60px;
      background: #f8fafc;
    }

    .testimonials-grid {
      max-width: 1200px;
      margin: 48px auto 0;
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(320px, 1fr));
      gap: 30px;
    }

    .testimonial-card {
      background: #ffffff;
      padding: 28px;
      border-radius: 20px;
      box-shadow: 0 5px 20px rgba(0,0,0,0.05);
      transition: var(--transition);
    }

    .testimonial-card:hover {
      transform: translateY(-4px);
      box-shadow: 0 15px 30px rgba(0,0,0,0.1);
    }

    .stars {
      font-weight: 700;
      font-size: 1rem;
      color: var(--orange);
      margin-bottom: 16px;
    }

    .testimonial-text {
      color: var(--text-mid);
      font-size: 0.9rem;
      line-height: 1.7;
      margin-bottom: 20px;
    }

    .testimonial-author {
      display: flex;
      align-items: center;
      gap: 12px;
    }

    .author-avatar {
      width: 45px;
      height: 45px;
      border-radius: 50%;
      object-fit: cover;
    }

    .author-name {
      font-weight: 700;
      color: var(--navy);
    }

    .author-trip {
      font-size: 0.75rem;
      color: var(--sky);
    }

    
    .contact-section {
      padding: 80px 60px;
      background: linear-gradient(135deg, #f0f4f8 0%, #e8eef4 100%);
    }

    .contact-grid {
      display: grid;
      grid-template-columns: 1fr 1fr;
      gap: 50px;
      margin-top: 48px;
    }

    .contact-info {
      background: var(--white);
      padding: 40px;
      border-radius: 24px;
      box-shadow: var(--card-shadow);
    }

    .contact-item {
      display: flex;
      align-items: flex-start;
      gap: 16px;
      margin-bottom: 32px;
    }

    .contact-icon {
      width: 50px;
      height: 50px;
      background: var(--cream);
      border-radius: 14px;
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 22px;
      color: var(--orange);
    }

    .contact-details h4 {
      font-weight: 700;
      font-size: 1.1rem;
      color: var(--navy);
      margin-bottom: 6px;
    }

    .contact-details p {
      color: var(--text-mid);
      font-size: 0.9rem;
      line-height: 1.5;
    }

    .contact-details a {
      color: var(--text-mid);
      text-decoration: none;
      transition: var(--transition);
    }

    .contact-details a:hover {
      color: var(--orange);
    }

    .social-links {
      display: flex;
      gap: 15px;
      margin-top: 30px;
    }

    .social-link {
      width: 42px;
      height: 42px;
      background: var(--cream);
      border-radius: 50%;
      display: flex;
      align-items: center;
      justify-content: center;
      color: var(--sky);
      font-size: 18px;
      transition: var(--transition);
      text-decoration: none;
    }

    .social-link:hover {
      background: var(--orange);
      color: var(--white);
      transform: translateY(-3px);
    }

    .contact-form {
      background: var(--white);
      padding: 40px;
      border-radius: 24px;
      box-shadow: var(--card-shadow);
    }

    .form-group {
      margin-bottom: 20px;
    }

    .form-group input,
    .form-group textarea {
      width: 100%;
      padding: 12px 16px;
      border: 1px solid #e0e6ed;
      border-radius: 12px;
      font-family: 'DM Sans', sans-serif;
      font-size: 0.9rem;
      transition: var(--transition);
    }

    .form-group input:focus,
    .form-group textarea:focus {
      outline: none;
      border-color: var(--orange);
      box-shadow: 0 0 0 3px rgba(255,107,43,0.1);
    }

    .form-group textarea {
      resize: vertical;
      min-height: 100px;
    }

    .btn-submit {
      width: 100%;
      padding: 14px;
      background: var(--orange);
      color: var(--white);
      border: none;
      border-radius: 12px;
      font-family: 'DM Sans', sans-serif;
      font-size: 1rem;
      font-weight: 600;
      cursor: pointer;
      transition: var(--transition);
    }

    .btn-submit:hover {
      background: var(--orange-dark);
      transform: translateY(-2px);
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

    /* ANIMATIONS */
    @keyframes fadeDown {
      from { opacity: 0; transform: translateY(-25px); }
      to { opacity: 1; transform: translateY(0); }
    }
    @keyframes fadeUp {
      from { opacity: 0; transform: translateY(25px); }
      to { opacity: 1; transform: translateY(0); }
    }
    @keyframes scaleIn {
      from { opacity: 0; transform: scale(0.95); }
      to { opacity: 1; transform: scale(1); }
    }

    /* RESPONSIVE */
    @media (max-width: 1100px) {
      .dest-grid { grid-template-columns: repeat(3, 1fr); }
    }
    
    @media (max-width: 900px) {
      .search-row { grid-template-columns: 1fr; gap: 15px; }
      .cards-grid { grid-template-columns: repeat(2, 1fr); }
      .features-grid { grid-template-columns: repeat(2, 1fr); }
      .contact-grid { grid-template-columns: 1fr; }
      .section, .destinations-section, .testimonials-section, .contact-section, .newsletter-section { padding: 60px 30px; }
      nav { padding: 15px 25px; }
      .nav-links { display: none; }
    }
    
    @media (max-width: 600px) {
      .cards-grid { grid-template-columns: 1fr; }
      .dest-grid { grid-template-columns: repeat(2, 1fr); }
      .features-grid { grid-template-columns: 1fr; }
      .newsletter-form { flex-direction: column; }
      .hero h1 { font-size: 2rem; }
    }
</style>
</head>

<body>

<!-- NAVIGATION -->
<nav>
  <a href="index.php" class="logo">
    <span class="logo-text">Globe<span>Trek</span></span>
  </a>
  <ul class="nav-links">
    <li><a href="#packages">Tour Packages</a></li>
    <li><a href="#destinations">Destinations</a></li>
    <li><a href="#why-us">Why Us</a></li>
    <li><a href="#contact">Contact</a></li>
  </ul>
  <div class="nav-cta">
    <a href="login.html" class="btn-outline-nav">Login</a>
    <a href="register.html" class="btn-fill-nav">Register</a>
  </div>
</nav>

<!-- HERO SECTION -->
<section class="hero">
  <h1>Discover Sri Lanka's Most Epic Destinations</h1>
  <p class="hero-sub">Curated adventures, seamless bookings, unforgettable memories. Your dream journey is just one search away.</p>

  <div class="search-box">
    <div class="search-row">
      <div class="search-field">
        <label><i class="fas fa-map-marker-alt"></i> Destination</label>
        <input type="text" placeholder="Where do you want to go?" id="dest-input"/>
      </div>
      <div class="search-field">
        <label><i class="fas fa-calendar"></i> Travel Date</label>
        <input type="date" id="travel-date"/>
      </div>
      <div class="search-field">
        <label><i class="fas fa-users"></i> Travelers</label>
        <select id="travelers-select">
          <option>1 Person</option>
          <option>2 Persons</option>
          <option>3 Persons</option>
          <option>4 Persons</option>
          <option>5 Persons</option>
          <option>6+ Persons</option>
        </select>
      </div>
      <button class="btn-search" onclick="handleSearch()"><i class="fas fa-search"></i> Search</button>
    </div>
  </div>
</section>

<!-- FEATURED PACKAGES SECTION -->
<section class="section" id="packages">
  <div class="section-center">
    <div class="section-label">Featured Packages</div>
    <h2 class="section-title">Popular Travel Packages<br>Just for You</h2>
    <p class="section-sub">Explore our most popular Sri Lankan travel experiences, carefully crafted by local experts.</p>
  </div>

  <div class="view-all-wrapper">
    <a href="package.php" class="view-all">View All Packages →</a>
  </div>

  <!-- SEARCH INPUT FOR FILTERING PACKAGES -->
  <div style="margin-bottom: 30px; max-width: 400px;">
    <input type="text" id="package-search" placeholder="🔍 Filter packages by name..." style="width: 100%; padding: 12px 16px; border: 1px solid #e0e6ed; border-radius: 12px; font-size: 0.9rem;">
  </div>

  <div class="cards-grid" id="packages-grid">
    <!-- SIGIRIYA PACKAGE -->
    <div class="pkg-card" data-name="sigiriya rock fortress cultural tour">
      <div class="card-img">
        <img src="img/Sigiriya Rock.jpg" alt="Sigiriya">
      </div>
      <div class="card-body">
        <div class="card-title">Sigiriya Rock Fortress & Cultural Tour</div>
        <div class="card-meta"><i class="far fa-calendar-alt"></i> 3 Days | <i class="fas fa-clock"></i> Guided Tour</div>
        <div class="card-footer">
          <div class="card-price"><sup>LKR</sup>35,500 <span>/ person</span></div>
          <button class="btn-book" onclick="goToBooking('Sigiriya Rock Fortress & Cultural Tour', 35500)">Book Now</button>
        </div>
      </div>
    </div>

    <!-- ELLA PACKAGE -->
    <div class="pkg-card" data-name="ella scenic escape train mountains">
      <div class="card-img">
        <img src="img/Ella.jpg">
      </div>
      <div class="card-body">
        <div class="card-title">Ella Scenic Escape – Train & Mountains</div>
        <div class="card-meta"><i class="far fa-calendar-alt"></i> 4 Days | <i class="fas fa-train"></i> Train Journey</div>
        <div class="card-footer">
          <div class="card-price"><sup>LKR</sup>42,900 <span>/ person</span></div>
          <button class="btn-book" onclick="goToBooking('Ella Scenic Escape – Train & Mountains', 42900)">Book Now</button>
        </div>
      </div>
    </div>

    <!-- MIRISSA PACKAGE -->
    <div class="pkg-card" data-name="mirissa beach getaway whale watching">
      <div class="card-img">
        <img src="img/Mirissa tp.jpg">
      </div>
      <div class="card-body">
        <div class="card-title">Mirissa Beach Getaway & Whale Watching</div>
        <div class="card-meta"><i class="far fa-calendar-alt"></i> 2 Days | <i class="fas fa-ship"></i> Whale Watching</div>
        <div class="card-footer">
          <div class="card-price"><sup>LKR</sup>28,750 <span>/ person</span></div>
          <button class="btn-book" onclick="goToBooking('Mirissa Beach Getaway & Whale Watching', 28750)">Book Now</button>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- DESTINATIONS SECTION -->
<section class="destinations-section" id="destinations">
  <div class="section-center">
    <div class="section-label">Top Destinations</div>
    <h2 class="section-title">Where Do You Want to Go?</h2>
    <p class="section-sub">
      From misty mountains to golden beaches — discover the beauty of Sri Lanka.
    </p>
  </div>

  <div class="dest-grid">

    <!-- SIGIRIYA -->
    <div class="dest-card">
      <img src="img/Sigiriya tp.jpg" alt="Sigiriya">
      <div class="dest-overlay">
        <div class="dest-name">Sigiriya</div>
        <div class="dest-count">18 packages</div>
      </div>
    </div>

    <!-- ELLA -->
    <div class="dest-card">
      <img src="img/Ella tp.jpg" alt="Ella">
      <div class="dest-overlay">
        <div class="dest-name">Ella</div>
        <div class="dest-count">14 packages</div>
      </div>
    </div>

    <!-- KANDY -->
    <div class="dest-card">
      <img src="img/Kandy tp.jpg" alt="Kandy">
      <div class="dest-overlay">
        <div class="dest-name">Kandy</div>
        <div class="dest-count">22 packages</div>
      </div>
    </div>

    <!-- GALLE -->
    <div class="dest-card">
      <img src="img/Galle.jpg" alt="Galle">
      <div class="dest-overlay">
        <div class="dest-name">Galle</div>
        <div class="dest-count">16 packages</div>
      </div>
    </div>

    <!-- NUWARA ELIYA -->
    <div class="dest-card">
      <img src="img/Nuwara Eliya tp.jpg" alt="Nuwara Eliya">
      <div class="dest-overlay">
        <div class="dest-name">Nuwara Eliya</div>
        <div class="dest-count">20 packages</div>
      </div>
    </div>

    <!-- YALA -->
    <div class="dest-card">
      <img src="img/Yala tp.jpg" alt="Yala">
      <div class="dest-overlay">
        <div class="dest-name">Yala</div>
        <div class="dest-count">12 packages</div>
      </div>
    </div>

  </div>
</section>

<!-- WHY US / FEATURES SECTION -->
<section class="section" id="why-us">
  <div class="section-center">
    <div class="section-label">Why Choose Us</div>
    <h2 class="section-title">Travel With Confidence & Comfort</h2>
    <p class="section-sub">Exceptional service, local expertise, and unforgettable experiences — we make it all happen.</p>
  </div>
  <div class="features-grid">
    <div class="feature-card">
      <div class="feature-icon fi-blue"><i class="fas fa-leaf"></i></div>
      <h3 class="feature-title">Eco-friendly Travel</h3>
      <p class="feature-desc">Sustainable tourism that respects local communities and preserves Sri Lanka's natural beauty.</p>
    </div>
    <div class="feature-card">
      <div class="feature-icon fi-orange"><i class="fas fa-star"></i></div>
      <h3 class="feature-title">Award-winning Service</h3>
      <p class="feature-desc">Recognized excellence in guided tours, customer support, and personalized experiences.</p>
    </div>
    <div class="feature-card">
      <div class="feature-icon fi-green"><i class="fas fa-shield-alt"></i></div>
      <h3 class="feature-title">Safe & Secure</h3>
      <p class="feature-desc">24/7 support, insured journeys, and trusted local partners for peace of mind.</p>
    </div>
    <div class="feature-card">
      <div class="feature-icon fi-purple"><i class="fas fa-laugh-beam"></i></div>
      <h3 class="feature-title">Best Price Guarantee</h3>
      <p class="feature-desc">Unbeatable rates with no hidden fees — your dream adventure at the best value.</p>
    </div>
  </div>
</section>

<!-- TESTIMONIALS SECTION -->
<section class="testimonials-section">
  <div class="section-center">
    <div class="section-label">Happy Travelers</div>
    <h2 class="section-title">Real Stories, Real Adventures</h2>
    <p class="section-sub">What our guests say about their Sri Lankan journey with GlobeTrek Adventures.</p>
  </div>
  <div class="testimonials-grid">
    <div class="testimonial-card">
      <div class="stars">Sigiriya Cultural Tour</div>
      <p class="testimonial-text">"Absolutely magical experience! The Sigiriya tour was perfectly organized — our guide was knowledgeable and the views were breathtaking. Highly recommend GlobeTrek for anyone visiting Sri Lanka!"</p>
      <div class="testimonial-author">
        <img src="img/comment2.jpg" alt="Emma" class="author-avatar">
        <div>
          <div class="author-name">Emma Thompson</div>
          <div class="author-trip">Traveller</div>
        </div>
      </div>
    </div>
    <div class="testimonial-card">
      <div class="stars">Ella Scenic Escape</div>
      <p class="testimonial-text">"The Ella train journey was a dream! From seamless booking to amazing accommodations, everything was perfect. We will definitely book again with GlobeTrek for our next incredible trip."</p>
      <div class="testimonial-author">
        <img src="img/comment1.jpg" alt="Michael" class="author-avatar">
        <div>
          <div class="author-name">Michael Chen</div>
          <div class="author-trip">Traveller</div>
        </div>
      </div>
    </div>
    <div class="testimonial-card">
      <div class="stars">Mirissa Beach Getaway</div>
      <p class="testimonial-text">"Whale watching in Mirissa was unforgettable! The team was professional and friendly, ensuring we had the best spots. Thank you GlobeTrek for providing such an amazing and memorable holiday!"</p>
      <div class="testimonial-author">
        <img src="img/comment3.jpg" alt="Sophia" class="author-avatar">
        <div>
          <div class="author-name">Sophia Rodriguez</div>
          <div class="author-trip">Traveller</div>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- CONTACT SECTION -->
<section class="contact-section" id="contact">
  <div class="section-center">
    <div class="section-label">Get In Touch</div>
    <h2 class="section-title">We'd Love to Hear From You</h2>
    <p class="section-sub">Have questions about your next adventure? Reach out to our travel specialists today.</p>
  </div>
  <div class="contact-grid">
    <div class="contact-info">
      <div class="contact-item">
        <div class="contact-icon"><i class="fas fa-map-pin"></i></div>
        <div class="contact-details">
          <h4>Visit Us</h4>
          <p>No. 63, Porutota Road, Negombo, Sri Lanka</p>
        </div>
      </div>
      <div class="contact-item">
        <div class="contact-icon"><i class="fas fa-phone-alt"></i></div>
        <div class="contact-details">
          <h4>Call Us</h4>
          <p><a href="tel:+94112345678">+94 11 234 5678</a> (Daily 9am–6pm)</p>
          <p><a href="tel:+94771234567">+94 77 123 4567</a> (Emergency 24/7)</p>
        </div>
      </div>
      <div class="contact-item">
        <div class="contact-icon"><i class="fas fa-envelope"></i></div>
        <div class="contact-details">
          <h4>Email Us</h4>
          <p><a href="mailto:hello@globetrek.lk">hello@globetrek.lk</a> (General inquiries)</p>
          <p><a href="mailto:reservations@globetrek.lk">reservations@globetrek.lk</a> (Bookings)</p>
        </div>
      </div>
      <div class="social-links">
        <a href="#" class="social-link"><i class="fab fa-facebook-f"></i></a>
        <a href="#" class="social-link"><i class="fab fa-instagram"></i></a>
        <a href="#" class="social-link"><i class="fab fa-tiktok"></i></a>
      </div>
    </div>
    <div class="contact-form">
      <form id="contactForm" onsubmit="handleContactSubmit(event)">
        <div class="form-group">
          <input type="text" id="name" placeholder="Your Name" required>
        </div>
        <div class="form-group">
          <input type="email" id="email" placeholder="Email Address" required>
        </div>
        <div class="form-group">
          <input type="tel" id="phone" placeholder="Phone Number (optional)">
        </div>
        <div class="form-group">
          <textarea id="message" placeholder="Tell us about your travel plans..." required></textarea>
        </div>
        <button type="submit" class="btn-submit">Send Message <i class="fas fa-paper-plane"></i></button>
      </form>
    </div>
  </div>
</section>

<!-- NEWSLETTER SECTION -->
<section class="newsletter-section">
  <div class="section-center">
    <div class="section-label">Stay Inspired</div>
    <h2 class="section-title">Join Our Newsletter</h2>
    <p class="section-sub">Subscribe to get exclusive travel deals, destination guides, and insider tips — delivered straight to your inbox.</p>
  </div>
  <form class="newsletter-form" onsubmit="handleNewsletter(event)">
    <input type="email" id="newsletter-email" placeholder="Your email address" required>
    <button type="submit">Subscribe <i class="fas fa-arrow-right"></i></button>
  </form>
</section>

<!-- FOOTER -->
<footer>
  <div class="footer-bottom">
    <p>© 2025 GlobeTrek Adventures (Pvt) Ltd. All rights reserved.</p>
  </div>
</footer>

<!-- LOGIN MODAL -->
<div class="modal-overlay" id="loginModal">
  <div class="modal">
    <button class="modal-close" onclick="closeModal()">✕</button>
    <h2>Welcome Back!</h2>
    <p>Login to manage bookings & save favorites</p>
    <div class="modal-btns">
      <a href="login.html" class="modal-btn-login">Login</a>
      <span style="width: 8px;"></span>
      <a href="register.html" class="modal-btn-register">Register Now</a>
    </div>
  </div>
</div>

<script>
  // PACKAGE FILTERING
  const searchInput = document.getElementById('package-search');
  const packagesGrid = document.getElementById('packages-grid');
  const packageCards = document.querySelectorAll('.pkg-card');

  if (searchInput) {
    searchInput.addEventListener('keyup', function() {
      const filter = this.value.toLowerCase();
      packageCards.forEach(card => {
        const name = card.getAttribute('data-name') || '';
        if (name.includes(filter)) {
          card.style.display = 'block';
        } else {
          card.style.display = 'none';
        }
      });
    });
  }

  // SEARCH FUNCTION
  function handleSearch() {
    const destination = document.getElementById('dest-input').value;
    const travelDate = document.getElementById('travel-date').value;
    const travelers = document.getElementById('travelers-select').value;
    
    if (!destination.trim()) {
      alert('Please enter a destination to search for packages.');
      return;
    }
    
    // Simple redirection to packages page with query
    let searchUrl = `package.php?search=${encodeURIComponent(destination)}`;
    if (travelDate) searchUrl += `&date=${travelDate}`;
    if (travelers) searchUrl += `&travelers=${encodeURIComponent(travelers)}`;
    
    alert(`🔍 Searching for "${destination}" packages...\n\n(You will be redirected to our packages page where you can see all matching tours.)`);
    window.location.href = searchUrl;
  }

  // BOOKING FUNCTION
  function goToBooking(packageName, price) {
    const modal = document.getElementById('loginModal');
    if (modal) {
      modal.classList.add('open');
      // Store selected package info if needed
      sessionStorage.setItem('selectedPackage', packageName);
      sessionStorage.setItem('packagePrice', price);
    }
  }

  // MODAL CLOSE
  function closeModal() {
    const modal = document.getElementById('loginModal');
    if (modal) modal.classList.remove('open');
  }

  // Contact Form Submission
  function handleContactSubmit(event) {
    event.preventDefault();
    const name = document.getElementById('name').value;
    const email = document.getElementById('email').value;
    const phone = document.getElementById('phone').value;
    const message = document.getElementById('message').value;
    
    if (name && email && message) {
      alert(`Thank you ${name}! Your message has been sent. We'll get back to you within 24 hours.`);
      document.getElementById('contactForm').reset();
    } else {
      alert('Please fill all required fields.');
    }
  }

  // Newsletter Subscription
  function handleNewsletter(event) {
    event.preventDefault();
    const email = document.getElementById('newsletter-email').value;
    if (email && email.includes('@')) {
      alert(`Success! ${email} has been subscribed to our newsletter. Get ready for amazing travel inspiration.`);
      document.getElementById('newsletter-email').value = '';
    } else {
      alert('Please enter a valid email address.');
    }
  }

  // Close modal when clicking outside
  window.onclick = function(event) {
    const modal = document.getElementById('loginModal');
    if (event.target === modal) {
      closeModal();
    }
  }
  
  // Smooth scrolling 
  document.querySelectorAll('a[href^="#"]').forEach(anchor => {
    anchor.addEventListener('click', function(e) {
      const href = this.getAttribute('href');
      if (href === "#" || href === "") return;
      const target = document.querySelector(href);
      if (target) {
        e.preventDefault();
        target.scrollIntoView({ behavior: 'smooth' });
      }
    });
  });
</script>

</body>
</html>
   