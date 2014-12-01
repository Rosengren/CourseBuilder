<?php
// <script>document.write('<script src="http://' + (location.host || 'localhost').split(':')[0] + ':35729/livereload.js?snipver=1"></' + 'script>')</script>
	require "../config.php";
	require "timeTable.class.php";
	require("../library/PreRequisite.class.php");

	// require '/Users/SuperiMan/repo/kint/Kint.class.php';
	// Kint::enabled(false);
	
	session_start();


	$lock = false;

	$variable = (isset($_POST['action']) ? $_POST: $_GET);

	$program = $variable['program'];
	$prerequisiteTree = $db->getPrerequisiteTree($program);
	if ($prerequisiteTree == []) {
		echo "Can't find info about this program";
		exit();
	}

	// Need another Ajax for Elective when select form prerequisite table
	$elective = $db->getElectivesByProgram($program);

	$action = $variable['action'];
	switch ($action) {
		case 'prereqTree':
			// pprint([$prerequisiteTree, $elective]);
			echo json_encode([$prerequisiteTree, $elective]);
			break;

		case 'timeTable':
			$courseCompleted = (isset($variable['courseCompleted']) && $variable['courseCompleted'] != '' ? explode(",", $variable['courseCompleted']) : []);

			$courseObjectArray = $db-> getCourseArray($courseCompleted, $prerequisiteTree, $program);

			if (isset($variable['TimeTableCourse'])) {
				// Generate table from selected course.
				$courseForTable = ($variable['TimeTableCourse'] != '' ? explode(",", $variable['TimeTableCourse']) : []);

				$maxNumOfCourse = sizeof($courseForTable);
				$courseForTable = $db->createCourseArrayBySelectCourse($courseForTable);
				$singleTimeTable = getATimeTable($courseForTable, $maxNumOfCourse);
			} else {
				// Generate table from completed courses
				$maxNumOfCourse = (isset($variable['max']) ? $variable['max'] : 5);
				$singleTimeTable = getATimeTable($courseObjectArray, $maxNumOfCourse);
			}

			

			// Add a lock ....
			// Need run getTablesInArray to update variable
			$tablesInArray = $singleTimeTable->getTablesInArray();
			$avaiableCourses = [];
			$courArr = $singleTimeTable->toArray();

			foreach ($courseObjectArray as $key => $value) {
				if (!in_array($value->name, $courArr)) {
					$avaiableCourses[] = [$value->name, $value->courseTitle];
				}
			}

			
			$alterElective = [];
			foreach ($elective as $key => $elec) {
				if (!in_array($elec[0], $courArr)) {
					$alterElective[] = $elec;
				}
			}

			$result = [];
			$result[] = [$singleTimeTable->toArrayWithTitle()]; // 1-6 Courses that we scheduled
			$result[] = $tablesInArray;                         // Combination of time table that are available
			$result[] = [$singleTimeTable->message]; 			// Message from Backend
			$result[] = $avaiableCourses; 						// A list of course that are available and unCompleted
			$result[] = $alterElective;  
			
			echo json_encode($result);

			break;
		case 'registration':

			$result = [];
			if($lock == false){
				$lock = true;
				if (isset($variable['selectedCourses'])) {
					$registrationMsg = $db->registerForClasses($variable['selectedCourses']);				
				}
				
				if($registrationMsg == []){
					$result[] = "All Classes Registered :)";
				}else{
					foreach ($registrationMsg as $message) {
						array_push($result, "Could not register for the following courses: ".$message);
					}
					array_push($result, ". Please remove course(s) and try again");
					$lock = false;
				}
			}else{
				json_encode("please try again");
			}	

			echo json_encode($result);

			break;
		case 'checkAvailability':

			$courses = [];
			if (isset($variable['selectedCourses'])) {
				$courses = $db->checkCourseAvailability($variable['selectedCourses']);
			}

			echo json_encode($courses);

			break;
		default:
			break;
	}

	/*
		get all the combination of available timeTables
		$timeTables : the final result
		$courseArray : Array of courses Object that we can choice
	 */
	function getTimeTables(&$timeTables, $courseArray, $startPoint, $timeTable) {
		if (sizeof($timeTables) > 2) {
			return true;
		}
		if ($timeTable->isFull()) {
			array_push($timeTables, $timeTable);
			// pprint("find solution");
			// d($timeTable);
			return true;
		}
		for ($i=$startPoint; $i < sizeof($courseArray); $i++) { 
			// $temp = $timeTable->duplicate();
			if ($timeTable->addCourse($courseArray[$i])) {
				getTimeTables($timeTables, $courseArray, $i + 1, $timeTable);
			}
		}
	}

	// get all the single of timeTable
	function getATimeTable($courseArray, $maxNumOfCourse='') {
		if ($maxNumOfCourse == '' && sizeof($courseArray) <=5) {
			$maxNumOfCourse = 5;
		}

		$timeTable = new TimeTable($maxNumOfCourse);
		for ($i=0; $i < sizeof($courseArray); $i++) { 
			$timeTable->addCourse($courseArray[$i]);
			if ($timeTable->isFull()) {
				break;
			}
		}
		return $timeTable;
	}

?>
