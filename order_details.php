<?php
session_start();
include 'includes/db_connect.php';
include 'includes/functions.php';

check_login();
$user_id = $_SESSION['user_id'];

if (!isset($_GET['id'])) {
    redirect('my_orders.php');
}

$order_id = intval($_GET['id']);

// Fetch Order (verify ownership)
$sql = "SELECT o.*, a.address_line, a.city, a.postal_code, a.country, a.province, a.barangay 
        FROM orders o 
        LEFT JOIN addresses a ON o.address_id = a.id
        WHERE o.id = ? AND o.user_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $order_id, $user_id);
$stmt->execute();
$order = $stmt->get_result()->fetch_assoc();

if (!$order) {
    // Order not found or doesn't belong to user
    redirect('my_orders.php');
}

// Fetch Items
$sql_items = "SELECT oi.*, p.name, p.image_url 
              FROM order_items oi 
              JOIN products p ON oi.product_id = p.id 
              WHERE oi.order_id = ?";
$stmt_items = $conn->prepare($sql_items);
$stmt_items->bind_param("i", $order_id);
$stmt_items->execute();
$items = $stmt_items->get_result();
?>
<?php include 'includes/header.php'; ?>

<div class="container my-5">
    <div class="row justify-content-center">
        <div class="col-md-9">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2 class="section-title mb-0">Order #<?php echo str_pad($order_id, 5, '0', STR_PAD_LEFT); ?></h2>
                <a href="my_orders.php" class="btn btn-outline-secondary rounded-pill px-4">
                    <i class="fas fa-arrow-left me-2"></i> Back to Orders
                </a>
            </div>

            <div class="card border-0 shadow-lg rounded-4 overflow-hidden mb-4">
                <div
                    class="card-header py-3 border-bottom d-flex justify-content-between align-items-center flex-wrap gap-2">
                    <div>
                        <span class="text-muted small d-block">Placed on
                            <?php echo date('M d, Y h:i A', strtotime($order['created_at'])); ?></span>
                        <?php
                        $status = $order['status'];
                        $badgeClass = match ($status) {
                            'completed', 'delivered' => 'bg-success text-success',
                            'shipped' => 'bg-info text-info',
                            'processing' => 'bg-primary text-primary',
                            'cancelled' => 'bg-danger text-danger',
                            default => 'bg-warning text-warning'
                        };
                        ?>
                        <span
                            class="badge <?php echo $badgeClass; ?> bg-opacity-10 px-3 py-2 rounded-pill text-uppercase mt-1">
                            <?php echo $status; ?>
                        </span>
                    </div>

                    <?php if ($status === 'pending'): ?>
                        <a href="actions/cancel_order.php?id=<?php echo $order_id; ?>"
                            class="btn btn-outline-danger btn-sm rounded-pill px-3"
                            onclick="return confirm('Are you sure you want to cancel this order?');">
                            <i class="fas fa-times-circle me-1"></i> Cancel Order
                        </a>
                    <?php endif; ?>
                </div>
                <div class="card-body p-0">
                    <table class="table mb-0 align-middle">
                        <tbody>
                            <?php while ($item = $items->fetch_assoc()): ?>
                                <tr>
                                    <td class="ps-4" style="width: 80px;">
                                        <?php
                                        $imgSrc = !empty($item['image_url']) ? $item['image_url'] : 'assets/img/placeholder.png';
                                        ?>
                                        <img src="<?php echo htmlspecialchars($imgSrc); ?>"
                                            alt="<?php echo htmlspecialchars($item['name']); ?>" class="rounded"
                                            style="width: 60px; height: 60px; object-fit: cover;">
                                    </td>
                                    <td>
                                        <h6 class="mb-0 fw-bold"><?php echo htmlspecialchars($item['name']); ?></h6>
                                        <small class="text-muted">Unit Price:
                                            ₱<?php echo number_format($item['price'], 2); ?></small>

                                        <?php if ($status === 'delivered' || $status === 'completed'): ?>
                                            <div class="mt-2">
                                                <button type="button"
                                                    class="btn btn-sm btn-outline-secondary rounded-pill py-0 px-3"
                                                    style="font-size: 0.75rem;" data-bs-toggle="modal"
                                                    data-bs-target="#reviewModal"
                                                    data-product-id="<?php echo $item['product_id']; ?>"
                                                    data-product-name="<?php echo htmlspecialchars($item['name']); ?>">
                                                    <i class="fas fa-star text-warning me-1"></i> Write a Review
                                                </button>
                                            </div>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-center">x<?php echo $item['quantity']; ?></td>
                                    <td class="text-end pe-4 fw-bold">
                                        ₱<?php echo number_format($item['price'] * $item['quantity'], 2); ?></td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                        <tfoot class="">
                            <tr>
                                <td colspan="3" class="text-end py-3 fw-bold">Total Amount:</td>
                                <td class="text-end py-3 pe-4 fs-5 fw-bold text-primary">
                                    ₱<?php echo number_format($order['total_amount'], 2); ?></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>

            <!-- Review Modal -->
            <div class="modal fade" id="reviewModal" tabindex="-1">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content rounded-4 border-0">
                        <div class="modal-header border-0 pb-0">
                            <h5 class="modal-title fw-bold">Review Product</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <form action="actions/submit_review.php" method="POST">
                            <div class="modal-body">
                                <input type="hidden" name="order_id" value="<?php echo $order_id; ?>">
                                <input type="hidden" name="product_id" id="reviewProductId">

                                <p class="text-muted mb-3">How was your experience with <strong class="text-dark"
                                        id="reviewProductName"></strong>?</p>

                                <div class="mb-3 text-center">
                                    <label class="form-label d-block fw-bold mb-2">Rating</label>
                                    <div class="rating-stars">
                                        <?php for ($i = 5; $i >= 1; $i--): ?>
                                            <input type="radio" name="rating" id="star<?php echo $i; ?>"
                                                value="<?php echo $i; ?>" required>
                                            <label for="star<?php echo $i; ?>"><i class="fas fa-star"></i></label>
                                        <?php endfor; ?>
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label fw-bold">Comment</label>
                                    <textarea name="comment" class="form-control" rows="3"
                                        placeholder="Tell us what you think..." required></textarea>
                                </div>
                            </div>
                            <div class="modal-footer border-0 pt-0">
                                <button type="button" class="btn btn-light rounded-pill"
                                    data-bs-dismiss="modal">Cancel</button>
                                <button type="submit" class="btn btn-primary-custom rounded-pill px-4">Submit
                                    Review</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <style>
                /* Simple Star Rating CSS */
                .rating-stars {
                    display: flex;
                    flex-direction: row-reverse;
                    justify-content: center;
                    gap: 10px;
                }

                .rating-stars input {
                    display: none;
                }

                .rating-stars label {
                    cursor: pointer;
                    font-size: 1.5rem;
                    color: #ddd;
                    transition: color 0.2s;
                }

                .rating-stars input:checked~label,
                .rating-stars label:hover,
                .rating-stars label:hover~label {
                    color: #ffc107;
                }
            </style>

            <script>
                document.addEventListener('DOMContentLoaded', function () {
                    var reviewModal = document.getElementById('reviewModal');
                    reviewModal.addEventListener('show.bs.modal', function (event) {
                        var button = event.relatedTarget;
                        var productId = button.getAttribute('data-product-id');
                        var productName = button.getAttribute('data-product-name');

                        var modalProductId = reviewModal.querySelector('#reviewProductId');
                        var modalProductName = reviewModal.querySelector('#reviewProductName');

                        modalProductId.value = productId;
                        modalProductName.textContent = productName;
                    });
                });
            </script>

            <div class="row">
                <div class="col-md-6">
                    <div class="card border-0 shadow-sm rounded-4 h-100">
                        <div class="card-body">
                            <h5 class="fw-bold mb-3"><i class="fas fa-map-marker-alt text-primary me-2"></i> Shipping
                                Address</h5>
                            <p class="mb-0 text-muted">
                                <?php echo htmlspecialchars($order['address_line']); ?><br>
                                <?php echo htmlspecialchars($order['barangay']) . ', ' . htmlspecialchars($order['city']); ?><br>
                                <?php echo htmlspecialchars($order['province']) . ', ' . htmlspecialchars($order['country']); ?><br>
                                <?php echo htmlspecialchars($order['postal_code']); ?>
                            </p>
                        </div>
                    </div>
                </div>
                <!-- Add Payment Info mock if needed, but address is enough for now -->
            </div>

        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>