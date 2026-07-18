<?php
$page_title='Payment'; include('includes/header.php');
$placed=false; $total=0; $message='';
if (!signed_in()) { echo '<section class="center-card"><h1>Please sign in first.</h1><a class="button" href="login.php">Sign in</a></section>'; include('includes/footer.php'); return; }
if (isset($_POST['checkout'])) {
    require_csrf();
    $_SESSION['checkout']=array(safe_field($_POST['name']),safe_field($_POST['contact']),safe_field($_POST['address']),safe_field($_POST['note']));
}
if (!isset($_SESSION['checkout'])) { echo '<section class="center-card"><h1>Checkout details needed.</h1><a class="button" href="checkout.php">Return to checkout</a></section>'; include('includes/footer.php'); return; }
if (!empty($_SESSION['cart'])) foreach($_SESSION['cart'] as $id=>$quantity){$p=find_row('products.txt',0,$id);if(isset($p[0]))$total+=(float)$p[4]*(int)$quantity;}
if(isset($_POST['place_order'])){
    require_csrf();
    $allowed=array('Cash on Delivery','Demo Card');
    $method=in_array($_POST['method'],$allowed,true)?$_POST['method']:'Cash on Delivery';
    $result=place_order((int)$_SESSION['user_id'],$method,isset($_SESSION['cart'])?$_SESSION['cart']:array());
    if($result['ok']){unset($_SESSION['cart'],$_SESSION['checkout']);audit('Placed order '.$result['id']);$placed=true;}else{$message=$result['message'];}
}
?>
<?php if($placed){?><section class="center-card"><div class="success-mark">&#10003;</div><span class="eyebrow">Order received</span><h1>Thank you.</h1><p>Your classroom-demo order has been recorded. No real payment was collected.</p><a class="button" href="store.php">Back to shop</a></section><?php }else{?><section class="page-heading compact"><span class="eyebrow">Step 2 of 2</span><h1>Payment method</h1><p>No payment API is connected, as required by the project brief.</p></section><?php if($message!=='')echo '<div class="notice">'.h($message).'</div>';?><form class="checkout panel form" method="post"><?php echo csrf_field(); ?><label class="radio"><input type="radio" name="method" value="Cash on Delivery" checked><span><strong>Cash on delivery</strong><small>Pay when the classroom-demo order arrives.</small></span></label><label class="radio"><input type="radio" name="method" value="Demo Card"><span><strong>Demo card payment</strong><small>Simulation only; no card details are requested.</small></span></label><div class="pay-total"><span>Order total</span><strong>&#8369;<?php echo number_format($total,2); ?></strong></div><button class="button full" name="place_order">Place demo order</button></form><?php } ?>
<?php include('includes/footer.php'); ?>
