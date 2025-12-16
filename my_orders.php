<?php
session_start();
// Prevent Caching
header("Cache-Control: no-cache, no-store, must-revalidate"); // HTTP 1.1.
header("Pragma: no-cache"); // HTTP 1.0.
header("Expires: 0"); // Proxies.
include 'includes/db_connect.php';
include 'includes/functions.php';

check_login();
$user_id = $_SESSION['user_id'];

// Fetch orders: Kunin lahat ng orders ng user, sorted by latest
$sql = "SELECT * FROM orders WHERE user_id = ? ORDER BY created_at DESC";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
?>
<?php include 'includes/header.php'; ?>

<div class="container my-5">
    <div class="row justify-content-center">
        <div class="col-lg-10">
            <h2 class="section-title mb-4">My Orders</h2>

            <div class="card border-0 shadow-lg rounded-4 overflow-hidden">
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0 align-middle">
                            <thead>
                                <tr>
                                    <th class="py-3 ps-4">Order ID</th>
                                    <th class="py-3">Date</th>
                                    <th class="py-3">Total</th>
                                    <th class="py-3">Status</th>
                                    <th class="py-3 text-end pe-4">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if ($result->num_rows > 0): ?>
                                    <?php while ($row = $result->fetch_assoc()): ?>
                                        <!-- Order Row Loop -->
                                        <tr>
                                            <td class="ps-4 fw-bold text-primary">
                                                #<?php echo str_pad($row['id'], 5, '0', STR_PAD_LEFT); ?></td>
                                            <td><?php echo date('M d, Y h:i A', strtotime($row['created_at'])); ?></td>
                                            <td class="fw-bold">₱<?php echo number_format($row['total_amount'], 2); ?></td>
                                            <td>
                                                <?php
                                                $status = $row['status'];
                                                // Gumamit ng match expression para sa badge colors base sa status
                                                $badgeClass = match ($status) {
                                                    'completed', 'delivered' => 'bg-success text-success',
                                                    'shipped' => 'bg-info text-info',
                                                    'processing' => 'bg-primary text-primary',
                                                    'cancelled' => 'bg-danger text-danger',
                                                    default => 'bg-warning text-warning'
                                                };
                                                ?>
                                                <span
                                                    class="badge <?php echo $badgeClass; ?> bg-opacity-10 px-3 py-2 rounded-pill text-uppercase">
                                                    <?php echo $status; ?>
                                                </span>
                                            </td>
                                            <td class="text-end pe-4">
                                                <a href="order_details.php?id=<?php echo $row['id']; ?>"
                                                    class="btn btn-sm btn-outline-primary rounded-pill px-3">
                                                    View Details
                                                </a>
                                            </td>
                                        </tr>
                                    <?php endwhile; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="5" class="text-center py-5 text-muted">You have no orders yet.</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div class="mt-4 text-center">
                <a href="products.php"
                    class="btn btn-primary-custom px-4 py-2 text-uppercase letter-spacing-1 text-decoration-none">
                    <i class="fas fa-shopping-bag me-2"></i> Continue Shopping
                </a>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>