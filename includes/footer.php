
</main>

<footer class="footer mt-5">
    <div class="container py-5">
        <div class="row g-4">

            <div class="col-lg-4">
                <h4 class="fw-bold">Modern<span>Clinic</span></h4>
                <p>Compassionate healthcare, trusted professionals and a simpler way to manage your care.</p>
            </div>

            <div class="col-6 col-lg-2">
                <h6>Explore</h6>
                <a href="<?= $baseUrl ?>/doctors.php">Doctors</a>
                <a href="<?= $baseUrl ?>/departments.php">Departments</a>
                <a href="<?= $baseUrl ?>/services.php">Services</a>
            </div>

            <div class="col-6 col-lg-2">
                <h6>Company</h6>
                <a href="<?= $baseUrl ?>/about.php">About</a>
                <a href="<?= $baseUrl ?>/contact.php">Contact</a>
                <a href="<?= $baseUrl ?>/faq.php">FAQs</a>
            </div>

            <div class="col-lg-4">
                <h6>Contact</h6>
                <p><i class="fa-solid fa-location-dot me-2"></i> Karachi, Pakistan</p>
                <p><i class="fa-solid fa-phone me-2"></i> +92 300 1234567</p>
                <p><i class="fa-solid fa-envelope me-2"></i> care@modernclinic.com</p>
            </div>

        </div>
    </div>

    <div class="footer-bottom text-center py-3">
        © <?= date('Y') ?> Modern Clinic. All rights reserved.
    </div>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="<?= $baseUrl ?>/assets/js/app.js"></script>

<script>
(function () {
    function initializeNavbarAnimation() {
        const navbar = document.querySelector('nav.clinic-nav');

        if (!navbar || navbar.dataset.scrollAnimationReady === 'true') {
            return;
        }

        navbar.dataset.scrollAnimationReady = 'true';

        let previousScroll = window.scrollY;
        let framePending = false;

        window.addEventListener('scroll', function () {
            if (framePending) return;

            framePending = true;

            window.requestAnimationFrame(function () {
                const currentScroll = window.scrollY;
                const movement = currentScroll - previousScroll;

                if (currentScroll <= 40) {
                    navbar.classList.remove('nav-hidden');
                } else if (movement > 4 && currentScroll > 120) {
                    navbar.classList.add('nav-hidden');
                } else if (movement < -4) {
                    navbar.classList.remove('nav-hidden');
                }

                previousScroll = currentScroll;
                framePending = false;
            });
        }, { passive: true });
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initializeNavbarAnimation);
    } else {
        initializeNavbarAnimation();
    }
})();
</script>

</body>
</html>
