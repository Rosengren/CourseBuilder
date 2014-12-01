var DB_CONNECTION_URL = "../resources/library/dbConnection.php";
var TIMETABLE_URL = "timetable.php";
// utilities

/**
 * toggleClass
 *
 * adds or removes specified class from a given element
 * param element   : html element to be modified
 * param className : name of the class to be toggled
**/

function toggleClass(element, className){
  if (!element || !className) { return; }

  var classString = element.className, 
  	nameIndex   = classString.indexOf(className);

  if (nameIndex == -1) {
    classString += ' ' + className;
  } else {
    classString = classString.substr(0, nameIndex) + classString.substr(nameIndex + className.length);
  }
  element.className = classString;
}


/**
 * selectProgram
 *
 * sets the given program object to active
 * param obj : object to be set active
**/

function selectProgram(obj) {
	toggleClass(obj, 'active');
}


/**
 * updateProgramtitle
 *
 * set the program header of the given title
 * param title : new header name
**/

function updateProgramTitle(title) {
  var titles = document.getElementsByClassName('selected-program');
  for (var i = 0; i < titles.length; i++)
    titles[i].innerHTML = title;
}


/**
 * toggleVisibility
 *
 * hide or show the element specified
 * param obj : element to be toggled
 */
function toggleVisibility(obj) {
  var e = document.getElementById(obj);
  if (e.style.display == 'none')
    e.style.display = 'block';
  else
    e.style.display = 'none';
}

/**
 * AJAXRequest
 *
 * method for making AJAX requrests
 * param callback : function that will be called when the request is completed
 * param page     : url to requested php webpage
 * param params   : object containing parameters to be passed to the page
**/

function AJAXRequest(callback, page, params) {
  var httpRequest = new XMLHttpRequest();
  var postRequest = objectToParameters(params);

  httpRequest.onreadystatechange = function() {
    if (httpRequest.readyState === 4 && httpRequest.status === 200)
      callback(httpRequest.responseText);
  }

  httpRequest.open("post", page, true);
  httpRequest.setRequestHeader("Content-type","application/x-www-form-urlencoded");
  httpRequest.send(postRequest);

}


/**
 * objectToParameters
 *
 * converts a given object into PHP parameters
 * This method is used to help make an AJAXRequest
 * param obj : object to be converted
 * returns string parameters of object
**/

function objectToParameters(obj) {
  var retval = "";
  for (var key in obj)
    retval += key + "=" + obj[key] + "&";
  return retval.slice(0, -1);
}


/**
 * SetCookie
 *
 * Set the value to cookie
 * param obj : cookieName, cookieValue, minutes
**/

function SetCookie(cookieName, cookieValue, minutes) {
  var currentTime = new Date();
  var expiresTime = new Date();
  if (minutes==null || minutes==0) minutes=1000;
  expiresTime.setTime(currentTime.getTime() + 60000*minutes);
  var cookie = cookieName+"="+JSON.stringify(cookieValue);
  document.cookie = cookie + ";expires="+expiresTime.toGMTString();
}


/**
 * ReadCookie
 *
 * read the value stored as a cookie
 * param cookieName : name of the cookie
**/

function ReadCookie(cookieName) {
  var result = document.cookie.match(new RegExp(cookieName + '=([^;]+)'));
  result && (result = JSON.parse(result[1]));
  return result;
}


/**
 * storeTables
 *
 * stores all tables in tableList object
 * param courseNames: list of course names
 * param tables: array of tables to add to tableList
 * param tableListObj: tableList to append tables to
 */

function storeTables(courseNames, tables, tableListObj) {

  tableListObj.clearList();

  for (var i = 0; i < tables.length; i++) {
    var table = tables[i];
    tableListObj.appendTable(tables[i], courseNames);
  }
}


/**
 * setTable
 *
 * populate the timetable with courses
 * param timeTable: timetable object to add courses to
 * param courses: list of courses to append
 */

function setTable(timeTable, courses) {
  // remove all items from the table
  timeTable.clearTable();

  // add new elements to the timetable
  for (var i = courses.length - 1; i >= 0; i--) {
    var info = courses[i][0] + " " + courses[i][1];
    var days = courses[i][2];
    var startTime = courses[i][3];
    var endTime = courses[i][4];
    var courseTitle = courses[i][5];

  timeTable.appendCourse(days, startTime, endTime, info, courseTitle);

  };
}


