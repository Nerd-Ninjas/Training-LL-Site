	<!--APP-SIDEBAR-->
            <div class="sticky">
                <div class="app-sidebar__overlay" data-bs-toggle="sidebar"></div>
				<div class="app-sidebar">
					<div class="side-header">
						<a class="header-brand1" href="main.php?ci=<?php echo $_SESSION['course_id']; ?>">
							<img src="assets/images/brand/full-logo-dark.png" class="header-brand-img desktop-logo" alt="logo">
							<img src="assets/images/brand/LL-logo-light.png" class="header-brand-img toggle-logo" alt="logo">
							<img src="assets/images/brand/LL-logo-light.png" class="header-brand-img light-logo" alt="logo">
							<img src="assets/images/brand/full-logo-light.png" class="header-brand-img light-logo1" alt="logo">						</a><!-- LOGO -->
					</div>
					<div class="main-sidemenu">
                        <div class="slide-left disabled" id="slide-left"><svg xmlns="http://www.w3.org/2000/svg"
                                fill="#7b8191" width="24" height="24" viewBox="0 0 24 24">
                                <path d="M13.293 6.293 7.586 12l5.707 5.707 1.414-1.414L10.414 12l4.293-4.293z" />
                            </svg>
						</div>
						<?php 
						$welcomeScreen=$account->welcomePageContents($course_id);
						
						?>
						<ul class="side-menu">
							<li>
								<h3>Menu</h3>
							</li>
							<li class="slide">
								<a class="side-menu__item has-link" data-bs-toggle="slide" href="index.php">
									<?php echo $welcomeSvg=$welcomeScreen['svg']; ?>
									<span class="side-menu__label"><?php echo $title=$welcomeScreen['title']; ?></span>
								</a>
							</li>
							<li>
								<h3>Topics</h3>
							</li>
							
							<?php 
							
							
							$courseTools=$account->getUserCoursesTools($course_id);
							foreach($courseTools as $courseTools){
							?>
							<li class="slide">
								<a class="side-menu__item" data-bs-toggle="slide" href="javascript: void(0);">
									<?php echo $svg=$courseTools['svg']; ?>
									<span class="side-menu__label"><?php echo $toolName=$courseTools['tool_name']; ?></span><i class="angle fa fa-angle-right"></i>
								</a>
								<ul class="slide-menu">
									<li class="side-menu-label1"><a href="javascript:void(0);"><?php echo $toolName=$courseTools['tool_name']; ?></a></li>
									
									<?php 
									$preClassRoom=$account->checkPreclassNotes($courseTools['tool_id']);
									if($preClassRoom){
									?>
									<li><a href="tools.php?id=<?php echo $id=$courseTools['tool_id'];?>" class="slide-item">Pre Classroom Resources</a></li>
									<?php }
									$courseMaterial=$account->checkCourseMaterial($courseTools['tool_id']);
									if($courseMaterial){
									?>
									<li><a href="courseMaterials.php?id=<?php echo $id=$courseTools['tool_id'];?>" class="slide-item">Course Materials</a></li>
									<?php } 
									$assignment=$account->checkAssignment($courseTools['tool_id']);
									if($assignment){
									?>
									<li><a href="assignment.php?id=<?php echo $id=$courseTools['tool_id'];?>&&ui=<?php echo $username; ?>" class="slide-item">Assignment</a></li>
									<?php } 
									$quizCheck=$account->checkQuiz($courseTools['tool_id']);
									if($quizCheck){
									?>
									<li><a href="startQuiz.php?id=<?php echo $id=$courseTools['tool_id'];?>" class="slide-item">Quiz</a></li>
									<?php } ?>
								</ul>
							</li>
							
							<?php } ?>

							<li>
								<h3>Videos</h3>
						
							</li>
							<li class="slide">
								<a class="side-menu__item" data-bs-toggle="slide" href="javascript: void(0);">
									<svg xmlns="http://www.w3.org/2000/svg"  class="side-menu__icon" enable-background="new 0 0 50 50" viewBox="0 0 50 50" viewBox="0 0 50 50">
									<path d="M33.619,4H16.381C9.554,4,4,9.554,4,16.381v17.238C4,40.446,9.554,46,16.381,46h17.238C40.446,46,46,40.446,46,33.619	
									V16.381C46,9.554,40.446,4,33.619,4z M30,30.386C30,31.278,29.278,32,28.386,32H15.005C12.793,32,11,30.207,11,27.995v-9.382	
									C11,17.722,11.722,17,12.614,17h13.382C28.207,17,30,18.793,30,21.005V30.386z M39,30.196c0,0.785-0.864,1.264-1.53,0.848l-5-3.125	
									C32.178,27.736,32,27.416,32,27.071v-5.141c0-0.345,0.178-0.665,0.47-0.848l5-3.125C38.136,17.54,39,18.019,39,18.804V30.196z"/></svg><span class="side-menu__label">Videos</span><i class="angle fa fa-angle-right"></i>
								</a>
								<ul class="slide-menu">
									<li class="side-menu-label1"><a href="javascript:void(0);">Recorded Videos</a></li>
									<li><a href="recorded_videos.php?id=<?php echo $course_id;?>&&ui=<?php echo $username;?>" class="slide-item">Recorded Videos</a></li>
								</ul>
							</li>
							<li>
								<h3>Exam / Quiz</h3>
							</li>
							<li class="slide">
								<a class="side-menu__item" data-bs-toggle="slide" href="javascript: void(0);">
									<svg class="side-menu__icon" version="1.1" id="Capa_1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" 
	 								viewBox="0 0 294.023 294.023" xml:space="preserve">
									<path color-rendering="auto" image-rendering="auto" shape-rendering="auto" color-interpolation="sRGB" d="M124.916,0.002
									c-1.649,0.045-3.169,0.9-4.064,2.285l-14.49,21.736h-49.35c-2.761,0-5,2.239-5,5v50c0,2.761,2.239,5,5,5h50c2.761,0,5-2.239,5-5
									V39.574l-10,15v19.449h-40v-40h37.682L85.631,55.117l-6.146-12.293c-1.205-2.485-4.196-3.523-6.681-2.318
									c-2.485,1.205-3.523,4.196-2.318,6.681c0.018,0.036,0.035,0.072,0.054,0.108l10,20c1.235,2.47,4.238,3.472,6.709,2.237
									c0.778-0.389,1.441-0.974,1.924-1.698l40-60c1.565-2.276,0.989-5.389-1.287-6.954C127.013,0.281,125.974-0.027,124.916,0.002
									L124.916,0.002z M147.012,44.025c-2.761,0-5,2.239-5,5v10c0,2.761,2.239,5,5,5h90c2.761,0,5-2.239,5-5v-10c0-2.761-2.239-5-5-5
									H147.012z M57.012,94.06c-2.761,0-5,2.239-5,5v50c0,2.761,2.239,5,5,5h50c2.761,0,5-2.239,5-5v-50c0-2.761-2.239-5-5-5H57.012z
	 								M62.012,104.06h40v40h-40V104.06z M147.012,114.023c-2.761,0-5,2.239-5,5v10c0,2.761,2.239,5,5,5h90c2.761,0,5-2.239,5-5v-10
									c0-2.761-2.239-5-5-5H147.012z M57.012,164.023c-2.761,0-5,2.239-5,5v50c0,2.761,2.239,5,5,5h50c2.761,0,5-2.239,5-5v-50
									c0-2.761-2.239-5-5-5H57.012z M62.012,174.023h40v40h-40V174.023z M147.012,184.058c-2.761,0-5,2.239-5,5v10c0,2.761,2.239,5,5,5h90
									c2.761,0,5-2.239,5-5v-10c0-2.761-2.239-5-5-5H147.012z M57.012,234.023c-2.761,0-5,2.239-5,5v50c0,2.761,2.239,5,5,5h50
									c2.761,0,5-2.239,5-5v-50c0-2.761-2.239-5-5-5L57.012,234.023L57.012,234.023z M62.012,244.023h40v40h-40V244.023z M147.012,254.023
									c-2.761,0-5,2.239-5,5v10c0,2.761,2.239,5,5,5h90c2.761,0,5-2.239,5-5v-10c0-2.761-2.239-5-5-5H147.012z"/>
								</svg><span class="side-menu__label">Exam</span><i class="angle fa fa-angle-right"></i>
								</a>
								<ul class="slide-menu">
									<li class="side-menu-label1"><a href="javascript:void(0);">Exam</a></li>
									<li><a href="startExam.php?id=<?php echo $course_id;?>" class="slide-item">Course Certification Exam</a></li>
								</ul>
							</li>


							<li>
								<h3>Analysis</h3>
						
							</li>
							<li class="slide">
								<a class="side-menu__item" data-bs-toggle="slide" href="javascript: void(0);">
									<svg class="side-menu__icon" viewBox="0 0 24 24" id="leaderboard-podium" 
									data-name="Flat Color" xmlns="http://www.w3.org/2000/svg" class="icon flat-color">
									<path id="primary" d="M21,13H16V10a1,1,0,0,0-1-1H9a1,1,0,0,0-1,1v5H3a1,1,0,0,0-1,1v5a1,1,0,0,0,1,1H21a1,1,0,0,0,1-1V14A1,1,0,0,0,21,13Z" 
									"></path><path id="secondary" d="M12.93,6.85a1,1,0,0,1-.47-.11L12,6.5l-.46.24a1,1,0,0,1-1.45-1.06l.09-.51L9.8,4.81a1,1,0,0,1,.56-1.71L10.87,3l.23-.47a1,1,0,0,1,1.8,0l.23.47.51.07a1,1,0,0,1,.56,1.71l-.38.36.09.51a1,1,0,0,1-.39,1A1,1,0,0,1,12.93,6.85Z" style="fill: rgb(44, 169, 188);"></path></svg>									
									<span class="side-menu__label">Leader Board</span><i class="angle fa fa-angle-right"></i>
								</a>
								<ul class="slide-menu">
									<li class="side-menu-label1"><a href="javascript:void(0);">Leader Board</a></li>
									<li><a href="leader_board.php?id=<?php echo $course_id;?>&&ui=<?php echo $username;?>" class="slide-item">Leader Board</a></li>
								</ul>
							</li>
									


							<li>
								<h3>Certificates & Receipts</h3>
							</li>
							<li class="slide">
								<a class="side-menu__item" data-bs-toggle="slide" href="javascript: void(0);">
									<svg xmlns="http://www.w3.org/2000/svg" class="side-menu__icon" enable-background="new 0 0 50 50" viewBox="0 0 50 50"
 									preserveAspectRatio="xMidYMid meet">

									<g transform="translate(0.000000,50.000000) scale(0.100000,-0.100000)"
									 stroke="none">
									<path d="M65 483 c35 -93 29 -165 -15 -171 -47 -7 -50 -16 -50 -168 l0 -144
									250 0 250 0 0 143 c0 157 -3 167 -57 167 -31 0 -32 1 -23 26 16 41 7 112 -20
									154 -8 13 -11 13 -26 -2 -15 -15 -19 -15 -41 -1 -22 15 -26 15 -40 0 -14 -14
									-18 -14 -40 0 -22 15 -26 15 -40 0 -14 -14 -18 -14 -40 0 -21 14 -25 14 -41 0
									-15 -13 -20 -14 -36 -1 -27 19 -40 18 -31 -3z m152 -20 c14 14 18 14 40 0 22
									-15 26 -15 40 0 14 14 18 14 39 0 19 -12 27 -13 38 -4 11 9 16 5 25 -20 15
									-43 14 -70 -5 -115 -15 -37 -15 -94 2 -137 6 -16 -7 -17 -144 -17 l-150 0 -12
									51 c-11 45 -10 58 6 105 15 44 16 61 7 94 -9 37 -9 40 7 35 9 -3 23 1 30 9 10
									14 15 13 37 -1 22 -15 26 -15 40 0z m-157 -196 c0 -13 7 -44 16 -70 l16 -47
									169 0 c93 0 169 2 169 4 0 2 -7 23 -15 46 -8 23 -15 53 -15 67 0 22 4 24 38
									21 l37 -3 3 -132 3 -133 -231 0 -230 0 0 128 c0 71 3 132 7 135 14 15 33 6 33
									-16z"/>
									<path d="M74 126 c-3 -8 -4 -29 -2 -48 l3 -33 175 0 175 0 0 45 0 45 -173 3
									c-142 2 -173 0 -178 -12z m336 -36 l0 -30 -160 0 -160 0 0 30 0 30 160 0 160
									0 0 -30z"/>
									<path d="M140 90 c0 -13 7 -20 20 -20 13 0 20 7 20 20 0 13 -7 20 -20 20 -13
									0 -20 -7 -20 -20z"/>
									<path d="M230 90 c0 -13 7 -20 20 -20 13 0 20 7 20 20 0 13 -7 20 -20 20 -13
									0 -20 -7 -20 -20z"/>
									<path d="M320 90 c0 -13 7 -20 20 -20 13 0 20 7 20 20 0 13 -7 20 -20 20 -13
									0 -20 -7 -20 -20z"/>
									</g>
									</svg>
									<span class="side-menu__label">Receipts</span><i class="angle fa fa-angle-right"></i>
								</a>
								<ul class="slide-menu">
									<li class="side-menu-label1"><a href="javascript:void(0);">Receipts</a></li>
									<li><a href="receipt_list.php?id=<?php echo $course_id;?>&&ui=<?php echo $username;?>" class="slide-item">Receipts List</a></li>
								</ul>
							</li>
									<li class="slide">
								<a class="side-menu__item" data-bs-toggle="slide" href="javascript: void(0);">
									<svg xmlns="http://www.w3.org/2000/svg" class="side-menu__icon" enable-background="new 0 0 50 50" viewBox="0 0 50 50"
									 preserveAspectRatio="xMidYMid meet">

									<g transform="translate(0.000000,50.000000) scale(0.100000,-0.100000)"
								 stroke="none">
									<path d="M80 250 l0 -210 35 0 c19 0 35 5 35 10 0 6 -11 10 -25 10 l-25 0 0
									190 0 190 150 0 150 0 0 -190 0 -190 -65 0 c-37 0 -65 -4 -65 -10 0 -6 32 -10
									75 -10 l75 0 0 210 0 210 -170 0 -170 0 0 -210z"/>
									<path d="M150 330 c0 -6 40 -10 100 -10 60 0 100 4 100 10 0 6 -40 10 -100 10
									-60 0 -100 -4 -100 -10z"/>
									<path d="M150 270 c0 -6 40 -10 100 -10 60 0 100 4 100 10 0 6 -40 10 -100 10
									-60 0 -100 -4 -100 -10z"/>
									<path d="M190 193 c-31 -11 -37 -32 -32 -106 2 -39 6 -73 7 -75 2 -2 12 3 24
									10 17 11 25 11 42 0 12 -7 22 -12 24 -10 9 11 11 150 1 162 -13 17 -45 26 -66
									19z m44 -28 c12 -13 13 -21 4 -40 -6 -14 -19 -25 -28 -25 -9 0 -22 11 -28 25
									-9 19 -8 27 4 40 9 8 19 15 24 15 5 0 15 -7 24 -15z m6 -104 c0 -20 -2 -21
									-15 -11 -12 10 -18 10 -30 0 -13 -10 -15 -9 -15 11 0 20 5 24 30 24 25 0 30
									-4 30 -24z"/>
									</g>
								</svg>									
								<span class="side-menu__label">Certificates</span><i class="angle fa fa-angle-right"></i>
								</a>
								<ul class="slide-menu">
									<li class="side-menu-label1"><a href="javascript:void(0);">Certificates</a></li>
									<li><a href="certificate_list.php?id=<?php echo $course_id;?>&&ui=<?php echo $username;?>" class="slide-item">Certificates List</a></li>
								</ul>
							</li>

			</ul>
						<div class="slide-right" id="slide-right"><svg xmlns="http://www.w3.org/2000/svg" fill="#7b8191"
								width="24" height="24" viewBox="0 0 24 24">
								<path d="M10.707 17.707 16.414 12l-5.707-5.707-1.414 1.414L13.586 12l-4.293 4.293z" />
							</svg>
						</div>
					
					

					</div>				</div>
            </div>
			<!--/APP-SIDEBAR-->