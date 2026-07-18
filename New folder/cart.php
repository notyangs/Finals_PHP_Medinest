<?php
$page_title="Cart"; include("includes/header.php");
if (isset($_POST["remove"])) { require_csrf(); $id=(int)$_POST["remove"]; if(isset($_SESSION["cart"][$id])) { unset($_SESSION["cart"][$id]); audit("Removed product " . $id . " from cart"); } }
$total=0;
?>
<section class="page-heading compact"><span class="eyebrow">Your order</span><h1>Shopping cart</h1></section>
<section class="cart-layout"><div class="cart-list">
<?php if (!isset($_SESSION["cart"]) || cart_count()==0) { ?><div class="empty"><h2>Your cart is resting.</h2><p>Add a few pharmacy essentials to get started.</p><a class="button" href="store.php">Shop products</a></div><?php } else { foreach($_SESSION["cart"] as $id=>$quantity) { $product=find_row("products.txt",0,$id); if(isset($product[0])) { $subtotal=$product[4]*$quantity; $total=$total+$subtotal; ?>
<article class="cart-item"><div class="mini-visual">+</div><div><small><?php echo h($product[2]); ?></small><h3><?php echo h($product[1]); ?></h3><p>Quantity: <?php echo (int)$quantity; ?></p></div><strong>&#8369;<?php echo number_format($subtotal,2); ?></strong><form method="post"><?php echo csrf_field(); ?><button class="danger-link" name="remove" value="<?php echo (int)$id; ?>" type="submit">Remove</button></form></article>
<?php }}} ?></div><aside class="summary"><h2>Order summary</h2><p><span>Subtotal</span><strong>&#8369;<?php echo number_format($total,2); ?></strong></p><p><span>Delivery</span><strong>Free</strong></p><hr><p class="grand"><span>Total</span><strong>&#8369;<?php echo number_format($total,2); ?></strong></p><?php if($total>0){ ?><a class="button full" href="checkout.php">Continue to checkout</a><?php } ?></aside></section>
<?php include("includes/footer.php"); ?>
