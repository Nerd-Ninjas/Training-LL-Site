<?php
require_once("../includes/config.php");
require_once("../includes/classes/Account.php");
$account = new Account($con);
$referenceId=$_GET['id'];
$receipt = $account->getReceiptDetails($referenceId);
$username=$receipt['username'];
$course_id=$receipt['course_id'];
$userDetails=$account->getUserDetails($username);
$courseDetails=$account->getCoursesDetails($course_id);
$courseName=$courseDetails['course_name'];

?>
<!-- PAGE-HEADER -->
<div class="page-header">
  <div>
    <h1 class="page-title">Receipt-Details</h1>
  </div>
  <div class="ms-auto pageheader-btn">
    <ol class="breadcrumb">
      <li class="breadcrumb-item">
        <a href="javascript:void(0);">Receipts</a>
      </li>
      <li class="breadcrumb-item active" aria-current="page">Receipt Details</li>
    </ol>
  </div>
</div>
<!-- PAGE-HEADER END -->
<!-- ROW-1 OPEN -->
<div class="row">
  <div class="col-md-12">
    <div class="card">
      <div class="card-body">
        <div class="clearfix">
          <div class="float-start">
            <h3 class="card-title mb-0">#<?php echo $receipt['receiptId'];?></h3>
          </div>
          <div class="float-end">
            <h3 class="card-title">Date: <?php echo $receipt['dateOfPayment'];?></h3>
          </div>
        </div>
        <hr>
        <div class="row">
          <div class="col-lg-6 ">
            <p class="h3">Receipt From:</p>
            <address><b>Learnlike</b><br>3D, Radhakrishna Enclave, Trichy Road<br>Ramanathapuram, Coimbatore <br>India, 641045 <br>support@learnlike.co.in<br>GSTIN:33AAJFL7696L1ZK</address>
          </div>
          <div class="col-lg-6 text-end">
            <p class="h3">Receipt To:</p>
            <address><b><?php echo $userDetails['firstName'].' '.$userDetails['lastName'];?></b><br><?php echo $userDetails['mobileNumber'];?><br><?php echo $userDetails['email'];?></address>
          </div>
        </div>
        <div class="table-responsive push">
          <table class="table table-bordered table-hover mb-0 text-nowrap border-bottom">
            <tbody>
              <tr class=" ">
                <th class="text-center"></th>
                <th>Description</th>
                <th class="text-center">Quantity</th>
                <th class="text-end">Unit Price</th>
                <th class="text-end">Sub Total</th>
              </tr>
              <tr>
                <td class="text-center">1</td>
                <td>
                  <p class="font-w600 mb-1"><?php echo $courseName; ?></p>
                  <div class="text-muted">
		   <?php if($receipt['installment']==1){ ?>	
                    <div class="text-muted">1<sup>st</sup> Installment</div>
		   <?php }elseif($receipt['installment']==2){ ?>
			<div class="text-muted">2<sup>nd</sup> Installment</div>
		<?php }else{ ?>
			<div class="text-muted">Full Payment</div>

		<?php } ?>
                  </div>
                </td>
                <td class="text-center">1</td>
                <td class="text-end">&#x20B9;<?php echo $receipt['amount'];?></td>
                <td class="text-end">&#x20B9;<?php echo $receipt['amount'];?></td>
              </tr>
            
              <tr>
                <td colspan="4" class="fw-bold text-uppercase text-end">Total</td>
                <td class="fw-bold text-end h4">&#x20B9;<?php echo $receipt['amount'];?></td>
              </tr>
            </tbody>
          </table>
	<br>
	<div class="row">
	<p>Mode: <?php echo $receipt['mode'];?></p>
	<p>Transaction/Reference Id: <?php echo $receipt['referenceId'];?></p>
	<p>Time of Payment: <?php echo $receipt['timeOfPayment'];?> HRS</p>

	</div>
        </div>
      </div>
      <div class="card-footer text-end">
         <button type="button" class="btn btn-info mb-1" onclick="javascript:window.print();">
          <i class="si si-printer"></i> Print Receipt </button>
      </div>
    </div>
  </div>
  <!-- COL-END -->
</div>
<!-- ROW-1 CLOSED -->