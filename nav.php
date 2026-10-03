<style>
  .navbar {
    background: linear-gradient(135deg, #8A0000 0%, #B00000 100%);
    backdrop-filter: blur(10px);
    transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
    border-bottom: 1px solid rgba(255, 255, 255, 0.1);
    padding: 1rem 0;
  }

  .navbar.scrolled {
    background: rgba(138, 0, 0, 0.95);
    padding: 0.75rem 0;
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3);
  }

  .nav-link {
    font-family: 'Playfair Display', serif;
    font-weight: 600;
    letter-spacing: 0.5px;
    margin: 0 5px;
    transition: all 0.3s ease;
    opacity: 0.85;
  }

  .nav-link:hover, .nav-link.active, .nav-link:focus, .nav-link:active {
    color: #FFC107 !important;
    opacity: 1;
    transform: translateY(-1px);
    outline: none !important;
    border: none !important;
    box-shadow: none !important;
    -webkit-tap-highlight-color: transparent !important;
  }

  .navbar-toggler {
    border: none !important;
    outline: none !important;
    box-shadow: none !important;
    -webkit-tap-highlight-color: transparent !important;
    padding: 0;
    width: 42px;
    height: 42px;
    display: flex;
    align-items: center;
    justify-content: center;
    background: rgba(255, 255, 255, 0.1);
    border-radius: 10px;
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
  }

  .navbar-toggler:hover, .navbar-toggler:focus, .navbar-toggler:active {
    background: rgba(255, 255, 255, 0.2);
    outline: none !important;
    box-shadow: none !important;
    border: none !important;
  }

  .navbar-toggler svg {
    width: 22px;
    height: 22px;
  }

  .navbar-toggler svg line {
    transition: all 0.3s ease;
    stroke: #fff;
  }
 
  @media (max-width: 991px) {
      .adj {
          width: 45%;
      }
      .navbar-collapse {
          background: #8A0000;
          margin-top: 1rem;
          padding: 1.5rem;
          border-radius: 15px;
          border: 1px solid rgba(255,255,255,0.1);
      }
  }

  @media (min-width: 991px) {
      .adj {
          width: 26%;
      }
  }

  .VIpgJd-ZVi9od-aZ2wEe-OiiCO.VIpgJd-ZVi9od-aZ2wEe-OiiCO-ti6hGc{
      display:none;
  }
</style>

<nav class="navbar navbar-expand-lg navbar-dark fixed-top shadow">
  <div class="container">
    
	<a class="navbar-brand fw-bolder text-white d-flex align-items-center adj" href="index.php" style="">
        
        <?php if ($use_logo): ?>
            <img src="<?php echo $logo_path; ?>" alt="<?php echo htmlspecialchars($site_settings['site_title'] ?? 'VIP Celebrity Booking'); ?>" style="max-height: 38px; margin-right: 10px;">
        
        <?php else: ?>
            <i class="bi bi-star-fill fs-3 text-warning me-2"></i>
            <div class="d-flex flex-column" style="line-height: 1.1;">
                <span class="fs-5 fw-900" style="
            FONT-WEIGHT: 900;
            FONT-FAMILY: 'ABC Ginto Nord Unlicensed Trial Black', 'Outfit', sans-serif;
        "><?php echo strtoupper(htmlspecialchars($site_settings['site_title'] ?? 'VIP CELEBRITY')); ?></span>
                <span class="fw-normal mt-n1 text-white opacity-75" style="font-size: 10px; letter-spacing: 1px; font-weight: 600 !important; FONT-FAMILY: 'Inter', sans-serif;">
                   EXCLUSIVE TALENT
                </span>
            </div>
        <?php endif; ?>

	</a>
	
	<span class="d-inline d-lg-none">
        <?php include "gtranslate.php"; ?>
    </span>
	
	<button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
      <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" class="menu-icon">
        <line x1="3" y1="5" x2="21" y2="6" class="line-1" />
        <line x1="7" y1="12" x2="18" y2="11.5" class="line-2" />
        <line x1="4" y1="18.5" x2="19" y2="18" class="line-3" />
      </svg>
    </button>


