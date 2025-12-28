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
	  $username=$_GET['ui'];
	  $assignmentPoints = $account->getToolAssignment($id);
	  $assignmentPointsTwo = $account->getToolAssignmentTwo($id);
	  $assignmentHeading = $account->getAssignmentHeading($id);
	  $assignmentHeadingTwo = $account->getAssignmentHeadingTwo($id);
	  $assignmentRubrics = $account->getAssignmentRubrics($id);
	  $assignmentFileLink = $account->getAssignmentFileLink($id,$username);
	  $titleQuery = $account->getToolTitle($id);
	  $title=$titleQuery['tool_name'];
	
	?>
<html>
  <head>
    <meta http-equiv="Content-Type" content="text/html; charset=windows-1252">
    <style>
      /* cyrillic-ext */
      @font-face {
        font-family: 'Roboto';
        font-style: normal;
        font-weight: 300;
        font-display: swap;
        src: url(https://fonts.gstatic.com/s/roboto/v20/KFOlCnqEu92Fr1MmSU5fCRc4EsA.woff2) format('woff2');
        unicode-range: U+0460-052F, U+1C80-1C88, U+20B4, U+2DE0-2DFF, U+A640-A69F, U+FE2E-FE2F;
      }

      /* cyrillic */
      @font-face {
        font-family: 'Roboto';
        font-style: normal;
        font-weight: 300;
        font-display: swap;
        src: url(https://fonts.gstatic.com/s/roboto/v20/KFOlCnqEu92Fr1MmSU5fABc4EsA.woff2) format('woff2');
        unicode-range: U+0400-045F, U+0490-0491, U+04B0-04B1, U+2116;
      }

      /* greek-ext */
      @font-face {
        font-family: 'Roboto';
        font-style: normal;
        font-weight: 300;
        font-display: swap;
        src: url(https://fonts.gstatic.com/s/roboto/v20/KFOlCnqEu92Fr1MmSU5fCBc4EsA.woff2) format('woff2');
        unicode-range: U+1F00-1FFF;
      }

      /* greek */
      @font-face {
        font-family: 'Roboto';
        font-style: normal;
        font-weight: 300;
        font-display: swap;
        src: url(https://fonts.gstatic.com/s/roboto/v20/KFOlCnqEu92Fr1MmSU5fBxc4EsA.woff2) format('woff2');
        unicode-range: U+0370-03FF;
      }

      /* vietnamese */
      @font-face {
        font-family: 'Roboto';
        font-style: normal;
        font-weight: 300;
        font-display: swap;
        src: url(https://fonts.gstatic.com/s/roboto/v20/KFOlCnqEu92Fr1MmSU5fCxc4EsA.woff2) format('woff2');
        unicode-range: U+0102-0103, U+0110-0111, U+0128-0129, U+0168-0169, U+01A0-01A1, U+01AF-01B0, U+1EA0-1EF9, U+20AB;
      }

      /* latin-ext */
      @font-face {
        font-family: 'Roboto';
        font-style: normal;
        font-weight: 300;
        font-display: swap;
        src: url(https://fonts.gstatic.com/s/roboto/v20/KFOlCnqEu92Fr1MmSU5fChc4EsA.woff2) format('woff2');
        unicode-range: U+0100-024F, U+0259, U+1E00-1EFF, U+2020, U+20A0-20AB, U+20AD-20CF, U+2113, U+2C60-2C7F, U+A720-A7FF;
      }

      /* latin */
      @font-face {
        font-family: 'Roboto';
        font-style: normal;
        font-weight: 300;
        font-display: swap;
        src: url(https://fonts.gstatic.com/s/roboto/v20/KFOlCnqEu92Fr1MmSU5fBBc4.woff2) format('woff2');
        unicode-range: U+0000-00FF, U+0131, U+0152-0153, U+02BB-02BC, U+02C6, U+02DA, U+02DC, U+2000-206F, U+2074, U+20AC, U+2122, U+2191, U+2193, U+2212, U+2215, U+FEFF, U+FFFD;
      }

      /* cyrillic-ext */
      @font-face {
        font-family: 'Roboto';
        font-style: normal;
        font-weight: 400;
        font-display: swap;
        src: url(https://fonts.gstatic.com/s/roboto/v20/KFOmCnqEu92Fr1Mu72xKOzY.woff2) format('woff2');
        unicode-range: U+0460-052F, U+1C80-1C88, U+20B4, U+2DE0-2DFF, U+A640-A69F, U+FE2E-FE2F;
      }

      /* cyrillic */
      @font-face {
        font-family: 'Roboto';
        font-style: normal;
        font-weight: 400;
        font-display: swap;
        src: url(https://fonts.gstatic.com/s/roboto/v20/KFOmCnqEu92Fr1Mu5mxKOzY.woff2) format('woff2');
        unicode-range: U+0400-045F, U+0490-0491, U+04B0-04B1, U+2116;
      }

      /* greek-ext */
      @font-face {
        font-family: 'Roboto';
        font-style: normal;
        font-weight: 400;
        font-display: swap;
        src: url(https://fonts.gstatic.com/s/roboto/v20/KFOmCnqEu92Fr1Mu7mxKOzY.woff2) format('woff2');
        unicode-range: U+1F00-1FFF;
      }

      /* greek */
      @font-face {
        font-family: 'Roboto';
        font-style: normal;
        font-weight: 400;
        font-display: swap;
        src: url(https://fonts.gstatic.com/s/roboto/v20/KFOmCnqEu92Fr1Mu4WxKOzY.woff2) format('woff2');
        unicode-range: U+0370-03FF;
      }

      /* vietnamese */
      @font-face {
        font-family: 'Roboto';
        font-style: normal;
        font-weight: 400;
        font-display: swap;
        src: url(https://fonts.gstatic.com/s/roboto/v20/KFOmCnqEu92Fr1Mu7WxKOzY.woff2) format('woff2');
        unicode-range: U+0102-0103, U+0110-0111, U+0128-0129, U+0168-0169, U+01A0-01A1, U+01AF-01B0, U+1EA0-1EF9, U+20AB;
      }

      /* latin-ext */
      @font-face {
        font-family: 'Roboto';
        font-style: normal;
        font-weight: 400;
        font-display: swap;
        src: url(https://fonts.gstatic.com/s/roboto/v20/KFOmCnqEu92Fr1Mu7GxKOzY.woff2) format('woff2');
        unicode-range: U+0100-024F, U+0259, U+1E00-1EFF, U+2020, U+20A0-20AB, U+20AD-20CF, U+2113, U+2C60-2C7F, U+A720-A7FF;
      }

      /* latin */
      @font-face {
        font-family: 'Roboto';
        font-style: normal;
        font-weight: 400;
        font-display: swap;
        src: url(https://fonts.gstatic.com/s/roboto/v20/KFOmCnqEu92Fr1Mu4mxK.woff2) format('woff2');
        unicode-range: U+0000-00FF, U+0131, U+0152-0153, U+02BB-02BC, U+02C6, U+02DA, U+02DC, U+2000-206F, U+2074, U+20AC, U+2122, U+2191, U+2193, U+2212, U+2215, U+FEFF, U+FFFD;
      }

      /* cyrillic-ext */
      @font-face {
        font-family: 'Roboto';
        font-style: normal;
        font-weight: 500;
        font-display: swap;
        src: url(https://fonts.gstatic.com/s/roboto/v20/KFOlCnqEu92Fr1MmEU9fCRc4EsA.woff2) format('woff2');
        unicode-range: U+0460-052F, U+1C80-1C88, U+20B4, U+2DE0-2DFF, U+A640-A69F, U+FE2E-FE2F;
      }

      /* cyrillic */
      @font-face {
        font-family: 'Roboto';
        font-style: normal;
        font-weight: 500;
        font-display: swap;
        src: url(https://fonts.gstatic.com/s/roboto/v20/KFOlCnqEu92Fr1MmEU9fABc4EsA.woff2) format('woff2');
        unicode-range: U+0400-045F, U+0490-0491, U+04B0-04B1, U+2116;
      }

      /* greek-ext */
      @font-face {
        font-family: 'Roboto';
        font-style: normal;
        font-weight: 500;
        font-display: swap;
        src: url(https://fonts.gstatic.com/s/roboto/v20/KFOlCnqEu92Fr1MmEU9fCBc4EsA.woff2) format('woff2');
        unicode-range: U+1F00-1FFF;
      }

      /* greek */
      @font-face {
        font-family: 'Roboto';
        font-style: normal;
        font-weight: 500;
        font-display: swap;
        src: url(https://fonts.gstatic.com/s/roboto/v20/KFOlCnqEu92Fr1MmEU9fBxc4EsA.woff2) format('woff2');
        unicode-range: U+0370-03FF;
      }

      /* vietnamese */
      @font-face {
        font-family: 'Roboto';
        font-style: normal;
        font-weight: 500;
        font-display: swap;
        src: url(https://fonts.gstatic.com/s/roboto/v20/KFOlCnqEu92Fr1MmEU9fCxc4EsA.woff2) format('woff2');
        unicode-range: U+0102-0103, U+0110-0111, U+0128-0129, U+0168-0169, U+01A0-01A1, U+01AF-01B0, U+1EA0-1EF9, U+20AB;
      }

      /* latin-ext */
      @font-face {
        font-family: 'Roboto';
        font-style: normal;
        font-weight: 500;
        font-display: swap;
        src: url(https://fonts.gstatic.com/s/roboto/v20/KFOlCnqEu92Fr1MmEU9fChc4EsA.woff2) format('woff2');
        unicode-range: U+0100-024F, U+0259, U+1E00-1EFF, U+2020, U+20A0-20AB, U+20AD-20CF, U+2113, U+2C60-2C7F, U+A720-A7FF;
      }

      /* latin */
      @font-face {
        font-family: 'Roboto';
        font-style: normal;
        font-weight: 500;
        font-display: swap;
        src: url(https://fonts.gstatic.com/s/roboto/v20/KFOlCnqEu92Fr1MmEU9fBBc4.woff2) format('woff2');
        unicode-range: U+0000-00FF, U+0131, U+0152-0153, U+02BB-02BC, U+02C6, U+02DA, U+02DC, U+2000-206F, U+2074, U+20AC, U+2122, U+2191, U+2193, U+2212, U+2215, U+FEFF, U+FFFD;
      }

      /* cyrillic-ext */
      @font-face {
        font-family: 'Roboto';
        font-style: normal;
        font-weight: 700;
        font-display: swap;
        src: url(https://fonts.gstatic.com/s/roboto/v20/KFOlCnqEu92Fr1MmWUlfCRc4EsA.woff2) format('woff2');
        unicode-range: U+0460-052F, U+1C80-1C88, U+20B4, U+2DE0-2DFF, U+A640-A69F, U+FE2E-FE2F;
      }

      /* cyrillic */
      @font-face {
        font-family: 'Roboto';
        font-style: normal;
        font-weight: 700;
        font-display: swap;
        src: url(https://fonts.gstatic.com/s/roboto/v20/KFOlCnqEu92Fr1MmWUlfABc4EsA.woff2) format('woff2');
        unicode-range: U+0400-045F, U+0490-0491, U+04B0-04B1, U+2116;
      }

      /* greek-ext */
      @font-face {
        font-family: 'Roboto';
        font-style: normal;
        font-weight: 700;
        font-display: swap;
        src: url(https://fonts.gstatic.com/s/roboto/v20/KFOlCnqEu92Fr1MmWUlfCBc4EsA.woff2) format('woff2');
        unicode-range: U+1F00-1FFF;
      }

      /* greek */
      @font-face {
        font-family: 'Roboto';
        font-style: normal;
        font-weight: 700;
        font-display: swap;
        src: url(https://fonts.gstatic.com/s/roboto/v20/KFOlCnqEu92Fr1MmWUlfBxc4EsA.woff2) format('woff2');
        unicode-range: U+0370-03FF;
      }

      /* vietnamese */
      @font-face {
        font-family: 'Roboto';
        font-style: normal;
        font-weight: 700;
        font-display: swap;
        src: url(https://fonts.gstatic.com/s/roboto/v20/KFOlCnqEu92Fr1MmWUlfCxc4EsA.woff2) format('woff2');
        unicode-range: U+0102-0103, U+0110-0111, U+0128-0129, U+0168-0169, U+01A0-01A1, U+01AF-01B0, U+1EA0-1EF9, U+20AB;
      }

      /* latin-ext */
      @font-face {
        font-family: 'Roboto';
        font-style: normal;
        font-weight: 700;
        font-display: swap;
        src: url(https://fonts.gstatic.com/s/roboto/v20/KFOlCnqEu92Fr1MmWUlfChc4EsA.woff2) format('woff2');
        unicode-range: U+0100-024F, U+0259, U+1E00-1EFF, U+2020, U+20A0-20AB, U+20AD-20CF, U+2113, U+2C60-2C7F, U+A720-A7FF;
      }

      /* latin */
      @font-face {
        font-family: 'Roboto';
        font-style: normal;
        font-weight: 700;
        font-display: swap;
        src: url(https://fonts.gstatic.com/s/roboto/v20/KFOlCnqEu92Fr1MmWUlfBBc4.woff2) format('woff2');
        unicode-range: U+0000-00FF, U+0131, U+0152-0153, U+02BB-02BC, U+02C6, U+02DA, U+02DC, U+2000-206F, U+2074, U+20AC, U+2122, U+2191, U+2193, U+2212, U+2215, U+FEFF, U+FFFD;
      }
    </style>
    <style>
      .ucbzx-button-wrapper {
        position: absolute !important;
        z-index: 1000 !important;
        top: 0 !important;
        right: 0 !important;
      }

      .ucbzx-button {
        display: block;
        border: none !important;
        /* outline: none !important; */
        background: #3c3c54 !important;
        padding: 0 !important;
        border-radius: 5px;
        width: 36px !important;
        height: 36px !important;
      }

      .ucbzx-button:active {
        border: none !important;
      }

      .ucbzx-button:disabled {
        cursor: default !important;
      }

      .ucbzx-download-img {
        display: block !important;
        width: 36px !important;
        height: 36px !important;
        cursor: pointer !important;
        margin: 0 !important;
      }

      .ucbzx-hide {
        display: none !important;
      }

      .ucbzx-loader {
        display: block;
        box-sizing: content-box !important;
        width: 30px !important;
        height: 30px !important;
        padding: 3px !important;
        animation: rotate 1s linear infinite;
        margin: 0 !important;
      }

      /*
    RESOLUTIONS CHOICE
*/
      .ucbzx-resolutions-list {
        position: absolute !important;
        top: 36px !important;
        right: 0 !important;
        background: #3c3c54 !important;
        color: #dbdbdb !important;
        font-family: 'Roboto', sans-serif !important;
        font-size: 14px !important;
        list-style: none !important;
        padding: 0 !important;
        margin: 0 !important;
      }

      .ucbzx-resolutions-list li {
        margin: 4px !important;
        padding: 0 !important;
        font-weight: bold !important;
        white-space: nowrap !important;
        width: 130px !important;
        height: 30px !important;
        display: flex !important;
        justify-content: space-between !important;
        align-items: center !important;
      }

      .ucbzx-list-item-tickbox {
        width: 20px !important;
        height: 20px !important;
        background: rgba(255, 255, 255, 0.219) !important;
        /* border-radius: 5px !important; */
        display: flex !important;
        justify-content: center !important;
        align-items: center !important;
        /* font-size: 17px !important; */
        /* border: 1px solid rgba(255, 255, 255, 0.835) !important; */
        cursor: pointer !important;
        margin-left: 4px !important;
      }

      .ucbzx-resolutions-list>li::before {
        display: none;
      }

      .ucbzx-list-item-tickbox:not(.ucbzx-list-item-tickbox-checked):hover {
        background: rgba(255, 255, 255, 0.37) !important;
      }

      .ucbzx-list-item-tickbox.ucbzx-list-item-tickbox-checked {
        cursor: default !important;
      }

      .ucbzx-list-item-tickbox-checked::after {
        content: "\2713" !important;
        color: white !important;
      }

      .ucbzx-list-item-text {
        /* border-radius: 5px !important; */
        flex-grow: 1 !important;
        display: flex !important;
        justify-content: center !important;
        align-items: center !important;
        cursor: pointer !important;
        height: 100% !important;
        margin-left: 10px !important;
      }

      .ucbzx-list-item-text:hover {
        background: rgba(255, 255, 255, 0.37) !important;
        color: white !important;
      }

      /* account for the fact that we got rid of the tickbox there */
      .ucbzx-resolutions-list li[data-type="captions"] .ucbzx-list-item-text {
        margin-left: 34px !important;
        font-style: italic;
      }

      /*
    MISC
*/
      .ucbzx-arrow-container {
        background: none !important;
        position: absolute !important;
        top: 36px !important;
        left: 0 !important;
        width: 0 !important;
        height: 0 !important;
        border-left: 18px solid transparent !important;
        border-right: 18px solid transparent !important;
        border-top: 18px solid #3c3c54 !important;
        /* border-bottom: none !important; */
        cursor: pointer !important;
      }

      .ucbzx-arrow-container::after {
        position: absolute !important;
        top: -18px !important;
        left: -4px !important;
        content: "" !important;
        border: 1px solid #dbdbdb !important;
        border-width: 0 3px 3px 0 !important;
        display: inline-block !important;
        padding: 3px !important;
        transform: rotate(45deg);
      }

      .ucbzx-button:focus,
      .ucbzx-arrow-container:focus,
      .ucbzx-resolutions-list:focus,
      .ucbzx-list-item-tickbox:focus,
      .ucbzx-list-item-text:focus {
        outline: 1px solid white !important;
      }

      @keyframes rotate {
        100% {
          transform: rotate(360deg);
        }
      }

      /*
    RESET STYLES
*/
      .ucbzx-button-wrapper button {
        border: none;
        margin: 0;
        padding: 0;
        width: auto;
        overflow: visible;
        background: transparent;
        color: inherit;
        font: inherit;
        outline: none;
        line-height: normal;
        -webkit-font-smoothing: inherit;
        -moz-osx-font-smoothing: inherit;
        -webkit-appearance: none;
      }

      .ucbzx-button-wrapper li,
      .ucbzx-button-wrapper li * {
        margin: 0;
        font: inherit;
        font-style: inherit;
        font-size: inherit;
      }

	ol.assignment li {
		padding: 5px;
		margin-left: 25px;
	}
	
	ul.general {
		padding: 10px  20px  20px 20px;
	}
	ul.general li {
		margin: 5px;
		list-style-type: circle;
	}
	 th, td {
	border-color: #6f42c1;
	}
	.fa-check-circle {
		color: green;
	}
    </style>
  </head>
  <body>
    <div class="page-header">
      <div>
        <h1 class="page-title">Assignment - <?php echo $title; ?></h1>
      </div>
      <div class="ms-auto pageheader-btn">
        <ol class="breadcrumb">
          <li class="breadcrumb-item">
            <a href="javascript:void(0);">Pages</a>
          </li>
          <li class="breadcrumb-item active" aria-current="page">Assignment - <?php echo $title; ?></li>
        </ol>
	
      </div>
    </div>
	<?php if($assignmentFileLink['approvedBy']){?>
	<!--<div class="breadcrumb"  style="text-align:right;">-->
	<div class="breadcrumb">
  		<p><i class="fa fa-check-circle fa-lg"></i> You're graded for your work <?php echo $assignmentFileLink['marks'];?>/<?php echo $assignmentFileLink['totalMarks'];?></p>
    </div>
	<?php } ?>
    <!-- PAGE-HEADER END -->
    <!-- ROW-1 OPEN -->
    <div class="row">
	<div class="card">
	<div class="card-body">
	<h5 style="font-weight:bold;">General Instructions</h5>
	<ul class="general">
		<li>Number of materials you can submit for this assignment: <b><?php if($assignmentHeadingTwo){ echo 2; }else{ echo 1;}?></b></li>
		<li>This assignment can be submitted <b>only once</b></li>
		<li>Grade points for this assignment: <b><?php echo $assignmentHeading['marks']; ?></b></li>
	</ul>

	</div>
	</div>
      <div class="card">
        <div class="card-body">
          <h5 style="font-weight:bold;">Task:</h5>
		  <p><?php echo $assignmentHeading['heading']; ?></p>
		  <ol class="assignment">
		  <?php foreach($assignmentPoints as $assignmentPoints ){ ?>
		  <li><?php echo $assignmentPoints['points']; ?></li>
		  <?php } ?>
		  </ol>
		  <?php if($assignmentHeadingTwo){?>
		  <p><?php echo $assignmentHeadingTwo['heading']; ?></p>
		  <ol class="assignment">
		  <?php foreach($assignmentPointsTwo as $assignmentPointsTwo ){ ?>
		  <li><?php echo $assignmentPointsTwo['points']; ?></li>
		  <?php } ?>
		  </ol>
		  <?php }?>
		  <p style="padding:5px;font-weight:bold;">How to submit:</p>
		  <p style="padding:10px;">Paste the share link. Then click on 'Submit'. </p>
		  <p style="padding:5px;font-weight:bold;">Rubrics</p>
		  <div class="table-responsive">
		  <table class="table text-nowrap text-md-nowrap table-bordered">
		  <thead>
		  <tr>
		  <th style="font-weight:bold;text-align:center;">S.No</th>
		  <th style="font-weight:bold;text-align:center;"><?php echo $title; ?> Rubric</th>
		  <th style="font-weight:bold;text-align:center;">Good (1 Mark)</th>
		  <th style="font-weight:bold;text-align:center;">Satisfactory (0.5 Mark)</th>
		  <th style="font-weight:bold;text-align:center;">Needs More Attention (0 mark)</th>
		  </tr>
		  </thead>
		  <tbody>
		  <?php 
		  $i=1;
		  foreach($assignmentRubrics as $assignmentRubrics){?>
		  <tr>
		  <td style="text-align:center;"><?php echo $i; ?></td>
		  <td><?php echo $assignmentRubrics['rubrics']; ?></td>
		  <td style="text-align:center;"><?php echo $assignmentRubrics['good']; ?></td>
		  <td style="text-align:center;"><?php echo $assignmentRubrics['satisfactory']; ?></td>
		  <td style="text-align:center;"><?php echo $assignmentRubrics['attention']; ?></td>
		  </tr>
		  <?php 
		  $i++;
		  } ?>
		  </tbody>
		  
		  </table>
		  </div>
		<?php  if($id=='MSTEEO'){ ?>
		  <p style="padding:5px;font-weight:bold;">Reference Materials</p>
			<p style="padding:5px;"><i class="fa fa-file-excel-o fa-lg"></i><span style="padding:10px;"> Excel Online Sheet for Assignment</span><a href="docs/preClassroom/Excel-Online-Sheet-for-Assignment.xlsx" target="_blank"><i class="fa fa-download fa-lg"></i></a></p>
		<?php } ?>	
			
		  <p style="padding:5px;font-weight:bold;">Your Assignment <span style="color:red;">*</span></p>	
		<p style="padding:5px;">Check the link before submitting. Once submitted cannot be reverted.</p>
		   <form id="form">
		   <div class="input-group">
		   <?php if(!$assignmentFileLink){ ?>
			<input type="text" class="form-control" id="fileLink" name="fileLink" placeholder="Copy your share link here" required>
			<input type="hidden" id="username" name="username" value="<?php echo $username; ?>">
			<input type="hidden" id="totalMarks" name="totalMarks" value="<?php echo $assignmentHeading['marks']; ; ?>">
			<input type="hidden" id="id"  name="id" value="<?php echo $id; ?>">
			<a id="submitButton" class="input-group-text btn btn-primary text-white" onclick="submit()">
            Submit
			</a> 
			</div>
		   <?php }else{?>	
		   <input type="text" style="background-color: #f0f0f0;" class="form-control" id="fileLink" name="fileLink" value="<?php echo $assignmentFileLink['fileLink'];?>" disabled>
		   <a id="submitButton" class="input-group-text btn btn-success text-white" style="pointer-events: none">
            Submit
			</a> 
			
			</div>
			<br>
			<p><span style="padding:5px;font-weight:bold;">File submitted on :</span> <?php echo $assignmentFileLink['uploadedDate']; ?></p>
			<p><span style="padding:5px;font-weight:bold;">Comments:</p> 
			<input type="text" style="background-color: #f0f0f0;" class="form-control" value="<?php echo $assignmentFileLink['comments']; ?>" readonly>
			 <?php }?>	
			</form>
	   
    <script>
        function submit() {
            let form = document.getElementById("form");
			let x=document.getElementById("fileLink").value;
			let y=document.getElementById("username").value;
			let z=document.getElementById("id").value;
			let m=document.getElementById("totalMarks").value;
			if(!x){
			alert("File link not Entered!");	
			}
			else{		
				function addView(fileLink, username, id, totalMarks) {
				$.post("process.php", { fileLink: fileLink, username: username, id: id, totalMarks: totalMarks }, function(data) {
				if(data !== null && data !== "") {
					alert(data);
						}
					})
				}
				addView(x, y, z,m);
				document.getElementById("fileLink").disabled = true;
				document.getElementById("submitButton").style.pointerEvents="none";
    
		   
			}
        }
    </script>
        </div>
      </div>
    </div>
	
  </body>
</html>