/**
 * listSelectedCourses
 *
 * list all courses selected in the
 * given DOM element
 *
 * param id: element ID
 * param childClass: className of child element to be created
 * param courses: array of courses to append to id
 */

function listSelectedCourses(id, childClass, courses, params, page) {

  var elem = document.getElementById(id);
  elem.innerHTML = ""; // clear list

  for (var i = 0; i < courses.length; i++) {
    if (courses[i][0].length > 2) { // skip empty strings
      var course = document.createElement('div');
      course.innerHTML = courses[i][0];
      course.className = childClass;
      course.allCourses = courses;

      course.onmouseover = function() {
        this.innerHTML += " (remove)";
      }

      course.onmouseout = function() {
        this.innerHTML = this.innerHTML.slice(0, -9);
      }

      course.onclick = function() {

        var tableCourses = "";
        var thisCourse = this.innerHTML.slice(0, -9);

        if (this.allCourses.length > 1) {
          for (var i = 0; i < this.allCourses.length; i++) {
            if (this.allCourses[i][0] !== thisCourse) {
              tableCourses += this.allCourses[i][0] + ",";
            }
          };

          tableCourses = tableCourses.replace(/,\s*$/, "")
          
          var customParams = {
              action: 'timeTable',
              TimeTableCourse: tableCourses,
              courseCompleted: params['courseCompleted'],
              program: params['program']
            }

          loadTimetableContent(customParams);
        } else {
          displayBannerMessage('messageBanner', 'you must have at least one course in the table');
        }
      }

      elem.appendChild(course);
    }
  }
}


/**
 * loadTimetableContent
 *
 * param customParams: custom parameters for AJAX call
 */

function loadTimetableContent(customParams) {
  var timetable;
  var tableList;
  
  timetable = new Timetable('timetable');
  tableList = new TableList('alt-table', timetable);

  var timeTableInfo = ReadCookie("TimeTableInfo");


  if (!(typeof timeTableInfo === 'undefined' || timeTableInfo === null)) {
    var page = DB_CONNECTION_URL
    
    if (typeof customParams === 'undefined' || customParams === null) {
      params = timeTableInfo
    } else {
      params = customParams;
    }

    console.log(params);
    
    // request 
    AJAXRequest( function(response) {
        // console.log(response);
        var json = JSON.parse(response);

        // add courses to sidebar
        listSelectedCourses('selected-courses', 'selected-course', json[0][0], 
                           params, page);

        // add timetables to sidebar
        storeTables(json[0][0], json[1], tableList);

        // display message banner
        displayBannerMessage('messageBanner', json[2]);

        // add courses to sidebar
        addCourses('add-course', json[3], json[0][0]);

        // add electives to sidebar
        addElectives('add-elective', json[4], json[0][0]);

        // add registration button
        addRegistrationSubmission('registration', tableList, params);

        // setting the first table
        var firstTable = json[1][0];
        setTable(timetable, firstTable);

        // Check course availability in real-time (every 5 seconds)
        checkAvailabilityInRealTime(tableList, params, 5000);

    }, page, params);
  }
}


/**
 * displayBannerMessage
 *
 * param id: banner id
 * param message: message to display in banner
 */
function displayBannerMessage(id, message) {

  if (message != '') {
    var banner = document.getElementById(id);
    banner.className = "displayBanner";
    banner.innerHTML = "Notice: " + message;
  }
}


/**
 * addElectives
 *
 * add list of electives to id and
 * display pop-up when user clicks
 * on the id.
 *
 * param id: object to add electives to
 * param electives: list of electives
 * param callback: function to call upon selecting an elective
 */

function addElectives(id, electives, selectedCourses) {

  var elem = document.getElementById(id);
  var header = "Select Elective";
  var subheader = "Please select the elective that you wish to add to your timetable";

  addPopupToElement(elem, header, subheader, electives, selectedCourses);
}


/**
 * addCourses
 *
 * add list of courses to id and
 * display pop-up when user clicks
 * on the id.
 *
 * param id: object to add courses to
 * param courses: list of courses
 * param callback: function to call upon selecting a course
 */
