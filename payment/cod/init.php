<?php
require_once(__DIR__ . '/../../config.php');
require_once(__DIR__ . '/../../header.php'); // assumes session & DB connection are initialized

try {
    $payment_id = 'COD_' . time();
    $payment_date = date('Y-m-d H:i:s');

    // Insert into tbl_payment
    $statement = $pdo->prepare("INSERT INTO tbl_payment (
        customer_id,
        customer_name,
        customer_email,
        payment_date,
        txnid,
        paid_amount,
        card_number,
        card_cvv,
        card_month,
        card_year,
        bank_transaction_info,
        payment_method,
        payment_status,
        shipping_status,
        payment_id
    ) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)");

    $statement->execute(array(
        $_SESSION['customer']['cust_id'],
        $_SESSION['customer']['cust_name'],
        $_SESSION['customer']['cust_email'],
        $payment_date,
        '',                 // txnid is empty for COD
        $_POST['amount'],
        '', '', '', '', '', // card/bank info empty for COD
        'Cash On Delivery',
        'Pending',
        'Pending',
        $payment_id
    ));

    // Insert into tbl_order
    $cartItems = count($_SESSION['cart_p_id']);
    for ($i = 0; $i < $cartItems; $i++) {
        if (
            empty($_SESSION['cart_p_id'][$i]) ||
            empty($_SESSION['cart_p_name'][$i]) ||
            !isset($_SESSION['cart_p_qty'][$i])
        ) {
            continue; // Skip incomplete cart items
        }
    
        $statement = $pdo->prepare("INSERT INTO tbl_order (
            product_id,
            product_name,
            size,
            color,
            quantity,
            unit_price,
            payment_id
        ) VALUES (?,?,?,?,?,?,?)");
    
        $statement->execute(array(
            $_SESSION['cart_p_id'][$i],
            $_SESSION['cart_p_name'][$i],
            $_SESSION['cart_size_name'][$i] ?? '',
            $_SESSION['cart_color_name'][$i] ?? '',
            $_SESSION['cart_p_qty'][$i],
            $_SESSION['cart_p_current_price'][$i],
            $payment_id
        ));
    
        // Update product quantity
        $statement = $pdo->prepare("SELECT p_qty FROM tbl_product WHERE p_id = ?");
        $statement->execute([$_SESSION['cart_p_id'][$i]]);
        $result = $statement->fetch(PDO::FETCH_ASSOC);
    
        if ($result) {
            $final_qty = $result['p_qty'] - $_SESSION['cart_p_qty'][$i];
            $statement = $pdo->prepare("UPDATE tbl_product SET p_qty = ? WHERE p_id = ?");
            $statement->execute([$final_qty, $_SESSION['cart_p_id'][$i]]);
        }
    }

    // Clear cart
    unset($_SESSION['cart_p_id']);
    unset($_SESSION['cart_size_id']);
    unset($_SESSION['cart_size_name']);
    unset($_SESSION['cart_color_id']);
    unset($_SESSION['cart_color_name']);
    unset($_SESSION['cart_p_qty']);
    unset($_SESSION['cart_p_current_price']);
    unset($_SESSION['cart_p_name']);
    unset($_SESSION['cart_p_featured_photo']);

    header('Location: ../../payment_success.php');
    exit;

} catch (Exception $e) {
    $error = 'Error: ' . $e->getMessage();
    header('Location: ../../checkout.php?error=' . urlencode($error));
    exit;
}
?>
