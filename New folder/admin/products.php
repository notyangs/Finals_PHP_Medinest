<?php
$page_title='Manage products'; $base='../'; include('../includes/header.php');
if(!is_admin()){echo '<section class="center-card"><h1>Admin access only.</h1></section>';include('../includes/footer.php');return;}
$message=''; $edit=array();
if(isset($_GET['edit'])) $edit=find_row('products.txt',0,(int)$_GET['edit']);
if(isset($_POST['delete'])){
    require_csrf(); $deleteId=(int)$_POST['delete_id']; $rows=read_rows('products.txt'); $kept=array(); $deleted=false;
    foreach($rows as $row){if((int)$row[0]===$deleteId){$deleted=true;}else{$kept[]=$row;}}
    if($deleted){save_rows('products.txt',$kept);audit('Deleted product '.$deleteId);$message='Product deleted.';}else{$message='Product was not found.';}
}
if(isset($_POST['save'])){
    require_csrf(); $id=(int)$_POST['id'];
    $item=array($id,safe_field($_POST['name']),safe_field($_POST['category']),safe_field($_POST['description']),max(0,(float)$_POST['price']),max(0,(int)$_POST['stock']),safe_field($_POST['type']));
    $rows=read_rows('products.txt');$found=false;
    foreach($rows as $key=>$row){if((int)$row[0]===$id){$rows[$key]=$item;$found=true;}}
    if($found){save_rows('products.txt',$rows);audit('Updated product '.$id);}else{append_row('products.txt',$item);audit('Added product '.$id);}
    $message='Product saved.';$edit=array();
}
?>
<section class="admin-heading"><div><span class="eyebrow">Admin / Catalog</span><h1>Products &amp; stock</h1></div><a class="text-link" href="index.php">&larr; Dashboard</a></section>
<?php if($message!=='') echo '<div class="notice success">'.h($message).'</div>'; ?>
<section class="manage-layout">
<form class="panel form" method="post"><?php echo csrf_field(); ?><h2><?php echo isset($edit[0])?'Update product':'Add product';?></h2>
<label>Product ID<input name="id" type="number" min="1" value="<?php echo isset($edit[0])?(int)$edit[0]:time();?>" required></label>
<label>Name<input name="name" value="<?php echo isset($edit[1])?h($edit[1]):'';?>" required></label>
<div class="two"><label>Category<select name="category"><option>Pain Relief</option><option>Vitamins</option><option>First Aid</option><option>Health Devices</option></select></label><label>Visual type<select name="type"><option value="tablet">Tablet</option><option value="vitamin">Vitamin</option><option value="firstaid">First aid</option><option value="device">Device</option></select></label></div>
<label>Description<textarea name="description" required><?php echo isset($edit[3])?h($edit[3]):'';?></textarea></label>
<div class="two"><label>Price<input name="price" type="number" min="0" step="0.01" value="<?php echo isset($edit[4])?h($edit[4]):'';?>" required></label><label>Stock<input name="stock" type="number" min="0" value="<?php echo isset($edit[5])?(int)$edit[5]:'';?>" required></label></div>
<button class="button full" name="save">Save product</button></form>
<div class="table-wrap"><table><tr><th>Product</th><th>Price</th><th>Stock</th><th>Actions</th></tr><?php foreach(read_rows('products.txt') as $p){?><tr><td><strong><?php echo h($p[1]);?></strong><small><?php echo h($p[2]);?></small></td><td>&#8369;<?php echo number_format((float)$p[4],2);?></td><td><?php echo (int)$p[5];?></td><td><div class="table-actions"><a href="products.php?edit=<?php echo (int)$p[0];?>">Edit</a><form method="post" onsubmit="return confirm('Delete this product?');"><?php echo csrf_field(); ?><input type="hidden" name="delete_id" value="<?php echo (int)$p[0];?>"><button class="danger-link" name="delete" type="submit">Delete</button></form></div></td></tr><?php }?></table></div>
</section>
<?php include('../includes/footer.php'); ?>

