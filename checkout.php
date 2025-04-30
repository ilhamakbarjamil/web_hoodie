<?php
require_once 'config.php';
require_once 'midtrans_config.php';
include 'header.php';

// Proses checkout
if($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Simpan data order ke database
    $payment_method = $_POST['payment_method'];
    $transaction_details = [
        'order_id' => uniqid(),
        'gross_amount' => $_POST['total']
    ];
    
    // Data customer
    $customer_details = [
        'first_name' => $_POST['name'],
        'email' => $_POST['email'],
        'phone' => $_POST['phone']
    ];
    
    // Data item details
    $item_details = [];
    foreach($_SESSION['cart'] as $product_id) {
        $stmt = $conn->prepare("SELECT * FROM products WHERE id = ?");
        $stmt->bind_param("i", $product_id);
        $stmt->execute();
        $product = $stmt->get_result()->fetch_assoc();
        
        $item_details[] = [
            'id' => $product['id'],
            'price' => $product['price'],
            'quantity' => 1,
            'name' => $product['name']
        ];
    }
    
    // Parameter transaksi Midtrans
    $transaction = [
        'transaction_details' => $transaction_details,
        'customer_details' => $customer_details,
        'item_details' => $item_details
    ];
    
    try {
        $snapToken = Snap::getSnapToken($transaction);
        header("Location: " . Snap::getSnapUrl($snapToken));
        exit();
    } catch (Exception $e) {
        echo "Error: " . $e->getMessage();
    }
}
?>

<h2>Checkout</h2>
<form method="post">
    <div class="mb-3">
        <label>Name</label>
        <input type="text" name="name" class="form-control" required>
    </div>
    <div class="mb-3">
        <label>Email</label>
        <input type="email" name="email" class="form-control" required>
    </div>
    <div class="mb-3">
        <label>Phone</label>
        <input type="tel" name="phone" class="form-control" required>
    </div>
    <div class="mb-3">
        <label>Payment Method</label>
        <select name="payment_method" class="form-select" required>
            <option value="credit_card">Credit Card</option>
            <option value="bank_transfer">Bank Transfer</option>
            <option value="gopay">GoPay</option>
        </select>
    </div>
    <input type="hidden" name="total" value="<?= $total ?>">
    <button type="submit" class="btn btn-primary">Proceed to Payment</button>
</form>

<?php include 'footer.php'; ?>