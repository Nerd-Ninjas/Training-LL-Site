<?php
require_once("../includes/config.php");
require_once("../includes/classes/Account.php");
$account = new Account($con);
$course_id=$_GET['id'];
$username=$_GET['ui'];
$certificateDetails = $account->getCertificates($course_id,$username);
$courseDetails=$account->getCoursesDetails($course_id);
$courseName=$courseDetails['course_name'];

?>
<!-- PAGE-HEADER -->
<div class="page-header">
  <div>
    <h1 class="page-title">Certificates</h1>
  </div>
  <div class="ms-auto pageheader-btn">
    <ol class="breadcrumb">
      <li class="breadcrumb-item">
        <a href="javascript:void(0);">Certificates & Reciepts</a>
      </li>
	<li class="breadcrumb-item">
        <a href="javascript:void(0);">Certificates</a>
      </li>

      <li class="breadcrumb-item active" aria-current="page">Certificates List</li>
    </ol>
  </div>
</div>
<!-- PAGE-HEADER END -->
<!-- Row -->
<div class="row row-sm">
  <div class="col-lg-12">
    <div class="card custom-card">
      <div class="card-header border-bottom">
        <h3 class="card-title">Certificate List</h3>
      </div>
      <div class="card-body">
	<?php if($certificateDetails){ ?>
        <div class="table-responsive">
          <table class="table border text-nowrap text-md-nowrap table-bordered">
            <thead>
              <tr>
                <th>#</th>
                <th>Course Name</th>
                <th>Reference Id</th>
		<th>Type</th>
                <th>Date of Issue</th>
		<th>Action</th>
              </tr>
            </thead>
            <tbody>
		
	<?php 
		$i=1;
		foreach($certificateDetails as $certificateDetails){ ?>
              <tr>
                <td><?php echo $i; ?></td>
                <td><?php echo $courseName; ?></td>
		<td><?php echo $certificateDetails['certificateId']; ?></td>
		<td><?php echo $certificateDetails['type']; ?></td>
                <td><?php echo $certificateDetails['dateOfIssue']; ?></td>
		<td><a href="certificate.php?id=<?php echo $certificateDetails['certificateId'];?>" class="btn btn-icon  btn-primary"><i class="fe fe-search"></i></a></td>
              </tr>
		
		<?php
		$i++;
        	 } ?>
             </tbody>
          </table>
        </div>
	<?php }else{ ?>
	<p style="text-align:center;font-weight:bold;">No Certificates to show</p>

<?php } ?>

      </div>
    </div>
  </div>
</div>
<!-- End Row -->