function addCourses(id, courses, selectedCourses) {

  var elem = document.getElementById(id);
  var header = "Select Course";
  var subheader = "Please select the course that you wish to add to your timetable";

  addPopupToElement(elem, header, subheader, courses, selectedCourses);
}


function addCourseToTable(addedCourse, selectedCourses) {

  var tableCourses = "";

  for (var i = 0; i < selectedCourses.length; i++) {

    if (selectedCourses[i][0] !== course.innerHTML) {
      tableCourses += selectedCourses[i][0] + ",";
    }
  };

  tableCourses += addedCourse;
  // tableCourses = tableCourses.replace(/,\s*$/, "")
  
  var customParams = {
      action: 'timeTable',
      TimeTableCourse: tableCourses,
      courseCompleted: params['courseCompleted'],
      program: params['program']
    }

  loadTimetableContent(customParams);

}


/**
 * addRegistrationSubmission
 *
 * adds an AJAX call to the registration button and
 * calls the server to register for courses
 * param id: id of registration button
 * param tableList: table containing all of the timetables
 * param params: AJAX parameters required to call the server
 * param page: server page to call
 */

function addRegistrationSubmission(id, tableList, params) {

  var registrButton = document.getElementById(id);

  registrButton.onclick = function() {

    var customParams = {
        action: 'registration',
        program: params['program'],
        selectedCourses: tableList.getSelectedList()
      }

    AJAXRequest( function(response) {
      console.log(response);
      var json = JSON.parse(response);

      displayBannerMessage('messageBanner', json[0]);

    }, DB_CONNECTION_URL, customParams);

  }
}


/**
 * addPopupToElement
 *
 * add popup with callback to a given element
 *
 */

function addPopupToElement(elem, headerText, subheaderText, listOfItems, selectedCourses) {

  elem.onclick = function() {

    var content = document.getElementById('light');
    content.innerHTML = ""; // reset content
    content.style.display='block';
    document.getElementById('fade').style.display='block';

    var header = document.createElement("div");
    header.className = "popupHeader";
    header.innerHTML = headerText;

    var subheader = document.createElement("div");
    subheader.className = "popupSubHeader";
    subheader.innerHTML = subheaderText;

    var electives = document.createElement("div");
    electives.className = "electivesList";

    for (var i = listOfItems.length - 1; i >= 0; i--) {

      if (listOfItems[i].length < 2) continue; // ignore empty

      var item = document.createElement("div");
      item.className = "elective";
      var course = listOfItems[i];
      item.name = course[0];
      item.innerHTML = item.name + " (" + course[1] + ")";

      item.onclick = function() {

        addCourseToTable(this.name, selectedCourses);

        // close pop-up
        document.getElementById('light').style.display = 'none';
        document.getElementById('fade').style.display = 'none';
      }

      electives.appendChild(item);
    }

    var closeButton = document.createElement("div");
    closeButton.className = "closeButton";
    closeButton.innerHTML = "Close";
    closeButton.onclick = function() {
      document.getElementById('light').style.display = 'none';
      document.getElementById('fade').style.display = 'none';
    }

    // append elements
    content.appendChild(header);
    content.appendChild(subheader);
    content.appendChild(electives);
    content.appendChild(closeButton);
  }
}


/**
 * checkAvailabilityInRealTime
 *
 * check if any of the courses have filled up
 * during user session
 *
 * param tableList: tableList object
 * param params: list of parameters for AJAX call
 * param waitTime: time interval betwen calls
 */
function checkAvailabilityInRealTime(tableList, params, waitTime) {

  var customParams = {
      action: 'checkAvailability',
      program: params['program'],
      selectedCourses: tableList.getSelectedList()
    }

  // TODO: POSSIBLY REFRESH WEB PAGE AFTER A FEW SECONDS?
  setInterval(function() {
    AJAXRequest( function(response) {

      var json = JSON.parse(response);

      if (json[0] !== undefined) {

        var message = "The following courses are no longer available: ";
        for (var i = 0; i < json.length; i++) {
          message += json[i] + ", ";
        };

        displayBannerMessage('messageBanner', message);
      }

    }, DB_CONNECTION_URL, customParams);
    
  }, waitTime);

}

