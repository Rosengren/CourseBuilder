<?php 
	/**
	* TimeTable
	*/
	class TimeTable
	{
		public $courses;
		public $maxmiumCourseTaking;
		public $message;

		private $numOfSulotion;
		private $isChange;
		private $tables;
		function __construct($max="5")
		{
			$this->courses = [];
			$this->maxmiumCourseTaking = $max;
			$this->numOfSulotion = 10; // 
			$this->message = "";
			$this->isChange = True;
			$this->tables = [];

		}

		public function duplicate(){
			$c = new TimeTable();
			$c->courses = $this->courses;
			return $c;
		}

		public function toString(){
			if ($this->isChange) 
				getTablesInArray();
						
			$r = "";
			foreach ($this->courses as $var) {
				$r .= $var->name.",";
			}
			return $r;
		}		

		public function toArrayWithTitle(){
			if ($this->isChange) 
				getTablesInArray();
						
			$r = [];
			foreach ($this->courses as $var) {
				$r[] = [$var->name, $var->courseTitle];
			}
			return $r;
		}

		public function toArray(){
			if ($this->isChange) 
				getTablesInArray();
			
			$r = [];
			foreach ($this->courses as $var) {
				$r[] = $var->name;
			}
			return $r;
		}

		public function addCourse($c)
		{
			if ($this->isFull()) {
				return false;
			}
			
			if ($this->checkTimeConflict($c)) {
				// TODO:add..........
				array_push($this->courses, $c);
				return true;
			} else {
				return false;
			}
			$this->isChange = True;
		}

		public function isFull()
		{
			return sizeof($this->courses) >= $this->maxmiumCourseTaking;
		}

		public function checkTimeConflict($value='')
		{
			// TODO
			if (sizeof($this->courses) == 0){
				return true;
			}
			// ....
			return true;
		}

		/**
		 * [recursiveGetTimeTables description]
		 * @param  [type] $combination Courses combination to pick
		 * @param  [type] &$result     result
		 * @param  [type] $path        path to store solution path
		 * @param  [type] $x           start point in array
		 * @param  [type] $y           start point in sub-array
		 * @return [type]              [description]
		 */
		private function recursiveGetTimeTables(&$result, $combination, $path, $x, $y){
			if (isset($combination[$x]) and isset($combination[$x][$y])) {

				if(!hasTimeConflictArrayToClass($this->getArrayByPath($path, $combination), $combination[$x][$y])) {
					$path[] = [$x, $y];
					if ($x == sizeof($combination)-1) {
						// Found one solution
						$this->numOfSulotion--;

						$oneSolution = flatArray($this->getArrayByPath($path, $combination));
						$resultInArray = [];
						foreach ($oneSolution as $class) {
							$resultInArray[] = ($class->toArray());
						}
						$result[] = $resultInArray;

						if ($this->numOfSulotion!= 0) {
							// next solution
							array_pop($path);
							$this->recursiveGetTimeTables($result, $combination, $path, $x, $y+1);
						}
					} else {
						// Keep working
						$this->recursiveGetTimeTables($result, $combination, $path, $x+1, 0);
					}
					return;
				}

				$this->recursiveGetTimeTables($result, $combination, $path, $x, $y+1);
			} else {
				if ($x == 0  and $y > sizeof($combination[$x])-1) {
					// Seach End
					return;
				} 

				if ($y > sizeof($combination[$x])-1) {
					// back Search
					$postion = array_pop($path);
					$this->recursiveGetTimeTables($result, $combination, $path, $postion[0], $postion[1]+1);
				}
			}
		}

		private function getArrayByPath($path, $array) {
			$result = [];
			foreach ($path as $p) {
				$result[] = $array[$p[0]][$p[1]];
			}
			return $result;
		}

		public function getATableInArray()
		{
			return getTablesInArray()[0];
		}

		public function getTablesInArray()
		{
			if ($this->isChange) {
				$this->isChange = False;
				$combination = [];
				foreach ($this->courses as $couse) {
					$combination[] = $couse->getCourseCombination();
				}
				$result = [];
				if (sizeof($combination) == 0) {
					return [];
				}

				while ($result == []) {
					// Take out one courses
					$this->recursiveGetTimeTables($result, $combination, [], 0, 0);
					if ($result != []) 
						break;
					array_pop($combination);
					$course = array_pop($this->courses);
					$this->message .= $course->name." has non-fixable time conflict\n";
				}
				$this->tables = $result;

			}
			return $this->tables;
		}
	}


	/**
	 * check if there is a conflict between a  list of class and a class
	 * @param  list  		$classArray list of CourseClass Object
	 * @param  list  		$class      list of CourseClass Object
	 * @return true : there is conflict
	 *         false: there is no conflict	
	 */
	function hasTimeConflictArrayToClass($classArrayA, $classArrayB)
	{
		$flat_classArrayA = flatArray($classArrayA);
		$flat_classArrayB = flatArray($classArrayB);

		foreach ($flat_classArrayA as $keyA => $classA) {
			foreach ($flat_classArrayB as $keyB => $classB) {
				if ($classA->hasConflictWith($classB)) {
					return true;
				}
			}
		}
		return false;
	}


	/*
	 * @return true : there is conflict
	 *         false: there is no conflict		
	 */
	function hasTimeConflictInArray($classArray)
	{
		foreach ($classArray as $keyA => $classA) {
			foreach ($classArray as $keyB => $classB) {
				if ($keyA != $keyB and $classA->hasConflictWith($classB)) {
					return true;
				}
			}
		}
		return false;
	}



	/**
	* 
	*/
	class Course
	{
		function __construct($name = "", $title ="")
		{
			$this->courseClasses = [];
			$this->name = $name;
			$this->courseTitle = $title;
			$this->courseCombination = 0;
		}

		public function addClass($course)
		{
			array_push($this->courseClasses, new CourseClass($course));
			if ($this->courseTitle == "") {
				$this->courseTitle = $course['CourseTitle'];
			}
		}

		public function checkConflict($compareCourse)
		{
			// TODO:
			return true;
		}

		// get the combination of labs and lectures
		public function getCourseCombination(){
			$result = [];

			$lectures = [];
			$sectionCode = [];
			$labs = [];

			// get Lecture
			foreach ($this->courseClasses as $class) {
				if ($class->isLecture()) {
					array_push($lectures, $class);
					$sectionCode[] = $class->section;
				}
			}

			// get Lab or classes
			foreach ($this->courseClasses as $key =>$class) {
				if (!$class->isLecture()) {
					// $sec = preg_replace('/[0-9]+/', '', $class->section);
					$labSection = $class->section[0];
					
					if (in_array($labSection, $sectionCode) or $labSection == 'L') {
						array_push($labs, $class);
					} else {
						unset($this->courseClasses[$key]);
					}
				} 
			}

			foreach ($lectures as $lecture) {
				foreach ($labs as $lab) {
					// $labSection = preg_replace('/[0-9]+/', '', $lab->section);
					$labSection = $lab->section[0];
					if ($labSection == 'L' or ($lecture->section) == $labSection) {
						array_push($result, [$lecture, $lab]);
						$this->courseCombination++;
					}
				}
			}

			if (sizeof($labs) == 0) {
				$result = [];
				foreach ($lectures as $l) {
					$result[] = [$l];
				}
				$this->courseCombination = sizeof($lectures);
			}

			return $result;
		}
	}
	
	/**
	* 
	*/
	class CourseClass
	{
		function __construct($course)
		{
			// $this->c = $course;
			$this->courseName = $course['Subject']." ".$course['CourseNumber'];
			$this->title = $course['CourseTitle'];
			$this->section = $course['Section'];
			$this->days = $course['Days'];
			$this->start_Time = intval($course['Start_Time']);
			$this->end_Time = intval($course['End_Time']);
		}

		public function toArray()
		{
			return [$this->courseName, $this->section, $this->days, $this->start_Time, $this->end_Time];
		}

		public function isLecture()
		{
			if (preg_match('([0-9])', $this->section)) {
				// pprint("Lab");
				return false;
			}
			return true;
		}

		/**
		 * Check the time conflict between two classes
		 * @param  CourseClass  $classB CourseClass Object
		 * @return true : there is conflict
		 *         false: there is no conflict
		 */
		function hasConflictWith($classB) {
			$daysA = str_split($this->days);
			$daysB = str_split($classB->days);

			if (array_intersect($daysA, $daysB) == []) {
				return false;
			} else {
				$A = $this->start_Time;
				$B = $this->end_Time;
				$C = $classB->start_Time;
				$D = $classB->end_Time;

				if (($A < $D and $D <= $B) or ($A <= $C and $C < $B)) {
					return true;
				}
				return false;
			}
		}
	}

?>
