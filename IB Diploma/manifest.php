<?php
/*
Gibbon, Flexible & Open School System
Copyright (C) 2010, Ross Parker

This program is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 3 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program.  If not, see <http://www.gnu.org/licenses/>.
*/

//This file describes the module, including database tables

//Basica variables
$name = 'IB Diploma';
$description = 'A module to facilitate schools to run the IB Diploma programme.';
$entryURL = 'index.php';
$type = 'Additional';
$category = 'IB';
$version = '1.1.01';
$author = 'Ross Parker, Ashton Power';
$url = 'http://rossparker.org';

//Module tables
$moduleTables[0] = "CREATE TABLE `ibDiplomaCASInterview` (
  `ibDiplomaCASInterviewID` INT(12) UNSIGNED ZEROFILL NOT NULL AUTO_INCREMENT,
  `1_gibbonPersonIDInterviewer` INT(10) UNSIGNED ZEROFILL NOT NULL,
  `gibbonPersonIDInterviewee` INT(10) UNSIGNED ZEROFILL NOT NULL,
  `1_date` DATE NOT NULL,
  `1_notes` TEXT NULL,
  `2_gibbonPersonIDInterviewer` INT(10) UNSIGNED ZEROFILL DEFAULT NULL,
  `2_date` DATE DEFAULT NULL,
  `2_notes` TEXT NULL,
  `2_outcome1` TEXT NULL,
  `2_outcome2` TEXT NULL,
  `2_outcome3` TEXT NULL,
  `2_outcome4` TEXT NULL,
  `2_outcome5` TEXT NULL,
  `2_outcome6` TEXT NULL,
  `2_outcome7` TEXT NULL,
  `2_outcome8` TEXT NULL,
  `3_gibbonPersonIDInterviewer` INT(10) UNSIGNED ZEROFILL DEFAULT NULL,
  `3_date` DATE DEFAULT NULL,
  `3_notes` TEXT NULL,
  `3_outcome1` TEXT NULL,
  `3_outcome1Notes` TEXT NULL,
  `3_outcome2` TEXT NULL,
  `3_outcome2Notes` TEXT NULL,
  `3_outcome3` TEXT NULL,
  `3_outcome3Notes` TEXT NULL,
  `3_outcome4` TEXT NULL,
  `3_outcome4Notes` TEXT NULL,
  `3_outcome5` TEXT NULL,
  `3_outcome5Notes` TEXT NULL,
  `3_outcome6` TEXT NULL,
  `3_outcome6Notes` TEXT NULL,
  `3_outcome7` TEXT NULL,
  `3_outcome7Notes` TEXT NULL,
  `3_outcome8` TEXT NULL,
  `3_outcome8Notes` TEXT NULL,
  PRIMARY KEY (`ibDiplomaCASInterviewID`),
  UNIQUE KEY `gibbonPersonIDInterviewee` (`gibbonPersonIDInterviewee`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 ;";

$moduleTables[1] = "CREATE TABLE `ibDiplomaCASCommitment` (
  `ibDiplomaCASCommitmentID` INT(12) UNSIGNED ZEROFILL NOT NULL AUTO_INCREMENT,
  `gibbonPersonID` INT(10) UNSIGNED ZEROFILL NOT NULL,
  `name` VARCHAR(50) NOT NULL,
  `approval` ENUM('Pending','Not Approved','Approved') NOT NULL DEFAULT 'Pending',
  `status` ENUM('Planning','In Progress','Complete','Incomplete') NOT NULL,
  `dateStart` DATE NOT NULL,
  `dateEnd` DATE DEFAULT NULL,
  `supervisorName` VARCHAR(100) NOT NULL,
  `supervisorEmail` VARCHAR(255) NOT NULL,
  `supervisorPhone` VARCHAR(20) NOT NULL,
  `description` TEXT NULL,
  `goals` VARCHAR(255) NULL,
  PRIMARY KEY (`ibDiplomaCASCommitmentID`),
  UNIQUE KEY `gibbonPersonID` (`gibbonPersonID`,`name`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 ;";

$moduleTables[2] = "CREATE TABLE `ibDiplomaCASStaff` (
  `ibDiplomaCASStaffID` INT(4) UNSIGNED ZEROFILL NOT NULL AUTO_INCREMENT,
  `gibbonPersonID` INT(10) UNSIGNED ZEROFILL NOT NULL,
  `role` ENUM('Coordinator','Advisor') NOT NULL,
  PRIMARY KEY (`ibDiplomaCASStaffID`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 ;";

$moduleTables[3] = "CREATE TABLE `ibDiplomaStudent` (
  `ibDiplomaStudentID` INT(10) UNSIGNED ZEROFILL NOT NULL AUTO_INCREMENT,
  `gibbonPersonID` INT(10) UNSIGNED ZEROFILL NOT NULL,
  `gibbonSchoolYearIDStart` INT(3) UNSIGNED ZEROFILL NOT NULL,
  `gibbonSchoolYearIDEnd` INT(3) UNSIGNED ZEROFILL NOT NULL,
  `gibbonPersonIDCASAdvisor` INT(10) UNSIGNED ZEROFILL DEFAULT NULL,
  `casStatusSchool` ENUM('','At Risk','On Task','Excellence','Complete','Incomplete') NOT NULL,
  PRIMARY KEY (`ibDiplomaStudentID`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 ;";

$moduleTables[4] = "CREATE TABLE `ibDiplomaCASReflection` (
  `ibDiplomaCASReflectionID` INT(12) UNSIGNED ZEROFILL NOT NULL AUTO_INCREMENT,
  `ibDiplomaCASCommitmentID` INT(12) UNSIGNED ZEROFILL DEFAULT NULL,
  `gibbonPersonID` INT(10) UNSIGNED ZEROFILL NOT NULL,
  `timestamp` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `title` VARCHAR( 100 ) NOT NULL,
  `reflection` TEXT NOT NULL,
  PRIMARY KEY (`ibDiplomaCASReflectionID`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 ;";

$moduleTables[5] = "CREATE TABLE `ibDiplomaCASSupervisorFeedback` (
  `ibDiplomaCASSupervisorFeedbackID` INT(14) UNSIGNED ZEROFILL NOT NULL AUTO_INCREMENT,
  `ibDiplomaCASCommitmentID` INT(12) UNSIGNED ZEROFILL NOT NULL,
  `key` VARCHAR(40) NOT NULL,
  `complete` ENUM('N','Y') NOT NULL DEFAULT 'N',
  `attendance` ENUM('','<60%','60-84%','85-99%','100%') NOT NULL,
  `evaluation` TEXT NOT NULL,
  PRIMARY KEY (`ibDiplomaCASSupervisorFeedbackID`),
  UNIQUE KEY `key` (`key`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 ;";

//Action rows
$actionRows[0]['name'] = 'IB Diploma Structure';
$actionRows[0]['precedence'] = '0';
$actionRows[0]['category'] = 'Home';
$actionRows[0]['description'] = 'Go home';
$actionRows[0]['URLList'] = 'index.php';
$actionRows[0]['entryURL'] = 'index.php';
$actionRows[0]['defaultPermissionAdmin'] = 'Y';
$actionRows[0]['defaultPermissionTeacher'] = 'Y';
$actionRows[0]['defaultPermissionStudent'] = 'Y';
$actionRows[0]['defaultPermissionParent'] = 'N';
$actionRows[0]['defaultPermissionSupport'] = 'N';
$actionRows[0]['categoryPermissionStaff'] = 'Y';
$actionRows[0]['categoryPermissionStudent'] = 'Y';
$actionRows[0]['categoryPermissionParent'] = 'N';
$actionRows[0]['categoryPermissionOther'] = 'N';

$actionRows[1]['name'] = 'Manage Staff - CAS';
$actionRows[1]['precedence'] = '1';
$actionRows[1]['category'] = 'Admin';
$actionRows[1]['description'] = 'Allows admins to manage staff roles.';
$actionRows[1]['URLList'] = 'staff_manage.php, staff_manage_add.php, staff_manage_edit.php, staff_manage_delete.php';
$actionRows[1]['entryURL'] = 'staff_manage.php';
$actionRows[1]['defaultPermissionAdmin'] = 'Y';
$actionRows[1]['defaultPermissionTeacher'] = 'Y';
$actionRows[1]['defaultPermissionStudent'] = 'N';
$actionRows[1]['defaultPermissionParent'] = 'N';
$actionRows[1]['defaultPermissionSupport'] = 'N';
$actionRows[1]['categoryPermissionStaff'] = 'Y';
$actionRows[1]['categoryPermissionStudent'] = 'N';
$actionRows[1]['categoryPermissionParent'] = 'N';
$actionRows[1]['categoryPermissionOther'] = 'N';

$actionRows[2]['name'] = 'Manage Student Enrolment';
$actionRows[2]['precedence'] = '0';
$actionRows[2]['category'] = 'Admin';
$actionRows[2]['description'] = 'Allows admins to manage students.';
$actionRows[2]['URLList'] = 'student_manage.php, student_manage_add.php, student_manage_edit.php, student_manage_delete.php';
$actionRows[2]['entryURL'] = 'student_manage.php';
$actionRows[2]['defaultPermissionAdmin'] = 'Y';
$actionRows[2]['defaultPermissionTeacher'] = 'N';
$actionRows[2]['defaultPermissionStudent'] = 'N';
$actionRows[2]['defaultPermissionParent'] = 'N';
$actionRows[2]['defaultPermissionSupport'] = 'N';
$actionRows[2]['categoryPermissionStaff'] = 'Y';
$actionRows[2]['categoryPermissionStudent'] = 'N';
$actionRows[2]['categoryPermissionParent'] = 'N';
$actionRows[2]['categoryPermissionOther'] = 'N';

$actionRows[3]['name'] = 'My Commitments';
$actionRows[3]['precedence'] = '0';
$actionRows[3]['category'] = 'CAS';
$actionRows[3]['description'] = 'Allows students to manage their commitments';
$actionRows[3]['URLList'] = 'cas_student_myCommitments.php, cas_student_myCommitments_add.php, cas_student_myCommitments_edit.php, cas_student_myCommitments_delete.php, cas_student_myCommitments_view.php';
$actionRows[3]['entryURL'] = 'cas_student_myCommitments.php';
$actionRows[3]['defaultPermissionAdmin'] = 'N';
$actionRows[3]['defaultPermissionTeacher'] = 'N';
$actionRows[3]['defaultPermissionStudent'] = 'Y';
$actionRows[3]['defaultPermissionParent'] = 'N';
$actionRows[3]['defaultPermissionSupport'] = 'N';
$actionRows[3]['categoryPermissionStaff'] = 'N';
$actionRows[3]['categoryPermissionStudent'] = 'Y';
$actionRows[3]['categoryPermissionParent'] = 'N';
$actionRows[3]['categoryPermissionOther'] = 'N';

$actionRows[4]['name'] = 'Advise CAS Students';
$actionRows[4]['precedence'] = '0';
$actionRows[4]['category'] = 'CAS';
$actionRows[4]['description'] = 'Allows coordinators and advisors to track student progress.';
$actionRows[4]['URLList'] = 'cas_adviseStudents.php, cas_adviseStudents_details.php, cas_adviseStudents_full.php, cas_adviseStudents_details_interview_add.php, cas_adviseStudents_details_interview_edit.php, cas_adviseStudents_details_interview_delete.php';
$actionRows[4]['entryURL'] = 'cas_adviseStudents.php';
$actionRows[4]['defaultPermissionAdmin'] = 'Y';
$actionRows[4]['defaultPermissionTeacher'] = 'Y';
$actionRows[4]['defaultPermissionStudent'] = 'N';
$actionRows[4]['defaultPermissionParent'] = 'N';
$actionRows[4]['defaultPermissionSupport'] = 'N';
$actionRows[4]['categoryPermissionStaff'] = 'Y';
$actionRows[4]['categoryPermissionStudent'] = 'N';
$actionRows[4]['categoryPermissionParent'] = 'N';
$actionRows[4]['categoryPermissionOther'] = 'N';

$actionRows[5]['name'] = 'Approve Commitments';
$actionRows[5]['precedence'] = '0';
$actionRows[5]['category'] = 'CAS';
$actionRows[5]['description'] = 'Allows coordinators and advisors to moderate and approve commitments .';
$actionRows[5]['URLList'] = 'cas_approveCommitments.php';
$actionRows[5]['entryURL'] = 'cas_approveCommitments.php';
$actionRows[5]['defaultPermissionAdmin'] = 'Y';
$actionRows[5]['defaultPermissionTeacher'] = 'Y';
$actionRows[5]['defaultPermissionStudent'] = 'N';
$actionRows[5]['defaultPermissionParent'] = 'N';
$actionRows[5]['defaultPermissionSupport'] = 'N';
$actionRows[5]['categoryPermissionStaff'] = 'Y';
$actionRows[5]['categoryPermissionStudent'] = 'N';
$actionRows[5]['categoryPermissionParent'] = 'N';
$actionRows[5]['categoryPermissionOther'] = 'N';

$actionRows[6]['name'] = 'IBO CAS Check';
$actionRows[6]['precedence'] = '0';
$actionRows[6]['category'] = 'CAS';
$actionRows[6]['description'] = 'Allows IBO personnel to access all CAS records of students finishing their Diploma in the current year.';
$actionRows[6]['URLList'] = 'cas_iboCheck.php,cas_iboCheck_details.php,cas_iboCheck_full.php';
$actionRows[6]['entryURL'] = 'cas_iboCheck.php';
$actionRows[6]['defaultPermissionAdmin'] = 'N';
$actionRows[6]['defaultPermissionTeacher'] = 'N';
$actionRows[6]['defaultPermissionStudent'] = 'N';
$actionRows[6]['defaultPermissionParent'] = 'N';
$actionRows[6]['defaultPermissionSupport'] = 'N';
$actionRows[6]['categoryPermissionStaff'] = 'N';
$actionRows[6]['categoryPermissionStudent'] = 'N';
$actionRows[6]['categoryPermissionParent'] = 'N';
$actionRows[6]['categoryPermissionOther'] = 'Y';

$actionRows[7]['name'] = 'Reflection';
$actionRows[7]['precedence'] = '0';
$actionRows[7]['category'] = 'CAS';
$actionRows[7]['description'] = 'Allows students to create a reflective journal for CAS.';
$actionRows[7]['URLList'] = 'cas_student_reflections.php, cas_student_reflections_add.php, cas_student_reflections_edit.php, cas_student_reflections_delete.php';
$actionRows[7]['entryURL'] = 'cas_student_reflections.php';
$actionRows[7]['defaultPermissionAdmin'] = 'N';
$actionRows[7]['defaultPermissionTeacher'] = 'N';
$actionRows[7]['defaultPermissionStudent'] = 'Y';
$actionRows[7]['defaultPermissionParent'] = 'N';
$actionRows[7]['defaultPermissionSupport'] = 'N';
$actionRows[7]['categoryPermissionStaff'] = 'N';
$actionRows[7]['categoryPermissionStudent'] = 'Y';
$actionRows[7]['categoryPermissionParent'] = 'N';
$actionRows[7]['categoryPermissionOther'] = 'N';

$actionRows[8]['name'] = 'Student: Interview 2';
$actionRows[8]['precedence'] = '0';
$actionRows[8]['category'] = 'CAS';
$actionRows[8]['description'] = 'Allows students to prefill data for Interview 2.';
$actionRows[8]['URLList'] = 'cas_student_interview2.php';
$actionRows[8]['entryURL'] = 'cas_student_interview2.php';
$actionRows[8]['defaultPermissionAdmin'] = 'N';
$actionRows[8]['defaultPermissionTeacher'] = 'N';
$actionRows[8]['defaultPermissionStudent'] = 'Y';
$actionRows[8]['defaultPermissionParent'] = 'N';
$actionRows[8]['defaultPermissionSupport'] = 'N';
$actionRows[8]['categoryPermissionStaff'] = 'N';
$actionRows[8]['categoryPermissionStudent'] = 'Y';
$actionRows[8]['categoryPermissionParent'] = 'N';
$actionRows[8]['categoryPermissionOther'] = 'N';

$actionRows[9]['name'] = 'Student: Interview 3';
$actionRows[9]['precedence'] = '0';
$actionRows[9]['category'] = 'CAS';
$actionRows[9]['description'] = 'Allows students to prefill data for Interview 3';
$actionRows[9]['URLList'] = 'cas_student_interview3.php';
$actionRows[9]['entryURL'] = 'cas_student_interview3.php';
$actionRows[9]['defaultPermissionAdmin'] = 'N';
$actionRows[9]['defaultPermissionTeacher'] = 'N';
$actionRows[9]['defaultPermissionStudent'] = 'Y';
$actionRows[9]['defaultPermissionParent'] = 'N';
$actionRows[9]['defaultPermissionSupport'] = 'N';
$actionRows[9]['categoryPermissionStaff'] = 'N';
$actionRows[9]['categoryPermissionStudent'] = 'Y';
$actionRows[9]['categoryPermissionParent'] = 'N';
$actionRows[9]['categoryPermissionOther'] = 'N';

$actionRows[10]['name'] = 'Invite Supervisor Feedback';
$actionRows[10]['precedence'] = '0';
$actionRows[10]['category'] = 'CAS';
$actionRows[10]['description'] = 'Allows coordinators and supervisors to invite commitment supervisors to give feedback.';
$actionRows[10]['URLList'] = 'cas_supervisor_invite.php';
$actionRows[10]['entryURL'] = 'cas_supervisor_invite.php';
$actionRows[10]['defaultPermissionAdmin'] = 'Y';
$actionRows[10]['defaultPermissionTeacher'] = 'Y';
$actionRows[10]['defaultPermissionStudent'] = 'N';
$actionRows[10]['defaultPermissionParent'] = 'N';
$actionRows[10]['defaultPermissionSupport'] = 'N';
$actionRows[10]['categoryPermissionStaff'] = 'Y';
$actionRows[10]['categoryPermissionStudent'] = 'N';
$actionRows[10]['categoryPermissionParent'] = 'N';
$actionRows[10]['categoryPermissionOther'] = 'N';

$actionRows[11]['name'] = 'View CAS in Student Profile';
$actionRows[11]['precedence'] = '0';
$actionRows[11]['category'] = '';
$actionRows[11]['description'] = "Allows CAS details to be viewed in Gibbon\'s student profile.";
$actionRows[11]['URLList'] = 'hook_studentProfile_cas.php';
$actionRows[11]['entryURL'] = '';
$actionRows[11]['defaultPermissionAdmin'] = 'Y';
$actionRows[11]['defaultPermissionTeacher'] = 'Y';
$actionRows[11]['defaultPermissionStudent'] = 'N';
$actionRows[11]['defaultPermissionParent'] = 'Y';
$actionRows[11]['defaultPermissionSupport'] = 'N';
$actionRows[11]['categoryPermissionStaff'] = 'Y';
$actionRows[11]['categoryPermissionStudent'] = 'N';
$actionRows[11]['categoryPermissionParent'] = 'Y';
$actionRows[11]['categoryPermissionOther'] = 'N';

$array = array();
$array['sourceModuleName'] = 'IB Diploma';
$array['sourceModuleAction'] = 'View CAS in Student Profile';
$array['sourceModuleInclude'] = 'hook_studentProfile_cas.php';
$hooks[0] = "INSERT INTO `gibbonHook` (`gibbonHookID`, `name`, `type`, `options`, gibbonModuleID) VALUES (NULL, 'IB Diploma CAS', 'Student Profile', '".serialize($array)."', (SELECT gibbonModuleID FROM gibbonModule WHERE name='$name'));";