<div class="collapse navbar-collapse" id="navbarNav">
  <ul class="navbar-nav ms-auto">
    <li class="nav-item">
      <a class="nav-link <?= basename($_SERVER['PHP_SELF']) == 'index.php' ? 'active' : '' ?>" href="index.php">Home</a>
    </li>
    <li class="nav-item">
      <a class="nav-link <?= basename($_SERVER['PHP_SELF']) == 'performers.php' ? 'active' : '' ?>" href="performers.php">Performers</a>
    </li>
    <li class="nav-item">
      <a class="nav-link <?= basename($_SERVER['PHP_SELF']) == 'fan_cards.php' ? 'active' : '' ?>" href="fan_cards.php">Fan Cards</a>
    </li>
    <li class="nav-item">
      <a class="nav-link <?= basename($_SERVER['PHP_SELF']) == 'donation.php' ? 'active' : '' ?>" href="donation.php">Donation</a>
    </li>
    <li class="nav-item">
      <a class="nav-link <?= basename($_SERVER['PHP_SELF']) == 'about.php' ? 'active' : '' ?>" href="about.php">About</a>
    </li>
    
	<li class="nav-item">
      <a class="nav-link <?= basename($_SERVER['PHP_SELF']) == 'services.php' ? 'active' : '' ?>" href="services.php">Services</a>
    </li>
	
    <li class="nav-item dropdown">
      <a class="nav-link dropdown-toggle <?= in_array(basename($_SERVER['PHP_SELF']), ['tickets.php', 'my_tickets.php']) ? 'active' : '' ?>" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
        Tickets <i class="fas fa-chevron-down ms-1" style="font-size: 0.75rem;"></i>
      </a>
      <ul class="dropdown-menu">
        <li><a class="dropdown-item" href="tickets.php"><i class="bi bi-ticket-perforated me-2"></i> Book Tickets</a></li>
        <li><a class="dropdown-item" href="my_tickets.php"><i class="bi bi-person-badge me-2"></i> My Tickets</a></li>
      </ul>
    </li>

   <li class="nav-item dropdown">
    <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
      More  <i class="fas fa-chevron-down ms-1" style="font-size: 0.75rem;"></i>
    </a>
    <ul class="dropdown-menu">
      <li><a class="dropdown-item" href="contact.php"><i class="bi bi-envelope me-2"></i> Contact Us</a></li>
      <li><a class="dropdown-item" href="careers.php"><i class="bi bi-briefcase-fill me-2"></i> Careers</a></li>
      <li><a class="dropdown-item" href="enter_giveaway.php"><i class="bi bi-gift-fill me-2"></i> Giveaways</a></li>
      <li><hr class="dropdown-divider"></li>
      <li><a class="dropdown-item" href="privacy.php"><i class="bi bi-shield-lock me-2"></i> Privacy Policy</a></li>
      <li><a class="dropdown-item" href="terms.php"><i class="bi bi-file-earmark-text me-2"></i> Terms of Service</a></li>
    </ul>
</li>
    <li class="nav-item d-none d-lg-block">
    <?php include "gtranslate.php"; ?>
</li>

  </ul>
  <a href="book.php" class="btn btn-warning fw-bold text-dark rounded-pill ms-lg-3 shadow-lg btn-skew">
    <span class="btn-skew-text">BOOK TALENT</span>
  </a>
</div>

  </div>
</nav>



<script>
    // --- Mobile Menu Toggle Icon Script (Retained) ---
    const toggler = document.querySelector('.navbar-toggler');
    const navbarCollapse = document.querySelector('#navbarNav');
    
    navbarCollapse.addEventListener('show.bs.collapse', function () {
        toggler.querySelector('.line-1').setAttribute('x1', '5');
        toggler.querySelector('.line-1').setAttribute('y1', '5');
        toggler.querySelector('.line-1').setAttribute('x2', '19');
        toggler.querySelector('.line-1').setAttribute('y2', '19');
        
        toggler.querySelector('.line-2').setAttribute('opacity', '0');
        
        toggler.querySelector('.line-3').setAttribute('x1', '5');
        toggler.querySelector('.line-3').setAttribute('y1', '19');
        toggler.querySelector('.line-3').setAttribute('x2', '19');
        toggler.querySelector('.line-3').setAttribute('y2', '5');
    });
    
    navbarCollapse.addEventListener('hide.bs.collapse', function () {
        
        toggler.querySelector('.line-1').setAttribute('x1', '3');
        toggler.querySelector('.line-1').setAttribute('y1', '5');
        toggler.querySelector('.line-1').setAttribute('x2', '21');
        toggler.querySelector('.line-1').setAttribute('y2', '6');
        
        toggler.querySelector('.line-2').setAttribute('opacity', '1');
        
        toggler.querySelector('.line-3').setAttribute('x1', '4');
        toggler.querySelector('.line-3').setAttribute('y1', '18.5');
        toggler.querySelector('.line-3').setAttribute('x2', '19');
        toggler.querySelector('.line-3').setAttribute('y2', '18');
    });

    // --- NEW: Navbar Scroll Effect ---
    window.addEventListener('scroll', function() {
        const nav = document.querySelector('.navbar');
        if (window.scrollY > 50) {
            nav.classList.add('scrolled');
        } else {
            nav.classList.remove('scrolled');
        }
    });

    // --- NEW: Nested Dropdown Hover Fix (Desktop) ---
    document.addEventListener("DOMContentLoaded", function() {
        // Only enable hover functionality on desktop (>= 992px)
        if (window.innerWidth >= 992) {
            document.querySelectorAll('.dropdown-menu .dropend').forEach(function(dropDownItem){
                
                // When hovering over the parent list item (.dropend)
                dropDownItem.addEventListener('mouseenter', function(e){
                    let menu = e.target.querySelector('.dropdown-menu');
                    // Check if a submenu exists and is currently hidden
                    if (menu && menu.classList.contains('dropdown-menu-end')) {
                        // Stop the link action
                        e.preventDefault(); 
                        // Show the submenu
                        menu.classList.add('show');
                    }
                });

                // When the mouse leaves the parent list item (.dropend)
                dropDownItem.addEventListener('mouseleave', function(e){
                    let menu = e.target.querySelector('.dropdown-menu');
                    // Hide the submenu
                    if (menu) {
                        menu.classList.remove('show');
                    }
                });
            });
        }
    });
</script>