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

//Module includes
include './moduleFunctions.php';

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

$URL = $_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/'.getModuleName($_POST['address']).'/cas_student_reflections_add.php';

if (isActionAccessible($guid, $connection2, '/modules/IB Diploma/cas_student_reflections_add.php') == false) {
    //Fail 0
    $URL = $URL.'&return=error0';
    header("Location: {$URL}");
} else {
    if (enroled($guid, $_SESSION[$guid]['gibbonPersonID'], $connection2) == false) {
        //Fail 0
        $URL = $URL.'&return=error0';
        header("Location: {$URL}");
    } else {
        //Proceed!
        $title = $_POST['title'];
        $reflection = $_POST['reflection'];
        if ($_POST['type'] == 'Commitment Reflection') {
            $ibDiplomaCASCommitmentID = $_POST['ibDiplomaCASCommitmentID'];
        } else {
            $ibDiplomaCASCommitmentID = null;
        }

        if ($title == '' or $reflection == '' or ($_POST['type'] == 'Commitment Reflection' and $_POST['ibDiplomaCASCommitmentID'] == '')) {
            //Fail 3
            $URL = $URL.'&return=error3';
            header("Location: {$URL}");
        } else {
            try {
                $data = array('gibbonPersonID' => $_SESSION[$guid]['gibbonPersonID'], 'title' => $title, 'reflection' => $reflection, 'ibDiplomaCASCommitmentID' => $ibDiplomaCASCommitmentID);
                $sql = 'INSERT INTO ibDiplomaCASReflection SET gibbonPersonID=:gibbonPersonID, title=:title, reflection=:reflection, ibDiplomaCASCommitmentID=:ibDiplomaCASCommitmentID';
                $result = $connection2->prepare($sql);
                $result->execute($data);
            } catch (PDOException $e) {
                //Fail 2
                $URL = $URL.'&return=error2';
                header("Location: {$URL}");
                exit();
            }

            //Success 0
            $URL = $URL.'&return=success0';
            header("Location: {$URL}");
        }
    }
}
