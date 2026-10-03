<style>
        .smooth-link {
            color: #4f46e5;
            text-decoration: none;
            position: relative;
            font-weight: 600;
            padding-bottom: 2px;
        }

        .smooth-link::after {
            content: '';
            position: absolute;
            width: 0;
            height: 3px;
            bottom: 0;
            left: 0;
            background-color: #4f46e5;
            transition: width 0.3s ease-in-out;
            border-radius: 2px;
        }

        .smooth-link:hover::after {
            width: 100%;
        }

        .smooth-link:hover,
        .smooth-link:focus {
            color: #4f46e5;
            text-decoration: none;
        }
    </style>

<!-- Premium Scroll to Top Button -->
<div class="scroll-to-top" id="scrollToTop">
    <svg class="progress-circle" width="100%" height="100%" viewBox="-1 -1 102 102">
        <path d="M50,1 a49,49 0 0,1 0,98 a49,49 0 0,1 0,-98" />
    </svg>
    <div class="scroll-icon">
        <i class="bi bi-arrow-up"></i>
    </div>
</div>

<style>
    .scroll-to-top {
        position: fixed;
        bottom: 30px;
        right: 30px;
        width: 45px;
        height: 45px;
        background: rgba(33, 37, 41, 0.9);
        backdrop-filter: blur(8px);
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        z-index: 1050;
        opacity: 0;
        visibility: hidden;
        transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
        transform: translateY(20px);
        box-shadow: 0 8px 20px rgba(0, 0, 0, 0.4);
        border: 1px solid rgba(255, 255, 255, 0.05);
    }

    .scroll-to-top.active {
        opacity: 1;
        visibility: visible;
        transform: translateY(0);
    }

    .scroll-to-top:hover {
        transform: translateY(-5px);
        background: var(--bs-crimson-light, #B00000);
        border-color: var(--bs-gold, #FFC107);
    }

    .scroll-to-top:hover .scroll-icon {
        color: #fff;
        transform: translateY(-2px);
    }

    .scroll-icon {
        font-size: 1.3rem;
        color: var(--bs-gold, #FFC107);
        transition: all 0.3s ease;
        z-index: 2;
    }

    .progress-circle {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        z-index: 1;
    }

    .progress-circle path {
        fill: none;
        stroke: var(--bs-gold, #FFC107);
        stroke-width: 4;
        transition: all 0.2s linear;
        stroke-dasharray: 307.919, 307.919;
        stroke-dashoffset: 307.919;
    }
</style>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const scrollBtn = document.getElementById('scrollToTop');
        const progressPath = document.querySelector('.progress-circle path');
        const pathLength = progressPath.getTotalLength();

        progressPath.style.strokeDasharray = `${pathLength} ${pathLength}`;
        progressPath.style.transition = 'stroke-dashoffset 10ms linear';

        const updateProgress = () => {
            const scroll = window.pageYOffset;
            const height = document.documentElement.scrollHeight - window.innerHeight;
            const progress = pathLength - (scroll * pathLength / height);
            progressPath.style.strokeDashoffset = progress;

            if (scroll > 200) {
                scrollBtn.classList.add('active');
            } else {
                scrollBtn.classList.remove('active');
            }
        };

        window.addEventListener('scroll', updateProgress);
        
        scrollBtn.addEventListener('click', () => {
            window.scrollTo({
                top: 0,
                behavior: 'smooth'
            });
        });

        updateProgress();
    });
</script>

<style>
    .dashboard_footer {
        text-align: center;
        padding: 1.5rem;
        color: #6c757d;
        font-size: 0.9rem;
        background: #ffffff;
        border-top: 1px solid rgba(0,0,0,0.05);
        margin-top: auto; /* Push to bottom if flex container */
        width: 100%;
        box-shadow: 0 -2px 10px rgba(0,0,0,0.02);
    }
    .dashboard_footer span {
        color: var(--bs-crimson-light, #B00000);
        font-weight: 600;
    }
</style>
<footer class="dashboard_footer">
    <div class="container-fluid">
        &copy; <?php echo date("Y"); ?> <span><?php echo htmlspecialchars($site_settings['site_title'] ?? 'Celebrity Booking'); ?></span>. All Rights Reserved.
    </div>
</footer>

<script src="../assets/js/bootstrap.bundle.min.js"></script>
<script src="../assets/js/sweetalert2.all.min.js"></script>
<script>
function confirmAction(event, formOrLink, message, confirmText = 'Yes, delete it!') {
    event.preventDefault();
    Swal.fire({
        title: 'Are you sure?',
        text: message,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#dc3545',
        cancelButtonColor: '#6c757d',
        confirmButtonText: confirmText
    }).then((result) => {
        if (result.isConfirmed) {
            if (formOrLink.tagName === 'FORM') {
                formOrLink.submit();
            } else if (formOrLink.tagName === 'A') {
                window.location.href = formOrLink.href;
            }
        }
    });
}
</script>

<?php if (isset($_SESSION['message'])): ?>
<script>
document.addEventListener('DOMContentLoaded', function() {
    Swal.fire({
        title: '<?php echo ($_SESSION['message_type'] ?? 'success') === 'danger' ? 'Error!' : (($_SESSION['message_type'] ?? 'success') === 'warning' ? 'Warning!' : 'Success!'); ?>',
        text: '<?php echo htmlspecialchars(strip_tags($_SESSION['message'])); ?>',
        icon: '<?php echo ($_SESSION['message_type'] ?? 'success') === 'danger' ? 'error' : (($_SESSION['message_type'] ?? 'success') === 'warning' ? 'warning' : 'success'); ?>',
        confirmButtonColor: '#B00000'
    });
});
</script>
<?php 
unset($_SESSION['message']); 
unset($_SESSION['message_type']); 
endif; 
?>
