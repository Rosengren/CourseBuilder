<?php 
	$pageTitle = "Home";
	require("../resources/config.php");
	include(TEMPLATES_PATH . "/header.php");
	$programList = $db->getListOfPrograms();
?>
	
	<section class="program-select">
		<div id="program-select-title">
			Select a program and the number of courses you want to take
		</div>
		<div id="program-select-dd" class="wrapper-dropdown" tabindex="1" onclick="selectProgram(this);">
			<div id="program-select-subtitle" class="selected-program">
				Select a program
			</div>
			<ul class="dropdown">
			<?php foreach ($programList as $program): ?>

				<li>
					<div class="gen-tree"><?= $program; ?></div>
				</li>

			<?php endforeach; ?>
			</ul>
		</div>

		<input type="number" max='6' min='1' maxlength="1" size="10" value="5" class='course-number' id='max'/>

		<div class='submit' id='submit'>
			<input type="submit" value="Submit" id="submitButton"/>
			<label for="submitButton">Submit</label>
		</div>
		
		<div id="year-select-dd" class="wrapper-dropdown" tabindex="1" onclick="selectProgram(this);" style="display:none">
			<div id="year-select-subtitle">Select a year</div>
			<ul class="dropdown">
			<?php foreach ($UNIVERSITY_YEARS as $year): ?>

				<li>
					<div class='select-year' ><?= $year; ?> Year Status</div>
				</li>

			<?php endforeach ?>
			</ul>
		</div>
	  	<div id="program-select-onpattern">
			<div class="checkbox">
		 		<input type="checkbox" value="1" id="checkboxInput" name="" />
				<label for="checkboxInput"></label>
		  </div>
	  	On Pattern		
		</div>
		</section>

		<h1 id="program-name" class="selected-program">
			<!-- generated by JavaScript -->
		</h1>

		<div id="course-table">
			<!-- generated by JavaScript -->
		</div>

<?php include(TEMPLATES_PATH . "/footer.php"); ?>
