<?php
// I-include ang database connection script
include 'includes/db_connect.php';
// I-load ang header (navbar, css, atbp.)
include 'includes/header.php';
?>

<!-- About Header Section: Dito nakalagay ang title at maikling intro -->
<div class="container mt-4">
    <div class="about-header">
        <h1 class="display-3 fw-bold mb-3">About Footporium</h1>
        <p class="lead">The world's leading destination for premium feet.</p>
    </div>
</div>

<!-- Main Content Section: Dito ang kwento at mission/vision ng Footporium -->
<div class="container mb-5">
    <div class="row justify-content-center">
        <div class="col-lg-10">
            <div class="card border-0 shadow-sm p-4 p-md-5">
                <div class="card-body">
                    <h2 class="section-title mb-4">Our Story</h2>
                    <p class="lead text-muted mb-4" style="line-height: 1.8;">
                        Welcome to <strong>Footporium</strong>, the premier luxury marketplace for feet. From the
                        everyday to the exotic, we curate the finest selection of podiatric wonders. Born from a passion
                        for anatomical excellence, we bridge the gap between collectors and the finest foot specimens
                        available.
                    </p>

                    <!-- Strategic Alignment (IT Governance) -->
                    <div class="row my-5">
                        <div class="col-md-6">
                            <h3 class="h4 fw-bold text-primary">Our Mission</h3>
                            <p class="text-muted">To curate an exclusive collection of high-quality foot products,
                                offering discerning clients unparalleled access to the world's most unique specimens
                                with guaranteed authenticity.</p>
                        </div>
                        <div class="col-md-6">
                            <h3 class="h4 fw-bold text-primary">Our Vision</h3>
                            <p class="text-muted">To be the undisputed global authority in foot commerce, setting the
                                standard for quality, preservation, and ethical sourcing in the anatomical market.</p>
                        </div>
                    </div>

                    <p class="lead text-muted mb-4" style="line-height: 1.8;">
                        <strong class="text-primary">Established in 2024</strong>, Footporium has rapidly ascended to
                        the pinnacle of the industry. What began as a humble curiosity has blossomed into a global
                        movement, redefining how the world appreciates the foundational beauty of biology.
                    </p>
                    <p class="lead text-muted mb-0" style="line-height: 1.8;">
                        Distinguished by our commitment to excellence, every item in our inventory is rigorously
                        inspected. We hope you find the perfect addition to your collection.
                    </p>

                    <!-- Social Media Links -->
                    <div class="mt-5 pt-4 text-center" style="border-top: 1px solid var(--border-color)">
                        <h4 class="mb-3">Connect With Us</h4>
                        <div class="d-flex justify-content-center gap-3">
                            <a href="#" class="btn btn-outline-secondary rounded-circle social-btn"><i
                                    class="fab fa-facebook-f fa-lg"></i></a>
                            <a href="#" class="btn btn-outline-secondary rounded-circle social-btn"><i
                                    class="fab fa-twitter fa-lg"></i></a>
                            <a href="#" class="btn btn-outline-secondary rounded-circle social-btn"><i
                                    class="fab fa-instagram fa-lg"></i></a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
// I-load ang footer section
include 'includes/footer.php';
?>