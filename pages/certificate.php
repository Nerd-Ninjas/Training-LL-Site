 <?php
	  require_once("../includes/config.php");
	  require_once("../includes/classes/Account.php");
	  
	  // Check if user is logged in
	  if(!isset($_SESSION["username"]) || empty($_SESSION["username"])){
		  header("Location:../login.php");
		  exit;
	  }
	  
	  $account = new Account($con);
	  $id=$_GET['id'];
	  $pdfViewer = $account->getCertificate($id);
	?>
<html>
  <head>
    <meta http-equiv="Content-Type" content="text/html; charset=windows-1252">
   </head>
  <body>
    <div class="page-header">
      <div>
	<?php
	if($pdfViewer['type']=='CC'){
	$type='Course Completion';
	}else{
	$type=$pdfViewer['type'];
	}
	?>
        <h1 class="page-title">Certificate  - <?php echo $type;?></h1>
      </div>
      <div class="ms-auto pageheader-btn">
        <ol class="breadcrumb">
          <li class="breadcrumb-item">
            <a href="javascript:void(0);">Certificate </a>
          </li>
          <li class="breadcrumb-item active" aria-current="page"><?php echo $type;?></li>
        </ol>
      </div>
    </div>
    <!-- PAGE-HEADER END -->
    <!-- ROW-1 OPEN -->
<?php
	if($pdfViewer){
		 $filepath=$pdfViewer['filePath'];
	  ?>
    <div class="row">
      <div class="card">
        <div class="card-body" style="height:400px;">
		  <iframe src="<?php echo $filepath; ?>" width="100%" height="100%">
        </div>
      </div>

<?php } ?>	
  </body>
</html>