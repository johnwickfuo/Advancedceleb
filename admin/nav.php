<style>
    .navbar.header-bg {
        background: linear-gradient(135deg, #8A0000 0%, #B00000 100%);
        backdrop-filter: blur(10px);
        border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        padding: 0.75rem 1.5rem;
    }
    .navbar-brand {
        font-size: 1.1rem;
        letter-spacing: 1px;
        display: flex;
        align-items: center;
    }
    .navbar-brand i {
        background: #fff;
        padding: 6px;
        border-radius: 8px;
        color: var(--bs-crimson-light) !important;
        font-size: 1rem;
        box-shadow: 0 4px 10px rgba(0,0,0,0.1);
    }
    .btn-crimson-toggle {
        background: rgba(255,255,255,0.15);
        border: none;
        color: white;
        border-radius: 8px;
        width: 40px;
        height: 40px;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: all 0.3s;
    }
    .btn-crimson-toggle:hover {
        background: rgba(255,255,255,0.25);
        transform: scale(1.05);
    }
    .welcome-text {
        font-size: 0.85rem;
        opacity: 0.9;
        font-weight: 500;
    }
    .logout-link {
        background: rgba(255, 255, 255, 0.1);
        border: 1px solid rgba(255, 255, 255, 0.2);
        padding: 0.6rem 1.2rem;
        border-radius: 10px;
        font-size: 0.8rem;
        text-transform: uppercase;
        letter-spacing: 1px;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        backdrop-filter: blur(5px);
    }
    .logout-link:hover {
        background: rgba(255, 255, 255, 0.2);
        border-color: rgba(255, 255, 255, 0.4);
        transform: translateY(-2px);
        box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        color: #fff !important;
    }
    .logout-link:active {
        transform: translateY(0);
    }
</style>

<nav class="navbar navbar-expand-lg navbar-dark header-bg shadow-sm sticky-top">
    <div class="container-fluid">
        <button class="btn btn-crimson-toggle d-md-none me-3" type="button" data-bs-toggle="offcanvas" data-bs-target="#sidebarOffcanvas" aria-controls="sidebarOffcanvas">
            <i class="bi bi-list fs-4"></i>
        </button>
        <a class="navbar-brand fw-bolder text-white" href="admin_dashboard.php">
            <i class="bi bi-star-fill me-md-2"></i>
            <span class="d-none d-md-inline">VIP ADMIN</span>
        </a>
        <div class="ms-auto d-flex align-items-center">
            <div class="welcome-text me-3 text-white d-none d-lg-block">
                <span class="opacity-75">Authenticated as</span> 
                <span class="fw-bold ms-1 text-gold"><?php echo htmlspecialchars($_SESSION['username'] ?? 'Admin'); ?></span>
            </div>
            <a href="settings.php?tab=password" class="btn btn-sm logout-link text-white fw-bold me-2" title="Security Settings">
                <i class="bi bi-shield-lock me-1"></i> <span class="d-none d-sm-inline">Password</span>
            </a>
            <a href="logout.php" class="btn btn-sm logout-link text-white fw-bold">
                <i class="bi bi-box-arrow-right me-1"></i> Logout
            </a>
        </div>
    </div>
</nav>
