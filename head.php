<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<?php if (isset($use_favicon) && $use_favicon): ?>
<link rel="icon" type="<?php echo $favicon_mime; ?>" href="<?php echo $favicon_path; ?>">
<?php endif; ?>
<link href="assets/css/google-fonts.css" rel="stylesheet">
<link href="assets/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="assets/css/bootstrap-icons.min.css">
<link rel="stylesheet" href="assets/css/swiper-bundle.min.css" />
<script src="assets/js/sweetalert2.all.min.js"></script>
<?php if (($site_settings['enable_translator'] ?? '0') === '1'): ?>
<link rel="stylesheet" href="assets/css/gtranslate.css">
<?php endif; ?>
<style>
    :root { 
        --primary: #b00000; /* Cargo Crimson Red */
        --primary-dark: #8c0000;
        --secondary: #daa520; /* Cargo Gold */
        --dark: #ffffff; /* Override dark with White */
        --darker: #f8f9fa; /* Override darker with Light Gray */
        --light: #333333; /* Override light with Dark Text */
        --text-muted: #6c757d; 
    }
    
    html, body { 
        font-family: 'Outfit', sans-serif; 
        background-color: var(--dark); 
        color: var(--light); 
        overflow-x: hidden; 
    }
    
    h1, h2, h3, h4, h5, h6, .serif-font { 
        font-family: 'Playfair Display', serif; 
        color: #1e3c72; /* Dark Blue for headings (Cargo feel) */
    }
    
    /* Global Overrides for light theme */
    .text-white, .text-light { color: #fff !important; }
    .bg-dark { background-color: #333 !important; }
    
    /* Navbar Premium modified for Cargo */
    .navbar-premium { 
        background: #b00000 !important; 
        box-shadow: 0 4px 15px rgba(0,0,0,0.15); 
        border-bottom: none !important; 
        padding: 1.2rem 0; 
        transition: all 0.3s ease; 
    }
    .navbar-premium * {
        -webkit-tap-highlight-color: transparent !important;
    }
    .navbar-premium .navbar-toggler,
    .navbar-premium .navbar-toggler:focus,
    .navbar-premium .navbar-toggler:active,
    .navbar-premium .navbar-toggler:hover {
        border: none !important;
        outline: none !important;
        box-shadow: none !important;
        background: transparent !important;
    }
    .navbar-premium .nav-link { 
        color: #fff !important; 
        font-weight: 500; 
        font-size: 0.95rem; 
        text-transform: uppercase; 
        letter-spacing: 1px; 
        padding: 0.5rem 1.2rem; 
        transition: color 0.3s; 
        position: relative;
        outline: none !important;
        border: none !important;
        box-shadow: none !important;
    }
    .navbar-premium .nav-link:focus,
    .navbar-premium .nav-link:active,
    .navbar-premium .dropdown-toggle:focus,
    .navbar-premium .dropdown-toggle:active {
        outline: none !important;
        border: none !important;
        box-shadow: none !important;
    }
    .navbar-premium .nav-link::after {
        content: '';
        position: absolute;
        width: 0;
        height: 2px;
        bottom: 0;
        left: 50%;
        background-color: var(--secondary);
        transition: all 0.3s ease-in-out;
        transform: translateX(-50%);
    }
    .navbar-premium .nav-link:hover::after, .navbar-premium .nav-link.active::after {
        width: calc(100% - 2.4rem); /* accounting for the padding */
    }
    .navbar-premium .nav-link:hover, .navbar-premium .nav-link.active { 
        color: var(--secondary) !important; 
    }
    
    /* Fix underline for dropdowns due to ::after conflict with bootstrap arrow */
    .navbar-premium .dropdown-toggle::after {
        display: inline-block !important;
        position: static !important;
        width: auto !important;
        height: auto !important;
        background-color: transparent !important;
        transform: none !important;
        margin-left: .255em !important;
        vertical-align: .255em !important;
        border-top: .3em solid !important;
        border-right: .3em solid transparent !important;
        border-bottom: 0 !important;
        border-left: .3em solid transparent !important;
    }
    .navbar-premium .dropdown-toggle::before {
        content: '';
        position: absolute;
        width: 0;
        height: 2px;
        bottom: 0;
        left: 50%;
        background-color: var(--secondary);
        transition: all 0.3s ease-in-out;
        transform: translateX(-50%);
    }
    .navbar-premium .dropdown-toggle:hover::before, .navbar-premium .dropdown-toggle.active::before {
        width: calc(100% - 2.8rem);
    }
    
    /* Dropdown Items styling */
    .navbar-premium .dropdown-item {
        color: rgba(255, 255, 255, 0.85) !important;
        transition: all 0.25s ease;
        background: transparent !important;
        outline: none !important;
        border: none !important;
        box-shadow: none !important;
    }
    .navbar-premium .dropdown-item:hover, 
    .navbar-premium .dropdown-item:focus,
    .navbar-premium .dropdown-item:active {
        background-color: rgba(194, 155, 87, 0.15) !important; /* Gold translucent tint */
        color: var(--secondary) !important; /* Gold text */
        outline: none !important;
        border: none !important;
        box-shadow: none !important;
    }
    .navbar-premium .dropdown-item i {
        transition: transform 0.25s ease;
    }
    .navbar-premium .dropdown-item:hover i {
        transform: translateX(3px);
    }
    
    /* Mobile Navbar Styling */
    @media (max-width: 991.98px) {
        .navbar-premium { 
            padding: 0.75rem 0 !important; 
        }
        .navbar-premium .navbar-collapse {
            background: rgba(15, 23, 42, 0.98) !important;
            backdrop-filter: blur(20px);
            padding: 20px 18px !important;
            border-radius: 12px;
            margin-top: 12px;
            border: 1px solid rgba(218, 165, 32, 0.3) !important;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.45);
            max-height: 80vh;
            overflow-y: auto;
        }
        .navbar-premium .nav-link {
            padding: 10px 14px !important;
            border-radius: 6px;
            margin: 2px 0;
            font-size: 0.9rem;
            border: none !important;
            outline: none !important;
            box-shadow: none !important;
        }
        .navbar-premium .nav-link::after,
        .navbar-premium .dropdown-toggle::before {
            display: none !important;
            content: none !important;
            border: none !important;
        }
        .navbar-premium .nav-link:hover, 
        .navbar-premium .nav-link:focus,
        .navbar-premium .nav-link:active,
        .navbar-premium .nav-link.active {
            background: rgba(218, 165, 32, 0.12) !important;
            border: none !important;
            outline: none !important;
            box-shadow: none !important;
        }
        .navbar-premium .dropdown-menu {
            background: rgba(255, 255, 255, 0.05) !important;
            border: none !important;
            box-shadow: none !important;
            border-radius: 8px !important;
            padding: 6px 8px !important;
            margin-top: 4px !important;
            margin-bottom: 6px !important;
        }
        .navbar-premium .dropdown-item {
            border-radius: 6px;
            border: none !important;
            outline: none !important;
            box-shadow: none !important;
        }
    }
    .btn-gold { 
        background: var(--secondary); 
        color: #fff; 
        border: none; 
        font-weight: 700; 
        padding: 0.8rem 2rem; 
        border-radius: 4px; 
        letter-spacing: 1px; 
        text-transform: uppercase; 
        transition: all 0.3s ease; 
    }
    .btn-gold:hover { 
        background: #b8860b; /* Darker Gold */
        color: #fff; 
        transform: translateY(-2px); 
        box-shadow: 0 10px 20px rgba(218, 165, 32, 0.3); 
    }
    .btn-outline-gold { 
        background: transparent; 
        border: 2px solid var(--secondary); 
        color: var(--secondary) !important; 
        font-weight: 600; 
        padding: 0.8rem 2rem; 
        border-radius: 4px; 
        letter-spacing: 1px; 
        text-transform: uppercase; 
        transition: all 0.3s ease; 
    }
    .btn-outline-gold:hover { 
        background: var(--secondary); 
        color: #fff !important; 
        transform: translateY(-2px);
        box-shadow: 0 10px 20px rgba(218, 165, 32, 0.3);
    }
    
    /* Content Boxes */
    .card, .service-card, .contact-card, .stat-card, .booking-card {
        box-shadow: 0 10px 30px rgba(0,0,0,0.05);
    }
    
    .service-title { color: #1e3c72 !important; }
    .service-desc { color: #6c757d !important; }
    
    /* Hero/Page Header */
    .hero-overlay {
        background: linear-gradient(to right, rgba(176, 0, 0, 0.9) 0%, rgba(176, 0, 0, 0.6) 50%, rgba(176, 0, 0, 0.2) 100%) !important;
    }
    .page-header {
        background: linear-gradient(135deg, #b00000, #ffc107) !important;
        border-bottom: none !important;
    }
    .page-header h1 { color: #fff !important; }
    .page-header p { color: #fff !important; font-weight: 500; }
    
    /* Footer */
    footer {
        background: #1e3c72 !important; /* Dark Blue */
        border-top: none !important;
        color: #f8f9fa;
    }
    footer h5, footer a.text-white { color: #daa520 !important; }
    footer p.text-muted, footer a.text-muted, footer ul.text-muted li { color: #ced4da !important; }
    footer a.text-muted:hover { color: #fff !important; }
    
    /* Hero Section Structure */
    .hero-section { 
        position: relative; 
        min-height: clamp(560px, 78vh, 760px); 
        display: flex; 
        align-items: center; 
        padding: 130px 0 70px; 
        overflow: hidden; 
    }
    .hero-bg { 
        position: absolute; 
        top: 0; 
        left: 0; 
        width: 100%; 
        height: 100%; 
        background-position: center center !important; 
        background-size: cover !important; 
        background-repeat: no-repeat !important; 
    }
    .hero-overlay { 
        position: absolute; 
        top: 0; 
        left: 0; 
        width: 100%; 
        height: 100%; 
        background: linear-gradient(to right, rgba(10, 0, 0, 0.75) 0%, rgba(10, 0, 0, 0.45) 50%, rgba(10, 0, 0, 0.25) 100%), linear-gradient(to bottom, rgba(0, 0, 0, 0.35) 0%, transparent 60%, rgba(0, 0, 0, 0.65) 100%) !important; 
    }
    .hero-content h1 { 
        font-family: 'Montserrat', sans-serif !important; 
        font-size: clamp(2rem, 5vw, 3.8rem); 
        font-weight: 800 !important; 
        line-height: 1.15; 
        margin-bottom: 20px; 
        color: #fff !important; 
        text-transform: uppercase; 
        letter-spacing: 1px; 
        text-shadow: 3px 3px 10px rgba(0, 0, 0, 0.6); 
    }
    @media (min-width: 992px) {
        .hero-section {
            min-height: clamp(580px, 80vh, 780px);
            padding: 140px 0 80px;
        }
        .hero-content h1 {
            font-size: 3.8rem;
        }
    }
    @media (max-width: 991.98px) {
        .hero-section {
            min-height: 520px;
            padding: 115px 0 55px;
        }
        .hero-content {
            text-align: center;
        }
        .hero-content .d-flex {
            justify-content: center;
        }
        .hero-content p {
            margin-left: auto;
            margin-right: auto;
        }
    }
    @media (max-width: 575.98px) {
        .hero-section {
            min-height: 460px;
            padding: 95px 0 40px;
        }
        .hero-content h1 {
            font-size: 1.95rem !important;
            margin-bottom: 14px;
            letter-spacing: 0.5px;
        }
        .hero-content p {
            font-size: 0.95rem !important;
            margin-bottom: 20px !important;
            line-height: 1.55 !important;
        }
        .hero-content .btn {
            padding: 0.7rem 1.3rem;
            font-size: 0.82rem;
            letter-spacing: 0.5px;
        }
    }
    .hero-content p { 
        font-family: 'Work Sans', sans-serif !important; 
        font-size: 1.15rem; 
        color: rgba(255,255,255,0.95) !important; 
        max-width: 600px; 
        margin-bottom: 35px; 
        text-shadow: 2px 2px 8px rgba(0, 0, 0, 0.5); 
    }

    /* Premium Ken Burns Animation for Slider */
    @keyframes kenburns { 0% { transform: scale(1); } 100% { transform: scale(1.05); } }
    .carousel-item .hero-bg { transform: scale(1); transition: transform 0.5s ease; }
    .carousel-item.active .hero-bg { animation: kenburns 12s ease-out forwards; }
    
    /* Global Section Padding and Titles */
    .section-padding { padding: 80px 0; }
    .section-title { margin-bottom: 50px; }
    .section-title p { color: var(--secondary); font-size: 1rem; text-transform: uppercase; letter-spacing: 3px; font-weight: 600; margin-bottom: 8px; }
    .section-title h2 { font-family: 'Montserrat', sans-serif; font-size: 2.3rem; font-weight: 800; text-transform: uppercase; letter-spacing: 2px; color: var(--primary-dark); }
    
    @media (max-width: 768px) {
        .section-padding { padding: 45px 0 !important; }
        .section-title { margin-bottom: 30px !important; }
        .section-title h2 { font-size: 1.65rem !important; letter-spacing: 1px !important; }
        .section-title p { font-size: 0.82rem !important; letter-spacing: 1.5px !important; }
    }
    
    /* Universal Page Header & Heroes */
    .page-header, .about-hero, .services-hero, .performers-hero, .donation-hero, .contact-hero, .booking-hero, .book-hero, .fancards-hero, .lookup-hero, .ticket-view-hero, .terms-hero, .privacy-hero, .tickets-hero, .premium-hero { 
        background: url('assets/img/head_bg.jpg') center/cover no-repeat; 
        padding: 120px 0 65px; 
        text-align: center; 
        color: #fff; 
        position: relative; 
        overflow: hidden; 
    }
    .page-header::before, .about-hero::before, .services-hero::before, .performers-hero::before, .donation-hero::before, .contact-hero::before, .booking-hero::before, .book-hero::before, .fancards-hero::before, .lookup-hero::before, .ticket-view-hero::before, .terms-hero::before, .privacy-hero::before, .tickets-hero::before {
        content: '';
        position: absolute;
        top: 0; left: 0; width: 100%; height: 100%;
        background: linear-gradient(135deg, rgba(176,0,0,0.92) 0%, rgba(30,60,114,0.85) 100%);
        z-index: 1;
    }
    .page-header .container, .about-hero .container, .services-hero .container, .performers-hero .container, .donation-hero .container, .contact-hero .container, .booking-hero .container, .book-hero .container, .fancards-hero .container, .lookup-hero .container, .ticket-view-hero .container, .terms-hero .container, .privacy-hero .container, .tickets-hero .container, .premium-hero .container { 
        position: relative; 
        z-index: 2; 
    }
    .page-header h1, .about-hero h1, .services-hero h1, .performers-hero h1, .donation-hero h1, .contact-hero h1, .booking-hero h1, .book-hero h1, .fancards-hero h1, .lookup-hero h1, .ticket-view-hero h1, .terms-hero h1, .privacy-hero h1, .tickets-hero h1, .premium-hero h1 { 
        font-size: clamp(2rem, 5vw, 3.5rem) !important; 
        font-weight: 800 !important; 
        margin-bottom: 10px !important; 
        font-family: 'Playfair Display', serif !important; 
        letter-spacing: -0.5px; 
        color: #fff !important;
        line-height: 1.2 !important;
    }
    .page-header p, .about-hero p, .services-hero p, .performers-hero p, .donation-hero p, .contact-hero p, .booking-hero p, .book-hero p, .fancards-hero p, .lookup-hero p, .ticket-view-hero p, .terms-hero p, .privacy-hero p, .tickets-hero p, .premium-hero p { 
        font-size: 1.15rem; 
        color: rgba(255,255,255,0.9); 
        max-width: 720px;
        margin-left: auto;
        margin-right: auto;
    }

    @media (max-width: 767.98px) {
        .page-header, .about-hero, .services-hero, .performers-hero, .donation-hero, .contact-hero, .booking-hero, .book-hero, .fancards-hero, .lookup-hero, .ticket-view-hero, .terms-hero, .privacy-hero, .tickets-hero, .premium-hero { 
            padding: 85px 15px 35px !important; 
        }
        .page-header h1, .about-hero h1, .services-hero h1, .performers-hero h1, .donation-hero h1, .contact-hero h1, .booking-hero h1, .book-hero h1, .fancards-hero h1, .lookup-hero h1, .ticket-view-hero h1, .terms-hero h1, .privacy-hero h1, .tickets-hero h1, .premium-hero h1 { 
            font-size: 1.85rem !important; 
            margin-bottom: 8px !important; 
            letter-spacing: 0 !important;
        }
        .page-header p, .about-hero p, .services-hero p, .performers-hero p, .donation-hero p, .contact-hero p, .booking-hero p, .book-hero p, .fancards-hero p, .lookup-hero p, .ticket-view-hero p, .terms-hero p, .privacy-hero p, .tickets-hero p, .premium-hero p { 
            font-size: 0.92rem !important; 
            line-height: 1.5 !important; 
            margin-bottom: 0 !important;
        }
        .experiences-section, .process-section, .standards-section, .urgent-support-section, .services-section, .about-content, .terms-section, .privacy-section, .contact-section {
            padding: 40px 0 !important;
            margin-top: 0 !important;
            margin-bottom: 0 !important;
        }
    }
    @media (max-width: 575.98px) {
        .page-header, .about-hero, .services-hero, .performers-hero, .donation-hero, .contact-hero, .booking-hero, .book-hero, .lookup-hero, .ticket-view-hero, .terms-hero, .privacy-hero, .tickets-hero, .premium-hero { 
            padding: 80px 12px 30px !important; 
        }
        .page-header h1, .about-hero h1, .services-hero h1, .performers-hero h1, .donation-hero h1, .contact-hero h1, .booking-hero h1, .book-hero h1, .lookup-hero h1, .ticket-view-hero h1, .terms-hero h1, .privacy-hero h1, .tickets-hero h1, .premium-hero h1 { 
            font-size: 1.65rem !important; 
        }
    }

    /* About Image & Stats */
    .about-image { width: 100%; height: 500px; object-fit: cover; border-radius: 12px; box-shadow: 0 20px 40px rgba(0,0,0,0.1); }
    .stat-card { background: #fff; padding: 30px; border-radius: 12px; text-align: center; border: 1px solid rgba(0,0,0,0.05); box-shadow: 0 10px 30px rgba(0,0,0,0.05); transition: transform 0.3s; }
    .stat-card:hover { transform: translateY(-5px); box-shadow: 0 15px 40px rgba(0,0,0,0.1); border-color: rgba(218, 165, 32, 0.3); }
    .stat-number { font-size: 2.5rem; font-weight: 700; color: var(--secondary); margin-bottom: 5px; font-family: 'Playfair Display', serif; }
    .stat-label { font-size: 0.95rem; font-weight: 600; text-transform: uppercase; letter-spacing: 1px; color: #6c757d; }

    /* Celebrity Cards Structure */
    .cel-card { border-radius: 12px; overflow: hidden; transition: all 0.4s ease; border: none; height: 420px; position: relative; background: #000; box-shadow: 0 10px 30px rgba(0,0,0,0.1); display: flex; flex-direction: column; justify-content: flex-end; }
    .cel-card:hover { transform: translateY(-10px); box-shadow: 0 20px 40px rgba(0,0,0,0.2); }
    .cel-img-wrapper { position: absolute; top: 0; left: 0; height: 100%; width: 100%; z-index: 0; }
    .cel-img { width: 100%; height: 100%; object-fit: cover; object-position: center top; transition: transform 0.7s ease; }
    .cel-card:hover .cel-img { transform: scale(1.08); }
    .cel-badge { position: absolute; top: 20px; right: 20px; background: #1877F2; color: #fff; padding: 5px 12px; border-radius: 30px; font-size: 0.8rem; font-weight: 700; z-index: 2; }
    .cel-overlay { position: absolute; bottom: 0; left: 0; width: 100%; height: 75%; background: linear-gradient(to top, rgba(90, 10, 10, 0.95) 0%, rgba(90, 10, 10, 0.4) 50%, transparent 100%); z-index: 1; }
    .cel-info { padding: 25px; position: relative; z-index: 2; background: transparent; display: flex; flex-direction: column; justify-content: flex-end; }
    .cel-name { font-size: 1.5rem; font-weight: 800; margin-bottom: 5px; color: #ffffff !important; letter-spacing: 0.5px; }
    .cel-price { color: #fff; font-size: 1rem; font-weight: 600; margin-bottom: 10px; opacity: 0.9; }
    .cel-desc { color: #f0f0f0 !important; font-size: 0.9rem; margin-bottom: 20px; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; opacity: 0.9; }
    
    @media (max-width: 575.98px) {
        .about-image { height: 280px !important; }
        .stat-card { padding: 18px 12px !important; margin-bottom: 12px; }
        .stat-number { font-size: 1.85rem !important; }
        .stat-label { font-size: 0.78rem !important; }
        .cel-card { height: 370px !important; }
        .cel-info { padding: 16px !important; }
        .cel-name { font-size: 1.25rem !important; }
        .cel-price { font-size: 0.9rem !important; margin-bottom: 6px !important; }
        .cel-desc { font-size: 0.82rem !important; margin-bottom: 14px !important; }
    }

    /* Form controls (Book/Contact) */
    .form-control, .form-select { background: #fff; border: 1px solid #ced4da; color: #495057; padding: 12px 20px; }
    .form-control:focus, .form-select:focus { background: #fff; border-color: var(--primary); box-shadow: 0 0 0 0.25rem rgba(176, 0, 0, 0.25); color: #495057; }
    .form-label { color: #495057; font-weight: 600; text-transform: uppercase; font-size: 0.85rem; letter-spacing: 1px; }

    /* Contact Details */
    .contact-section { padding: 80px 0; }
    .contact-card { background: #fff !important; border: 1px solid #dee2e6 !important; border-radius: 12px; padding: 40px; box-shadow: 0 10px 30px rgba(0,0,0,0.05); }
    .contact-icon { font-size: 2.5rem; color: var(--secondary); margin-bottom: 20px; display: inline-block; }

    /* Booking specific */
    .booking-card { background: #fff !important; border: 1px solid #dee2e6 !important; border-radius: 12px; padding: 40px; box-shadow: 0 10px 30px rgba(0,0,0,0.05); }

    /* Track Booking */
    .search-box { max-width: 600px; margin: 0 auto; display: flex; box-shadow: 0 10px 30px rgba(0,0,0,0.1); border-radius: 50px; overflow: hidden; border: 1px solid rgba(0,0,0,0.1); }
    .search-input { flex: 1; padding: 15px 25px; border: none; outline: none; font-size: 1.1rem; background: #fff; color: #333; }
    .search-input:focus { background: #fafafa; }
    .search-btn { padding: 15px 30px; background: var(--secondary); border: none; font-weight: 700; color: #fff; cursor: pointer; transition: all 0.3s; text-transform: uppercase; }
    .search-btn:hover { background: #b8860b; color: #fff; }
    .booking-details-card { background: #fff !important; border: 1px solid #dee2e6 !important; border-radius: 12px; padding: 40px; margin-top: 50px; box-shadow: 0 10px 30px rgba(0,0,0,0.05); }
    .status-badge { display: inline-block; padding: 8px 16px; border-radius: 30px; font-weight: 700; font-size: 0.9rem; text-transform: uppercase; letter-spacing: 1px; }
    .status-pending { background: rgba(243, 156, 18, 0.1); color: #f39c12; border: 1px solid rgba(243, 156, 18, 0.3); }
    .status-accepted { background: rgba(46, 204, 113, 0.1); color: #2ecc71; border: 1px solid rgba(46, 204, 113, 0.3); }
    .status-cancelled { background: rgba(231, 76, 60, 0.1); color: #e74c3c; border: 1px solid rgba(231, 76, 60, 0.3); }
    
    /* Payment Page Buttons */
    .btn-bank-transfer { background: var(--secondary); color: #fff; font-weight: bold; border: none; }
    .btn-bank-transfer:hover { background: #b8860b; color: #fff; }
    .btn-crypto-payments { background: #f8f9fa; color: #333; font-weight: bold; border: 1px solid var(--secondary); }
    .btn-crypto-payments:hover { background: var(--secondary); color: #fff; }
    .btn-gift-cards { background: #e9ecef; color: #333; font-weight: bold; border: 1px solid var(--secondary); }
    .btn-gift-cards:hover { background: var(--secondary); color: #fff; }
    /* About Page Styles */
    .about-content { padding: 100px 0; }
    .about-image { width: 100%; border-radius: 12px; box-shadow: 0 20px 40px rgba(0,0,0,0.1); }
    .stat-card { background: #fff !important; border: 1px solid #dee2e6 !important; border-radius: 8px; padding: 30px; text-align: center; box-shadow: 0 10px 30px rgba(0,0,0,0.05); }
    .stat-number { font-size: 3rem; font-weight: 700; color: var(--secondary); margin-bottom: 5px; font-family: 'Playfair Display', serif; }
    .stat-label { color: #6c757d; font-weight: 600; text-transform: uppercase; letter-spacing: 1px; font-size: 0.9rem; }

    /* Services Page Styles */
    .services-section { padding: 100px 0; }
    .service-card { background: #fff !important; border: 1px solid #dee2e6 !important; border-radius: 12px; padding: 40px 30px; height: 100%; text-align: center; transition: all 0.3s ease; box-shadow: 0 10px 30px rgba(0,0,0,0.05); }
    .service-card:hover { transform: translateY(-10px); box-shadow: 0 20px 40px rgba(0,0,0,0.1); border-color: rgba(218, 165, 32, 0.3) !important; }
    .service-icon { font-size: 3.5rem; color: var(--secondary); margin-bottom: 25px; display: inline-block; }
    .service-title { font-size: 1.5rem; font-weight: 700; margin-bottom: 15px; color: #1e3c72; }
    .service-desc { color: #6c757d; font-size: 0.95rem; line-height: 1.6; }
    /* Event Cards - Permanently Active/Hovered Aesthetic */
    .event-card { position: relative; border-radius: 12px; overflow: hidden; height: 350px; display: flex; align-items: flex-end; box-shadow: 0 20px 45px rgba(176,0,0,0.25); transform: translateY(-5px); border-bottom: 4px solid var(--secondary); transition: all 0.5s cubic-bezier(0.4, 0, 0.2, 1); cursor: pointer; }
    .event-card:hover { transform: translateY(-12px) scale(1.02); box-shadow: 0 30px 60px rgba(176,0,0,0.4); border-bottom-color: #fff; }
    .event-img { position: absolute; top: 0; left: 0; width: 100%; height: 100%; object-fit: cover; z-index: 1; transform: scale(1.08); filter: brightness(0.85); transition: transform 0.8s cubic-bezier(0.4, 0, 0.2, 1), filter 0.5s ease; }
    .event-card:hover .event-img { transform: scale(1.15) rotate(1deg); filter: brightness(0.75); }
    .event-overlay { position: absolute; top: 0; left: 0; width: 100%; height: 100%; background: linear-gradient(to top, rgba(176, 0, 0, 0.95) 0%, rgba(15, 23, 42, 0.7) 50%, transparent 100%); z-index: 2; opacity: 1; transition: opacity 0.5s ease, background 0.5s ease; }
    .event-card:hover .event-overlay { background: linear-gradient(to top, rgba(140, 0, 0, 0.98) 0%, rgba(15, 23, 42, 0.8) 50%, transparent 100%); }
    .event-content { position: relative; z-index: 3; padding: 30px; width: 100%; transform: translateY(-5px); transition: transform 0.5s cubic-bezier(0.4, 0, 0.2, 1); }
    .event-card:hover .event-content { transform: translateY(-8px); }
    .event-date { background: var(--secondary); color: var(--primary-dark); display: inline-block; padding: 6px 15px; border-radius: 30px; font-weight: 800; font-size: 0.8rem; margin-bottom: 12px; text-transform: uppercase; letter-spacing: 2px; box-shadow: 0 4px 10px rgba(218, 165, 32, 0.4); }
    .event-title { color: #fff; font-size: 1.8rem; font-weight: 700; font-family: 'Playfair Display', serif; margin-bottom: 12px; line-height: 1.2; text-shadow: 0 2px 4px rgba(0,0,0,0.5); }
    .event-location { color: rgba(255, 255, 255, 0.95); font-size: 0.95rem; display: flex; align-items: center; margin-bottom: 15px; font-weight: 600; }
    .event-btn-container { margin-top: 15px; transition: transform 0.5s cubic-bezier(0.4, 0, 0.2, 1); }
    .event-book-btn { background: var(--secondary); color: var(--primary-dark); border: 1px solid var(--secondary); padding: 8px 20px; font-size: 0.85rem; font-weight: 700; text-transform: uppercase; letter-spacing: 1px; border-radius: 6px; transition: all 0.3s; display: inline-block; text-decoration: none; box-shadow: 0 0 15px rgba(218, 165, 32, 0.5); }
    .event-book-btn:hover { background: #fff; color: var(--primary-dark); border-color: #fff; text-decoration: none; box-shadow: 0 0 20px rgba(255, 255, 255, 0.8); }


    /* Custom Carousel Controls (like cargo project) */
    #heroCarousel .carousel-indicators {
        bottom: 30px;
    }
    #heroCarousel .carousel-indicators button {
        width: 25px;
        height: 3px;
        background: var(--secondary); /* Cargo Gold */
        opacity: 0.4;
        margin: 0 4px !important;
        border-radius: 1.5px;
        transition: all 0.5s cubic-bezier(0.25, 0.46, 0.45, 0.94);
        border: none;
    }
    #heroCarousel .carousel-indicators button:hover {
        opacity: 0.9;
        transform: scale(1.2);
    }
    #heroCarousel .carousel-indicators .active {
        background: var(--primary); /* Cargo Crimson Red */
        opacity: 1;
        width: 45px;
        height: 3px;
        border-radius: 1.5px;
    }
    #heroCarousel .carousel-control-prev-icon,
    #heroCarousel .carousel-control-next-icon {
        width: 3rem;
        height: 3rem;
        background-color: rgba(176, 0, 0, 0.6);
        border-radius: 50%;
        transition: all 0.3s ease;
        border: 2px solid rgba(218, 165, 32, 0.4);
    }
    #heroCarousel .carousel-control-prev-icon:hover,
    #heroCarousel .carousel-control-next-icon:hover {
        background-color: rgba(176, 0, 0, 0.9);
        border-color: var(--secondary);
        transform: scale(1.1);
    }
    #heroCarousel .carousel-control-prev,
    #heroCarousel .carousel-control-next {
        opacity: 0.7;
        transition: opacity 0.3s ease;
        width: auto;
        display: none;
    }
    #heroCarousel .carousel-control-prev {
        left: 30px;
    }
    #heroCarousel .carousel-control-next {
        right: 30px;
    }
    @media (min-width: 768px) {
        #heroCarousel .carousel-control-prev,
        #heroCarousel .carousel-control-next {
            display: flex;
        }
    }
    @media (min-width: 992px) {
        .hero-content {
            padding-left: 90px;
            padding-right: 90px;
        }
    }
    #heroCarousel .carousel-control-prev:hover,
    #heroCarousel .carousel-control-next:hover {
        opacity: 1;
    }

    /* Slide Animation Effects */
    #heroCarousel .carousel-item .hero-content h1,
    #heroCarousel .carousel-item .hero-content p,
    #heroCarousel .carousel-item .hero-content .btn,
    #heroCarousel .carousel-item .hero-content span.rounded-pill {
        opacity: 0;
        transform: translateY(30px);
    }

    #heroCarousel .carousel-item.active .hero-content h1 {
        animation: slideFadeUp 0.8s cubic-bezier(0.25, 0.46, 0.45, 0.94) forwards;
        animation-delay: 0.2s;
    }
    #heroCarousel .carousel-item.active .hero-content p {
        animation: slideFadeUp 0.8s cubic-bezier(0.25, 0.46, 0.45, 0.94) forwards;
        animation-delay: 0.4s;
    }
    #heroCarousel .carousel-item.active .hero-content .btn {
        animation: slideFadeUp 0.8s cubic-bezier(0.25, 0.46, 0.45, 0.94) forwards;
        animation-delay: 0.6s;
    }
    #heroCarousel .carousel-item.active .hero-content span.rounded-pill {
        animation: slideFadeUp 0.8s cubic-bezier(0.25, 0.46, 0.45, 0.94) forwards;
        animation-delay: 0.0s;
    }

    @keyframes slideFadeUp {
        from {
            opacity: 0;
            transform: translateY(30px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    /* Premium Stats & CTA Section */
    .cta-stats-section {
        background: linear-gradient(135deg, #8c0000 0%, #b00000 50%, #4a0000 100%);
        position: relative;
        padding: 90px 0 130px;
        text-align: center;
        overflow: hidden;
    }
    .cta-stats-section::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: radial-gradient(circle at 50% 30%, rgba(255, 255, 255, 0.08), transparent 70%);
        pointer-events: none;
    }
    .cta-stats-section h2 {
        font-family: 'Montserrat', sans-serif !important;
        font-size: 2.2rem;
        font-weight: 800;
        color: #fff;
        letter-spacing: 1.5px;
        text-transform: uppercase;
        margin-bottom: 20px;
        text-shadow: 0 4px 15px rgba(0, 0, 0, 0.4);
    }
    @media (min-width: 992px) {
        .cta-stats-section h2 {
            font-size: 3rem;
        }
    }
    .cta-stats-section p {
        font-family: 'Work Sans', sans-serif !important;
        font-size: 1.15rem;
        color: rgba(255, 255, 255, 0.9);
        max-width: 700px;
        margin: 0 auto 35px;
        text-shadow: 0 2px 8px rgba(0, 0, 0, 0.2);
    }
    .stats-container-wrapper {
        margin-top: -40px;
        position: relative;
        z-index: 10;
    }
    @media (min-width: 768px) {
        .stats-container-wrapper {
            margin-top: -70px;
        }
    }
    .stat-card-premium {
        background: #ffffff;
        border-radius: 16px;
        padding: 40px 30px;
        box-shadow: 0 15px 40px rgba(0, 0, 0, 0.08);
        border: 1px solid rgba(218, 165, 32, 0.15);
        transition: all 0.4s ease;
        text-align: center;
        height: 100%;
    }
    .stat-card-premium:hover {
        transform: translateY(-8px);
        box-shadow: 0 25px 50px rgba(0, 0, 0, 0.15);
        border-color: var(--secondary);
    }
    .stat-number {
        font-family: 'Montserrat', sans-serif !important;
        font-size: 3rem;
        font-weight: 800;
        line-height: 1;
        margin-bottom: 12px;
    }
    .stat-number.primary-color {
        color: var(--primary);
    }
    .stat-number.secondary-color {
        color: var(--secondary);
    }
    .stat-label {
        font-family: 'Montserrat', sans-serif !important;
        font-size: 0.85rem;
        font-weight: 700;
        color: #555;
        letter-spacing: 1.5px;
        text-transform: uppercase;
        margin-bottom: 10px;
    }
    .stat-stars {
        color: #ffc107;
        font-size: 0.95rem;
    }
    /* Premium Contact Page Styles */
    .contact-card-premium {
        background: #ffffff;
        border: none;
        border-radius: 20px;
        padding: 40px 30px;
        box-shadow: 0 15px 35px rgba(0, 0, 0, 0.05);
        transition: all 0.4s ease;
        height: 100%;
        position: relative;
        overflow: hidden;
    }
    .contact-card-premium:hover {
        transform: translateY(-8px);
        box-shadow: 0 25px 45px rgba(0, 0, 0, 0.08);
    }
    .contact-icon-wrapper {
        width: 70px;
        height: 70px;
        background: rgba(218, 165, 32, 0.1);
        color: var(--secondary);
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.8rem;
        margin: 0 auto 25px;
        transition: all 0.4s ease;
    }
    .contact-card-premium:hover .contact-icon-wrapper {
        background: var(--secondary);
        color: #ffffff;
        transform: scale(1.1) rotate(5deg);
    }
    .form-premium .form-control {
        border: none;
        border-bottom: 2px solid #e2e8f0;
        border-radius: 0;
        padding: 15px 5px;
        background: transparent;
        font-size: 1.05rem;
        transition: all 0.3s ease;
        box-shadow: none;
    }
    .form-premium .form-control:focus {
        border-bottom-color: var(--secondary);
        background: transparent;
    }
    .form-premium .form-label {
        font-family: 'Outfit', sans-serif;
        font-weight: 600;
        color: #1e3c72;
        letter-spacing: 0.5px;
        margin-bottom: 0;
    }
    .map-container {
        border-radius: 20px;
        overflow: hidden;
        box-shadow: 0 15px 35px rgba(0, 0, 0, 0.05);
        height: 450px;
        margin-top: 30px;
    }
    /* Edge-Attached Social Sidebar */
    .floating-social-sidebar {
        position: fixed;
        left: 0;
        top: 50%;
        transform: translateY(-50%);
        z-index: 1000;
        display: flex;
        flex-direction: column;
        gap: 0;
    }
    .social-bubble {
        width: 45px;
        height: 45px;
        border-radius: 0 5px 5px 0;
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-size: 1.4rem;
        box-shadow: 2px 2px 10px rgba(0,0,0,0.1);
        transition: all 0.3s ease;
        text-decoration: none;
        margin-bottom: 2px;
        overflow: hidden;
        position: relative;
    }
    .social-bubble i {
        position: absolute;
        transition: transform 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
    }
    .social-bubble i:not(.hover-icon) {
        transform: translateY(0);
    }
    .social-bubble i.hover-icon {
        transform: translateY(150%);
    }
    .social-bubble:hover {
        background-blend-mode: multiply;
        filter: brightness(0.9);
        color: white;
    }
    .social-bubble:hover i:not(.hover-icon) {
        transform: translateY(-150%);
    }
    .social-bubble:hover i.hover-icon {
        transform: translateY(0);
    }
    .whatsapp-bubble {
        background-color: #25D366;
    }
    .telegram-bubble {
        background-color: #0088cc;
    }
    .social-bubble i {
        line-height: 1;
    }

    /* Mobile UI Overflow and Spacing Hardening */
    @media (max-width: 767.98px) {
        .contact-card-premium, 
        .policy-card, 
        .terms-card, 
        .donation-form-wrapper, 
        .form-premium-card, 
        .lookup-card {
            padding: 1.5rem 1.25rem !important;
            border-radius: 16px !important;
            max-width: 100% !important;
        }
        
        .pagination {
            flex-wrap: wrap !important;
            gap: 4px !important;
            padding: 6px 12px !important;
            max-width: 100% !important;
            border-radius: 30px !important;
        }
        
        .pagination .page-link {
            width: 36px !important;
            height: 36px !important;
            font-size: 0.85rem !important;
        }
        
        .amount-btn {
            padding: 0.75rem 0.25rem !important;
            font-size: 0.95rem !important;
        }

        .confidence-cta {
            margin: 2rem auto 4rem !important;
            max-width: 100% !important;
            padding: 1.5rem 1rem !important;
        }
        
        .table-responsive {
            overflow-x: auto !important;
            -webkit-overflow-scrolling: touch !important;
            width: 100% !important;
        }
        
        .booking-row {
            padding: 1.25rem !important;
        }
        
        .btn-skew {
            letter-spacing: 2px !important;
            padding: 8px 18px !important;
            font-size: 12px !important;
        }
    }
</style>

<?php if ((($site_settings['disable_right_click_copy'] ?? '0') === '1') && strpos($_SERVER['SCRIPT_NAME'], '/admin/') === false): ?>
<style id="anti-copy-style">
    *:not(input):not(textarea):not(select):not([contenteditable="true"]) {
        -webkit-user-select: none !important;
        -moz-user-select: none !important;
        -ms-user-select: none !important;
        user-select: none !important;
    }
    img, picture, svg, video {
        -webkit-user-drag: none !important;
        -khtml-user-drag: none !important;
        -moz-user-drag: none !important;
        -o-user-drag: none !important;
        user-drag: none !important;
        -webkit-touch-callout: none !important;
    }
    input, textarea, select {
        -webkit-user-select: auto !important;
        -moz-user-select: auto !important;
        -ms-user-select: auto !important;
        user-select: auto !important;
    }
    #anti-copy-toast {
        position: fixed;
        bottom: 30px;
        left: 50%;
        transform: translateX(-50%) translateY(50px);
        background: rgba(18, 18, 18, 0.95);
        color: #ffffff;
        padding: 12px 24px;
        border-radius: 50px;
        font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
        font-size: 13px;
        font-weight: 600;
        letter-spacing: 0.3px;
        box-shadow: 0 10px 30px rgba(0,0,0,0.4), 0 0 0 1px rgba(255,255,255,0.1);
        backdrop-filter: blur(10px);
        -webkit-backdrop-filter: blur(10px);
        opacity: 0;
        pointer-events: none;
        transition: all 0.3s cubic-bezier(0.16, 1, 0.3, 1);
        z-index: 999999;
        display: flex;
        align-items: center;
        gap: 10px;
    }
    #anti-copy-toast.show {
        opacity: 1;
        transform: translateX(-50%) translateY(0);
    }
    #anti-copy-toast svg {
        width: 16px;
        height: 16px;
        fill: #e63946;
        flex-shrink: 0;
    }
</style>
<script>
(function() {
    window.__copy_protection_active = true;
    var toastTimeout = null;
    var lastToastTime = 0;

    function showProtectionToast(message) {
        var now = Date.now();
        if (now - lastToastTime < 1500) return; // Prevent toast spam
        lastToastTime = now;

        var toast = document.getElementById('anti-copy-toast');
        if (!toast) {
            toast = document.createElement('div');
            toast.id = 'anti-copy-toast';
            toast.innerHTML = '<svg viewBox="0 0 24 24"><path d="M18 8h-1V6c0-2.76-2.24-5-5-5S7 3.24 7 6v2H6c-1.1 0-2 .9-2 2v10c0 1.1.9 2 2 2h12c1.1 0 2-.9 2-2V10c0-1.1-.9-2-2-2zm-6 9c-1.1 0-2-.9-2-2s.9-2 2-2 2 .9 2 2-.9 2-2 2zm3.1-9H8.9V6c0-1.71 1.39-3.1 3.1-3.1 1.71 0 3.1 1.39 3.1 3.1v2z"/></svg><span id="anti-copy-toast-msg"></span>';
            document.body.appendChild(toast);
        }
        var msgEl = document.getElementById('anti-copy-toast-msg');
        if (msgEl) {
            msgEl.textContent = message || 'Content Protected: Right-click and copying are disabled.';
        }
        toast.classList.add('show');
        clearTimeout(toastTimeout);
        toastTimeout = setTimeout(function() {
            toast.classList.remove('show');
        }, 2200);
    }

    function isInputElement(target) {
        if (!target) return false;
        var tag = target.tagName ? target.tagName.toUpperCase() : '';
        return tag === 'INPUT' || tag === 'TEXTAREA' || target.isContentEditable;
    }

    // 1. Disable Right-Click Context Menu
    document.addEventListener('contextmenu', function(e) {
        if (!isInputElement(e.target)) {
            e.preventDefault();
            showProtectionToast('Right-click is disabled to protect website content.');
            return false;
        }
    }, true);

    // 2. Disable Selection outside input elements
    document.addEventListener('selectstart', function(e) {
        if (!isInputElement(e.target)) {
            e.preventDefault();
            return false;
        }
    }, true);

    // 3. Disable Dragging of Images / Elements
    document.addEventListener('dragstart', function(e) {
        e.preventDefault();
        return false;
    }, true);

    // 4. Disable Copy and Cut
    document.addEventListener('copy', function(e) {
        if (!isInputElement(e.target)) {
            e.preventDefault();
            showProtectionToast('Copying text and media is disabled.');
            return false;
        }
    }, true);

    document.addEventListener('cut', function(e) {
        if (!isInputElement(e.target)) {
            e.preventDefault();
            return false;
        }
    }, true);

    // 5. Disable Inspection & Save/Source Keyboard Shortcuts
    document.addEventListener('keydown', function(e) {
        var isCtrlOrCmd = e.ctrlKey || e.metaKey;
        var key = e.key ? e.key.toLowerCase() : '';
        var keyCode = e.keyCode || e.which;

        // F12 or Devtools (Ctrl+Shift+I / J / C)
        if (key === 'f12' || keyCode === 123 || (isCtrlOrCmd && e.shiftKey && (key === 'i' || key === 'j' || key === 'c'))) {
            e.preventDefault();
            showProtectionToast('Developer tools access is disabled.');
            return false;
        }

        // View Source (Ctrl+U)
        if (isCtrlOrCmd && key === 'u') {
            e.preventDefault();
            showProtectionToast('View source is disabled.');
            return false;
        }

        // Save Page (Ctrl+S)
        if (isCtrlOrCmd && key === 's') {
            e.preventDefault();
            showProtectionToast('Saving web pages is disabled.');
            return false;
        }

        // Print Page (Ctrl+P) - only block if not printing tickets
        if (isCtrlOrCmd && key === 'p' && !window.location.pathname.includes('print_ticket.php') && !window.location.pathname.includes('view_ticket.php')) {
            e.preventDefault();
            return false;
        }

        // Copy (Ctrl+C) / Select All (Ctrl+A) outside text inputs
        if (isCtrlOrCmd && (key === 'c' || key === 'a' || key === 'x') && !isInputElement(e.target)) {
            e.preventDefault();
            showProtectionToast('Content copying is disabled.');
            return false;
        }
    }, true);
})();
</script>
<?php endif; ?>
