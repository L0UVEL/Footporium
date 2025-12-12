<?php include 'includes/header.php'; ?>

    <!-- Cart Section -->
    <div class="container my-5">
        <h2 class="section-title text-center mb-5">Your Shopping Cart</h2>
        
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="cart-container">
                    <!-- Cart Item 1 -->
                    <div class="cart-item d-flex align-items-center justify-content-between flex-wrap gap-3">
                        <div class="d-flex align-items-center gap-3">
                            <img src="assets/img/human.png" alt="Human Foot" class="cart-img">
                            <div>
                                <h5 class="mb-1">Human Foot</h5>
                                <span class="text-primary fw-bold">₱50,000.00</span>
                            </div>
                        </div>
                        <div class="d-flex align-items-center gap-4">
                            <div class="quantity-control">
                                <button class="btn btn-sm btn-outline-secondary rounded-circle"><i class="fas fa-minus"></i></button>
                                <input type="text" value="1" class="quantity-input">
                                <button class="btn btn-sm btn-outline-secondary rounded-circle"><i class="fas fa-plus"></i></button>
                            </div>
                            <button class="btn btn-outline-danger btn-sm rounded-pill px-3">
                                <i class="fas fa-trash-alt me-1"></i> Remove
                            </button>
                        </div>
                    </div>

                    <!-- Cart Item 2 -->
                    <div class="cart-item d-flex align-items-center justify-content-between flex-wrap gap-3">
                        <div class="d-flex align-items-center gap-3">
                            <img src="assets/img/prosthetic.png" alt="Prosthetic Foot" class="cart-img">
                            <div>
                                <h5 class="mb-1">Prosthetic Foot</h5>
                                <span class="text-primary fw-bold">₱100,000.00</span>
                            </div>
                        </div>
                        <div class="d-flex align-items-center gap-4">
                            <div class="quantity-control">
                                <button class="btn btn-sm btn-outline-secondary rounded-circle"><i class="fas fa-minus"></i></button>
                                <input type="text" value="1" class="quantity-input">
                                <button class="btn btn-sm btn-outline-secondary rounded-circle"><i class="fas fa-plus"></i></button>
                            </div>
                            <button class="btn btn-outline-danger btn-sm rounded-pill px-3">
                                <i class="fas fa-trash-alt me-1"></i> Remove
                            </button>
                        </div>
                    </div>

                    <!-- Cart Summary -->
                    <div class="mt-4 pt-4 border-top">
                        <div class="d-flex justify-content-between align-items-center mb-4">
                            <h4 class="mb-0">Subtotal</h4>
                            <h3 class="text-primary fw-bold mb-0">₱150,000.00</h3>
                        </div>
                        <button class="btn btn-primary-custom w-100 py-3 text-uppercase letter-spacing-1">
                            Proceed to Checkout <i class="fas fa-arrow-right ms-2"></i>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

<?php include 'includes/footer.php'; ?>
