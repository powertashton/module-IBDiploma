<?php
//USE ;end TO SEPERATE SQL STATEMENTS. DON'T USE ;end IN ANY OTHER PLACES!

$sql = array();
$count = 0;

//v0.6.00
$sql[$count][0] = '0.6.00';
$sql[$count][1] = "ALTER TABLE `ibDiplomaCASCommitment` DROP `creativity` ,DROP `action` ,DROP `service` ,DROP `outcome1Intention` ,DROP `outcome2Intention` ,DROP `outcome3Intention` ,DROP `outcome4Intention` ,DROP `outcome5Intention` ,DROP `outcome6Intention` ,DROP `outcome7Intention` ,DROP `outcome8Intention` ,DROP `outcome1Evaluation` ,DROP `outcome2Evaluation` ,DROP `outcome3Evaluation` ,DROP `outcome4Evaluation` ,DROP `outcome5Evaluation` ,DROP `outcome6Evaluation` ,DROP `outcome7Evaluation` ,DROP `outcome8Evaluation` ;end
INSERT IGNORE INTO gibbonAction (`gibbonModuleID`, `name`, `precedence`, `category`, `description`, `URLList`, `entryURL`, `defaultPermissionAdmin`, `defaultPermissionTeacher`, `defaultPermissionStudent`, `defaultPermissionParent`, `defaultPermissionSupport`, `categoryPermissionStaff`, `categoryPermissionStudent`, `categoryPermissionParent`, `categoryPermissionOther`) SELECT gibbonModuleID, 'Reflectiions', 0, 'CAS', 'Allows students to create a reflective journal for CAS', 'cas_reflections.php, cas_reflections_add.php, cas_reflections_edit.php, cas_reflections_delete.php', 'cas_reflections.php', 'N', 'N', 'Y', 'N', 'N', 'N', 'Y', 'N', 'N' FROM gibbonModule WHERE name = 'IB Diploma';end
INSERT IGNORE INTO gibbonPermission (gibbonRoleID, gibbonActionID) SELECT 3, gibbonActionID FROM gibbonAction WHERE category = 'CAS' AND name = 'Reflections';end
CREATE TABLE `ibDiplomaCASReflection` (`ibDiplomaCASReflectionID` INT( 12 ) UNSIGNED ZEROFILL NOT NULL AUTO_INCREMENT PRIMARY KEY ,`ibDiplomaCASCommitmentID` INT( 12 ) UNSIGNED ZEROFILL NULL ,`gibbonPersonID` INT( 10 ) UNSIGNED ZEROFILL NOT NULL ,`timestamp` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ,`reflection` TEXT NOT NULL) ENGINE = MYISAM ;end
ALTER TABLE `ibDiplomaStudent` DROP `casStatusIBO` ;end
ALTER TABLE `ibDiplomaCASCommitment` ADD `goals` VARCHAR( 255 ) NOT NULL AFTER `description` ;end
ALTER TABLE `ibDiplomaStudent` CHANGE `casStatusSchool` `casStatusSchool` ENUM( '', 'At Risk', 'On Task', 'Excellence' ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL ;end
UPDATE `gibbonAction` SET `URLList` = 'cas_student_myCommitments.php, cas_student_myCommitments_add.php, cas_student_myCommitments_edit.php, cas_student_myCommitments_delete.php, cas_student_myCommitments_view.php', `entryURL` = 'cas_student_myCommitments.php' WHERE `gibbonAction`.`gibbonActionID` =0000107;end
UPDATE `gibbonAction` SET `URLList` = 'cas_student_reflections.php, cas_student_reflections_add.php, cas_student_reflections_edit.php, cas_student_reflections_delete.php',`entryURL` = 'cas_student_reflections.php' WHERE `gibbonAction`.`gibbonActionID` =0000111;end
ALTER TABLE `ibDiplomaCASInterview` CHANGE `gibbonPersonIDInterviewer` `1_gibbonPersonIDInterviewer` INT( 10 ) UNSIGNED ZEROFILL NOT NULL ,CHANGE `timestamp` `1_timestamp` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ,CHANGE `notes` `1_notes` TEXT CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL ;end
ALTER TABLE `ibDiplomaCASInterview` ADD UNIQUE (`gibbonPersonIDInterviewee`) ;end
ALTER TABLE `ibDiplomaCASInterview` CHANGE `1_timestamp` `1_date` DATE NOT NULL
INSERT IGNORE INTO gibbonAction (`gibbonModuleID`, `name`, `precedence`, `category`, `description`, `URLList`, `entryURL`, `defaultPermissionAdmin`, `defaultPermissionTeacher`, `defaultPermissionStudent`, `defaultPermissionParent`, `defaultPermissionSupport`, `categoryPermissionStaff`, `categoryPermissionStudent`, `categoryPermissionParent`, `categoryPermissionOther`) SELECT gibbonModuleID, 'Student: Interview 2', 0, 'CAS', 'Allows students to prefill data for Interview 2', 'cas_student_interview2.php', 'cas_student_interview2.php', 'N', 'N', 'Y', 'N', 'N', 'N', 'Y', 'N', 'N' FROM gibbonModule WHERE name = 'IB Diploma';end
INSERT IGNORE INTO gibbonPermission (gibbonRoleID, gibbonActionID) SELECT 3, gibbonActionID FROM gibbonAction WHERE category = 'CAS' AND name = 'Student: Interview 2';end
ALTER TABLE `ibDiplomaCASInterview` ADD `2_outcome1` VARCHAR( 255 ) NOT NULL ,ADD `2_outcome2` VARCHAR( 255 ) NOT NULL , ADD `2_outcome3` VARCHAR( 255 ) NOT NULL , ADD `2_outcome4` VARCHAR( 255 ) NOT NULL , ADD `2_outcome5` VARCHAR( 255 ) NOT NULL , ADD `2_outcome6` VARCHAR( 255 ) NOT NULL , ADD `2_outcome7` VARCHAR( 255 ) NOT NULL , ADD `2_outcome8` VARCHAR( 255 ) NOT NULL ;end
ALTER TABLE `gibbon`.`ibDiplomaCASCommitment` ADD UNIQUE (`gibbonPersonID` ,`name`) ;end
ALTER TABLE `ibDiplomaCASInterview` ADD `2_gibbonPersonIDInterviewer` INT( 10 ) UNSIGNED ZEROFILL NULL DEFAULT NULL AFTER `1_notes` ,ADD `2_date` DATE NULL DEFAULT NULL AFTER `2_gibbonPersonIDInterviewer` ,ADD `2_notes` TEXT NOT NULL AFTER `2_date` ;end
INSERT IGNORE INTO gibbonAction (`gibbonModuleID`, `name`, `precedence`, `category`, `description`, `URLList`, `entryURL`, `defaultPermissionAdmin`, `defaultPermissionTeacher`, `defaultPermissionStudent`, `defaultPermissionParent`, `defaultPermissionSupport`, `categoryPermissionStaff`, `categoryPermissionStudent`, `categoryPermissionParent`, `categoryPermissionOther`) SELECT gibbonModuleID, 'Student: Interview 3', 0, 'CAS', 'Allows students to prefill data for Interview 3', 'cas_student_interview3.php', 'cas_student_interview3.php', 'N', 'N', 'Y', 'N', 'N', 'N', 'Y', 'N', 'N' FROM gibbonModule WHERE name = 'IB Diploma';end
INSERT IGNORE INTO gibbonPermission (gibbonRoleID, gibbonActionID) SELECT 3, gibbonActionID FROM gibbonAction WHERE category = 'CAS' AND name = 'Student: Interview 3';end
ALTER TABLE `ibDiplomaCASInterview` ADD `3_gibbonPersonIDInterviewer` int(10) unsigned zerofill NULL DEFAULT NULL ,ADD `3_date` DATE NULL DEFAULT NULL AFTER `3_gibbonPersonIDInterviewer` ,ADD `3_notes` TEXT NULL DEFAULT NULL AFTER `3_date`, ADD `3_outcome1` VARCHAR( 255 ) NOT NULL ,ADD `3_outcome1Notes` TEXT NOT NULL ,ADD `3_outcome2` VARCHAR( 255 ) NOT NULL ,ADD `3_outcome2Notes` TEXT NOT NULL ,ADD `3_outcome3` VARCHAR( 255 ) NOT NULL ,ADD `3_outcome3Notes` TEXT NOT NULL ,ADD `3_outcome4` VARCHAR( 255 ) NOT NULL ,ADD `3_outcome4Notes` TEXT NOT NULL ,ADD `3_outcome5` VARCHAR( 255 ) NOT NULL ,ADD `3_outcome5Notes` TEXT NOT NULL ,ADD `3_outcome6` VARCHAR( 255 ) NOT NULL ,ADD `3_outcome6Notes` TEXT NOT NULL ,ADD `3_outcome7` VARCHAR( 255 ) NOT NULL ,ADD `3_outcome7Notes` TEXT NOT NULL ,ADD `3_outcome8` VARCHAR( 255 ) NOT NULL ,ADD `3_outcome8Notes` TEXT NOT NULL ;end
CREATE TABLE `ibDiplomaCASSupervisorFeedback` (`ibDiplomaCASSupervisorFeedbackID` INT( 14 ) UNSIGNED ZEROFILL NOT NULL AUTO_INCREMENT PRIMARY KEY ,`ibDiplomaCASCommitmentID` INT( 12 ) UNSIGNED ZEROFILL NOT NULL ,`key` VARCHAR( 40 ) NOT NULL) ENGINE = MYISAM ;end
ALTER TABLE `ibDiplomaCASSupervisorFeedback` ADD `complete` ENUM( 'N', 'Y' ) NOT NULL DEFAULT 'N' ;end
ALTER TABLE `ibDiplomaCASSupervisorFeedback` ADD `attendance` ENUM( '','<60%', '60-84%', '85-99%', '100%' ) NOT NULL, ADD `evaluation` TEXT NOT NULL ;end
ALTER TABLE `ibDiplomaCASSupervisorFeedback` ADD UNIQUE (`key`) ;end
INSERT IGNORE INTO gibbonAction (`gibbonModuleID`, `name`, `precedence`, `category`, `description`, `URLList`, `entryURL`, `defaultPermissionAdmin`, `defaultPermissionTeacher`, `defaultPermissionStudent`, `defaultPermissionParent`, `defaultPermissionSupport`, `categoryPermissionStaff`, `categoryPermissionStudent`, `categoryPermissionParent`, `categoryPermissionOther`) SELECT gibbonModuleID, 'Invite Supervisor Feedback', 0, 'CAS', 'Allows coordinators and supervisors to invite commitment supervisors to give feedback.', 'cas_supervisor_invite.php', 'cas_supervisor_invite.php', 'Y', 'Y', 'N', 'N', 'N', 'Y', 'N', 'N', 'N' FROM gibbonModule WHERE name = 'IB Diploma';end
INSERT IGNORE INTO gibbonPermission (gibbonRoleID, gibbonActionID) SELECT 1, gibbonActionID FROM gibbonAction WHERE category = 'CAS' AND name = 'Invite Supervisor Feedback';end
INSERT IGNORE INTO gibbonPermission (gibbonRoleID, gibbonActionID) SELECT 2, gibbonActionID FROM gibbonAction WHERE category = 'CAS' AND name = 'Invite Supervisor Feedback';end
ALTER TABLE `ibDiplomaStudent` CHANGE `casStatusSchool` `casStatusSchool` ENUM( '', 'At Risk', 'On Task', 'Excellence', 'Complete', 'Incomplete' ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL ;end
ALTER TABLE `ibDiplomaCASCommitment` DROP `outcome1` ,DROP `outcome2` ,DROP `outcome3` ,DROP `outcome4` ,DROP `outcome5` ,DROP `outcome6` ,DROP `outcome7` ,DROP `outcome8` ;end
";

//v0.7.00
++$count;
$sql[$count][0] = '0.7.00';
$sql[$count][1] = "INSERT INTO `gibbonHook` (`gibbonHookID`, `name`,`type`, `sourceModuleName`, `sourceModuleAction`, `sourceModuleInclude`) VALUES (NULL, 'IB CAS in Student Profile', 'Student Profile', 'IB Diploma', 'View CAS in Student Profile', 'hook_studentProfile_cas.php');end
INSERT INTO `gibbonAction` (`gibbonModuleID` ,`name` ,`precedence` ,`category` ,`description` ,`URLList` ,`entryURL` ,`defaultPermissionAdmin` ,`defaultPermissionTeacher` ,`defaultPermissionStudent` ,`defaultPermissionParent` ,`defaultPermissionSupport` ,`categoryPermissionStaff` ,`categoryPermissionStudent` ,`categoryPermissionParent` ,`categoryPermissionOther`) VALUES ((SELECT gibbonModuleID FROM gibbonModule WHERE name='IB Diploma'), 'View CAS in Student Profile', 0, '', 'Allows CAS details to be viewed in Gibbon\'s student profile.', 'hook_studentProfile_cas.php', '', 'Y', 'Y', 'N', 'Y', 'N', 'Y', 'N', 'Y', 'N');end
INSERT INTO `gibbonPermission` (`permissionID` ,`gibbonRoleID` ,`gibbonActionID`) VALUES (NULL , '1', (SELECT gibbonActionID FROM gibbonAction JOIN gibbonModule ON (gibbonAction.gibbonModuleID=gibbonModule.gibbonModuleID) WHERE gibbonModule.name='IB Diploma' AND gibbonAction.name='View CAS in Student Profile'));end
INSERT INTO `gibbonPermission` (`permissionID` ,`gibbonRoleID` ,`gibbonActionID`) VALUES (NULL , '2', (SELECT gibbonActionID FROM gibbonAction JOIN gibbonModule ON (gibbonAction.gibbonModuleID=gibbonModule.gibbonModuleID) WHERE gibbonModule.name='IB Diploma' AND gibbonAction.name='View CAS in Student Profile'));end
INSERT INTO `gibbonPermission` (`permissionID` ,`gibbonRoleID` ,`gibbonActionID`) VALUES (NULL , '4', (SELECT gibbonActionID FROM gibbonAction JOIN gibbonModule ON (gibbonAction.gibbonModuleID=gibbonModule.gibbonModuleID) WHERE gibbonModule.name='IB Diploma' AND gibbonAction.name='View CAS in Student Profile'));end
";

//v0.7.01
++$count;
$sql[$count][0] = '0.7.01';
$sql[$count][1] = 'ALTER TABLE `ibDiplomaCASInterview`
CHANGE `2_outcome1` `2_outcome1` TEXT CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
CHANGE `2_outcome2` `2_outcome2` TEXT CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
CHANGE `2_outcome3` `2_outcome3` TEXT CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
CHANGE `2_outcome4` `2_outcome4` TEXT CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
CHANGE `2_outcome5` `2_outcome5` TEXT CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
CHANGE `2_outcome6` `2_outcome6` TEXT CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
CHANGE `2_outcome7` `2_outcome7` TEXT CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
CHANGE `2_outcome8` `2_outcome8` TEXT CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
CHANGE `3_outcome1` `3_outcome1` TEXT CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
CHANGE `3_outcome2` `3_outcome2` TEXT CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
CHANGE `3_outcome3` `3_outcome3` TEXT CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
CHANGE `3_outcome4` `3_outcome4` TEXT CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
CHANGE `3_outcome5` `3_outcome5` TEXT CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
CHANGE `3_outcome6` `3_outcome6` TEXT CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
CHANGE `3_outcome7` `3_outcome7` TEXT CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
CHANGE `3_outcome8` `3_outcome8` TEXT CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL;end
';

//v0.7.02
++$count;
$sql[$count][0] = '0.7.02';
$sql[$count][1] = '
';

//v0.7.03
++$count;
$sql[$count][0] = '0.7.03';
$sql[$count][1] = "
UPDATE gibbonAction JOIN gibbonModule ON (gibbonAction.gibbonModuleID=gibbonModule.gibbonModuleID) SET gibbonAction.name='Reflection' WHERE gibbonAction.name='Reflectiions' AND gibbonModule.name='IB Diploma' ;end
ALTER TABLE  `ibDiplomaCASReflection` ADD  `title` VARCHAR( 100 ) NOT NULL AFTER  `timestamp`;end
";

//v0.8.00
++$count;
$sql[$count][0] = '0.8.00';
$sql[$count][1] = '
';

//v0.8.01
++$count;
$sql[$count][0] = '0.8.01';
$sql[$count][1] = '
';

//v0.8.02
++$count;
$sql[$count][0] = '0.8.02';
$sql[$count][1] = '
';

//v0.8.03
++$count;
$sql[$count][0] = '0.8.03';
$sql[$count][1] = '
';

//v0.8.04
++$count;
$sql[$count][0] = '0.8.04';
$sql[$count][1] = '
';

//v1.0.00
++$count;
$sql[$count][0] = '1.0.00';
$sql[$count][1] = '
';

//v1.0.01
++$count;
$sql[$count][0] = '1.0.01';
$sql[$count][1] = '
';

//v1.0.02
++$count;
$sql[$count][0] = '1.0.02';
$sql[$count][1] = '
';

//v1.0.03
++$count;
$sql[$count][0] = '1.0.03';
$sql[$count][1] = '
';

//v1.0.04
++$count;
$sql[$count][0] = '1.0.04';
$sql[$count][1] = '
';

//v1.0.05
++$count;
$sql[$count][0] = '1.0.05';
$sql[$count][1] = '
';

//v1.0.06
++$count;
$sql[$count][0] = '1.0.06';
$sql[$count][1] = '
';

//v1.0.07
++$count;
$sql[$count][0] = '1.0.07';
$sql[$count][1] = '
';

//v1.0.08
++$count;
$sql[$count][0] = '1.0.08';
$sql[$count][1] = '
';

//v1.0.09
++$count;
$sql[$count][0] = '1.0.09';
$sql[$count][1] = '
';

//v1.0.10
++$count;
$sql[$count][0] = '1.0.10';
$sql[$count][1] = '
';

//v1.0.11
++$count;
$sql[$count][0] = '1.0.11';
$sql[$count][1] = '
';
