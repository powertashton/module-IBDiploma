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

include '../../functions.php';
include '../../config.php';

//New PDO DB connection
try {
    $connection2 = new PDO("mysql:host=$databaseServer;dbname=$databaseName", $databaseUsername, $databasePassword);
    $connection2->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $connection2->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    echo $e->getMessage();
}

@session_start();

//Set timezone from session variable
date_default_timezone_set($_SESSION[$guid]['timezone']);

$ibDiplomaStudentID = $_GET['ibDiplomaStudentID'];
$URL = $_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/'.getModuleName($_POST['address'])."/student_manage_edit.php&ibDiplomaStudentID=".$ibDiplomaStudentID;

if (isActionAccessible($guid, $connection2, '/modules/IB Diploma/student_manage_edit.php') == false) {

    //Fail 0
    $URL = $URL.'&return=error0';
    header("Location: {$URL}");
} else {
    //Proceed!
    //Check if school year specified
    if ($ibDiplomaStudentID == '') {
        //Fail1
        $URL = $URL.'&return=error1';
        header("Location: {$URL}");
    } else {
        try {
            $data = array('ibDiplomaStudentID' => $ibDiplomaStudentID);
            $sql = 'SELECT * FROM ibDiplomaStudent WHERE ibDiplomaStudentID=:ibDiplomaStudentID';
            $result = $connection2->prepare($sql);
            $result->execute($data);
        } catch (PDOException $e) {
            //Fail2
            $URL = $URL.'&return=error2';
            header("Location: {$URL}");
            exit();
        }

        if ($result->rowCount() != 1) {
            //Fail 2
            $URL = $URL.'&return=error2';
            header("Location: {$URL}");
        } else {
            //Validate Inputs
            $gibbonSchoolYearIDStart = $_POST['gibbonSchoolYearIDStart'];
            $gibbonSchoolYearIDEnd = $_POST['gibbonSchoolYearIDEnd'];
            if ($_POST['gibbonPersonIDCASAdvisor'] != '') {
                $gibbonPersonIDCASAdvisor = $_POST['gibbonPersonIDCASAdvisor'];
            } else {
                $gibbonPersonIDCASAdvisor = null;
            }

            if ($gibbonSchoolYearIDStart == '' or $gibbonSchoolYearIDEnd == '') {
                //Fail 3
                $URL = $URL.'&return=error3';
                header("Location: {$URL}");
            } else {
                //Write to database
                try {
                    $data = array('gibbonSchoolYearIDStart' => $gibbonSchoolYearIDStart, 'gibbonSchoolYearIDEnd' => $gibbonSchoolYearIDEnd, 'gibbonPersonIDCASAdvisor' => $gibbonPersonIDCASAdvisor, 'ibDiplomaStudentID' => $ibDiplomaStudentID);
                    $sql = 'UPDATE ibDiplomaStudent SET gibbonSchoolYearIDStart=:gibbonSchoolYearIDStart, gibbonSchoolYearIDEnd=:gibbonSchoolYearIDEnd, gibbonPersonIDCASAdvisor=:gibbonPersonIDCASAdvisor WHERE ibDiplomaStudentID=:ibDiplomaStudentID';
                    $result = $connection2->prepare($sql);
                    $result->execute($data);
                } catch (PDOException $e) {
                    //Fail 2
                    $URL = $URL.'&return=error5';
                    header("Location: {$URL}");
                    exit();
                }

                //Success 0
                $URL = $URL.'&return=success0';
                header("Location: {$URL}");
            }
        }
    }
}
