<?php
$page_title='Manage users';$base='../';include('../includes/header.php');
if(!is_admin()){echo '<section class="center-card"><h1>Admin access only.</h1></section>';include('../includes/footer.php');return;}
$message='';$edit=array();if(isset($_GET['edit']))$edit=find_row('users.txt',0,(int)$_GET['edit']);
if(isset($_POST['save'])){
    require_csrf();$id=(int)$_POST['id'];$plain=(string)$_POST['password'];$stored=$plain===''?'':password_hash($plain,PASSWORD_DEFAULT);$role=in_array($_POST['role'],array('buyer','admin'),true)?$_POST['role']:'buyer';
    $rows=read_rows('users.txt');$found=false;
    foreach($rows as $key=>$row){if((int)$row[0]===$id){if($stored==='')$stored=$row[3];$rows[$key]=array($id,safe_field($_POST['name']),strtolower(safe_field($_POST['email'])),$stored,safe_field($_POST['address']),safe_field($_POST['contact']),$role);$found=true;}}
    if($found){save_rows('users.txt',$rows);audit('Updated system user '.$id);$message='User saved.';}elseif($stored!==''){append_row('users.txt',array($id,safe_field($_POST['name']),strtolower(safe_field($_POST['email'])),$stored,safe_field($_POST['address']),safe_field($_POST['contact']),$role));audit('Added system user '.$id);$message='User saved.';}else{$message='A password is required for a new user.';}
    $edit=array();
}
?>
<section class="admin-heading"><div><span class="eyebrow">Admin / Access</span><h1>System users</h1></div><a class="text-link" href="index.php">&larr; Dashboard</a></section>
<?php if($message!=='')echo '<div class="notice success">'.h($message).'</div>'; ?>
<section class="manage-layout"><form class="panel form" method="post"><?php echo csrf_field(); ?><h2><?php echo isset($edit[0])?'Modify user':'Add user';?></h2>
<input type="hidden" name="id" value="<?php echo isset($edit[0])?(int)$edit[0]:time();?>"><label>Complete name<input name="name" value="<?php echo isset($edit[1])?h($edit[1]):'';?>" required></label><label>Email<input name="email" type="email" value="<?php echo isset($edit[2])?h($edit[2]):'';?>" required></label><label><?php echo isset($edit[0])?'New password (leave blank to keep current)':'Password';?><input name="password" type="password" minlength="8" <?php echo isset($edit[0])?'':'required';?>></label><div class="two"><label>Role<select name="role"><option value="buyer" <?php if(isset($edit[6])&&$edit[6]==='buyer')echo 'selected';?>>Buyer</option><option value="admin" <?php if(isset($edit[6])&&$edit[6]==='admin')echo 'selected';?>>Admin</option></select></label><label>Contact<input name="contact" value="<?php echo isset($edit[5])?h($edit[5]):'';?>" required></label></div><label>Address<input name="address" value="<?php echo isset($edit[4])?h($edit[4]):'';?>" required></label><button class="button full" name="save">Save user</button></form>
<div class="table-wrap"><table><tr><th>User</th><th>Role</th><th></th></tr><?php foreach(read_rows('users.txt') as $u){?><tr><td><strong><?php echo h($u[1]);?></strong><small><?php echo h($u[2]);?></small></td><td><?php echo h(ucfirst($u[6]));?></td><td><a href="users.php?edit=<?php echo (int)$u[0];?>">Edit</a></td></tr><?php }?></table></div></section>
<?php include('../includes/footer.php'); ?>

