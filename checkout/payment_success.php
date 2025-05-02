<?php
include '../includes/header.php';
require '../vendor/autoload.php';
require '../includes/config.php';

use Midtrans\Config;
use Midtrans\Snap;

// Setup Midtrans
Config::$serverKey = 'SB-Mid-server-YourKey';
Config::$isProduction = false;
Config::$isSanitized = true;

// Ambil data order
$order_id = $_GET['order_id'];
$order = mysqli_fetch_assoc(mysqli_query($conn, 
    "SELECT * FROM orders WHERE id = $order_id"));

// Siapkan transaction details
$transaction = [
    'transaction_details' => [
        'order_id' => $order_id,
        'gross_amount' => $order['total']
    ],
    'customer_details' => [
        'first_name' => 'Customer',
        'email' => $_SESSION['user_email']
    ]
];

// Generate Snap Token
try {
    $snapToken = Snap::getSnapToken($transaction);
} catch (Exception $e) {
    die("Payment Error: " . $e->getMessage());
}
?>

<div class="container mt-5">
    <h2>Payment Gateway</h2>
    <div class="card shadow mt-4">
        <div class="card-body">
            <div id="payment-button" class="text-center py-5">
                <button class="btn btn-lg btn-success" id="pay-button">
                    Bayar Rp <?= number_format($order['total'], 0, ',', '.') ?>
                </button>
            </div>
        </div>
    </div>
</div>

<script src="https://app.sandbox.midtrans.com/snap/snap.js" 
        data-client-key="SB-Mid-client-YourClientKey"></script>
<script>
document.getElementById('pay-button').onclick = function(){
    snap.pay('<?= $snapToken ?>', {
        onSuccess: function(result){
            window.location = 'payment_success.php?order_id=<?= $order_id ?>';
        },
        onPending: function(result){
            window.location = 'payment_pending.php?order_id=<?= $order_id ?>';
        },
        onError: function(result){
            window.location = 'payment_failed.php?order_id=<?= $order_id ?>';
        }
    });
};
</script>

<?php include '../includes/footer.php'; ?>