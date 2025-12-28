<?php 
require_once("../includes/config.php");
require_once("../includes/classes/Account.php");

// Check if user is logged in
if(!isset($_SESSION["username"]) || empty($_SESSION["username"])){
	header("Location:../login.php");
	exit;
}

$account = new Account($con);
$course_id = $_GET['id'];
$username = $_GET['ui'];
$batchDetails = $account->getBatchDetails($username, $course_id);
$batch_unique_id = $batchDetails['batch_unique_id'];
$recordedVideos = $account->checkRecordedVideo($course_id, $batch_unique_id);
$courseDetails = $account->getCoursesDetails($course_id);
$courseName = $courseDetails['course_name'];
?>

<!-- PAGE-HEADER -->
<div class="page-header">
  <div>
    <h1 class="page-title">Recorded Videos</h1>
  </div>
  <div class="ms-auto pageheader-btn">
    <ol class="breadcrumb">
      <li class="breadcrumb-item">
        <a href="javascript:void(0);">Videos</a>
      </li>
      <li class="breadcrumb-item">
        <a href="javascript:void(0);">Videos</a>
      </li>
      <li class="breadcrumb-item active" aria-current="page">Recorded Videos</li>
    </ol>
  </div>
</div>
<!-- PAGE-HEADER END -->

<!-- Row -->
<div class="row row-sm">
  <div class="col-lg-12">
    <div class="card custom-card">
      <div class="card-header border-bottom">
        <h3 class="card-title">Recorded Video List</h3>
      </div>
      <div class="card-body">
	<?php if ($recordedVideos) { ?>
        <div class="table-responsive">
          <table class="table border text-nowrap text-md-nowrap table-bordered">
            <thead>
              <tr>
                <th>#</th>
                <th>Date</th>
                <th>Session Description</th>
                <th>Action</th>
              </tr>
            </thead>
            <tbody>
	<?php 
	$i = 1;
	foreach ($recordedVideos as $recordedVideo) { ?>
              <tr>
                <td><?php echo $i; ?></td>
                <td><?php echo $recordedVideo['date_of_session']; ?></td>
                <td><?php echo $recordedVideo['video_description']; ?></td>
                <td>
                  <a href="recorded_videos.php?id=<?php echo $course_id; ?>&&ui=<?php echo $username; ?>" onclick="openVideoPopup('<?php echo $recordedVideo['videoId']; ?>')" class="btn btn-icon btn-primary">
                    <i class="fe fe-play"></i>
                  </a>
                </td>
              </tr>
	<?php $i++; } ?>
            </tbody>
          </table>
        </div>
	<?php } else { ?>
	<p style="text-align:center;font-weight:bold;">No Videos to show</p>
	<?php } ?>
      </div>
    </div>
  </div>
</div>
<!-- End Row -->

<script>
function openVideoPopup(videoId) {
    var popup = window.open("pages/recorded_single_video.php?id=" + videoId, "VideoPopup", "width=800,height=600,scrollbars=no,resizable=no");
    if (popup) {
        popup.focus();
    } else {
        alert("Please allow pop-ups for this website.");
    }
}
</script>
