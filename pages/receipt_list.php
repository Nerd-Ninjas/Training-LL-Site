<?php
require_once("../includes/config.php");
require_once("../includes/classes/Account.php");
$account = new Account($con);
$course_id=$_GET['id'];
$username=$_GET['ui'];
$receiptDetails = $account->getPaymentReceipts($course_id,$username);
$courseDetails=$account->getCoursesDetails($course_id);
$courseName=$courseDetails['course_name'];

?>
<!-- PAGE-HEADER -->
<div class="page-header">
  <div>
    <h1 class="page-title">Receipts List</h1>
  </div>
  <div class="ms-auto pageheader-btn">
    <ol class="breadcrumb">
      <li class="breadcrumb-item">
        <a href="javascript:void(0);">Certification & Receipts</a>
      </li>
      <li class="breadcrumb-item">
        <a href="javascript:void(0);">Receipts</a>
      </li>
	
      <li class="breadcrumb-item active" aria-current="page">Receipt List</li>
    </ol>
  </div>
</div>
<!-- PAGE-HEADER END -->
<!-- Row -->
<div class="row row-sm">
  <div class="col-lg-12">
    <div class="card custom-card">
      <div class="card-header border-bottom">
        <h3 class="card-title">Payment Receipts</h3>
      </div>
      <div class="card-body">
	<?php if($receiptDetails){ ?>
        <div class="table-responsive">
          <table class="table border text-nowrap text-md-nowrap table-bordered">
            <thead>
              <tr>
                <th>#</th>
                <th>Course Name</th>
                <th>Reference Id</th>
                <th>Date</th>
		<th>Amount</th>
		<th>Action</th>
              </tr>
            </thead>
            <tbody>
	<?php foreach($receiptDetails as $receiptDetails){ ?>
              <tr>
                <td><?php echo $receiptDetails['receiptId']; ?></td>
                <td><?php echo $courseName; ?> - 

		<?php if($receiptDetails['installment']==1){ ?>	
                    1<sup>st</sup> Installment
		   <?php }elseif($receiptDetails['installment']==2){ ?>
			2<sup>nd</sup> Installment
		<?php }else{ ?>
			Full Payment

		<?php } ?>
		</td>
		<td><?php echo $receiptDetails['referenceId']; ?></td>
                <td><?php echo $receiptDetails['dateOfPayment']; ?></td>
		<td>&#8377;<?php echo $receiptDetails['amount']; ?></td>
		<td><a href="receipt.php?id=<?php echo $receiptDetails['receiptId'];?>" class="btn btn-icon  btn-primary"><i class="fe fe-search"></i></a></td>
              </tr>
	<?php } ?>
             </tbody>
          </table>
        </div>
	<?php }else{ ?>
	<p style="text-align:center;font-weight:bold;">No receipts to show</p>

<?php } ?>

      </div>
    </div>
  </div>
</div>
<!-- End Row -->
