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

use Gibbon\Forms\Form;
use Gibbon\Forms\DatabaseFormFactory;

@session_start();

//Module includes
include './modules/'.$_SESSION[$guid]['module'].'/moduleFunctions.php';

if (isActionAccessible($guid, $connection2, '/modules/IB Diploma/student_manage_edit.php') == false) {
    //Acess denied
    echo "<div class='error'>";
    echo 'You do not have access to this action.';
    echo '</div>';
} else {
    //Proceed!
    echo "<div class='trail'>";
    echo "<div class='trailHead'><a href='".$_SESSION[$guid]['absoluteURL']."'>Home</a> > <a href='".$_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/'.getModuleName($_GET['q']).'/'.getModuleEntry($_GET['q'], $connection2, $guid)."'>".getModuleName($_GET['q'])."</a> > <a href='".$_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/'.getModuleName($_GET['q'])."/student_manage.php'>Student Enrolment</a> > </div><div class='trailEnd'>Edit Student Enrolment</div>";
    echo '</div>';

    if (isset($_GET['return'])) {
        returnProcess($guid, $_GET['return'], null, null);
    }

    //Check if school year specified
    $ibDiplomaStudentID = $_GET['ibDiplomaStudentID'];
    if ($ibDiplomaStudentID == 'Y') { echo "<div class='error'>";
        echo 'You have not specified an activity.';
        echo '</div>';
    } else {
        try {
            $data = array('gibbonSchoolYearID' => $_SESSION[$guid]['gibbonSchoolYearID'], 'ibDiplomaStudentID' => $ibDiplomaStudentID);
            $sql = "SELECT ibDiplomaStudentID, surname, preferredName, start.name AS start, end.name AS end, gibbonSchoolYearIDStart, gibbonSchoolYearIDEnd, gibbonPersonIDCASAdvisor FROM ibDiplomaStudent JOIN gibbonPerson ON (ibDiplomaStudent.gibbonPersonID=gibbonPerson.gibbonPersonID) JOIN gibbonStudentEnrolment ON (ibDiplomaStudent.gibbonPersonID=gibbonStudentEnrolment.gibbonPersonID) LEFT JOIN gibbonSchoolYear AS start ON (start.gibbonSchoolYearID=ibDiplomaStudent.gibbonSchoolYearIDStart) LEFT JOIN gibbonSchoolYear AS end ON (end.gibbonSchoolYearID=ibDiplomaStudent.gibbonSchoolYearIDEnd) LEFT JOIN gibbonYearGroup ON (gibbonStudentEnrolment.gibbonYearGroupID=gibbonYearGroup.gibbonYearGroupID) LEFT JOIN gibbonRollGroup ON (gibbonStudentEnrolment.gibbonRollGroupID=gibbonRollGroup.gibbonRollGroupID) WHERE gibbonStudentEnrolment.gibbonSchoolYearID=:gibbonSchoolYearID AND gibbonPerson.status='Full' AND ibDiplomaStudentID=:ibDiplomaStudentID ORDER BY start.sequenceNumber, surname, preferredName";
            $result = $connection2->prepare($sql);
            $result->execute($data);
        } catch (PDOException $e) {
            echo "<div class='error'>";
            echo 'The student cannot be edited due to a database error.';
            echo '</div>';
        }

        if ($result->rowCount() != 1) {
            echo "<div class='error'>";
            echo 'The selected activity does not exist.';
            echo '</div>';
        } else {
            //Let's go!
            $values = $result->fetch();

            $form = Form::create('editStudent', $_SESSION[$guid]['absoluteURL'].'/modules/'.$_SESSION[$guid]['module'].'/student_manage_editProcess.php?ibDiplomaStudentID='.$ibDiplomaStudentID, 'post');
             
             $form->setFactory(DatabaseFormFactory::create($pdo));
            $form->setClass('smallIntBorder fullWidth');
            
             $form->addHiddenValue('address', $_SESSION[$guid]['address']);
             
            
            $row = $form->addRow();
                $row->addLabel('Student',__('Student'));
                $row->addTextField('gibbonPersonName')->readOnly()->setValue(formatName('', $values['preferredName'], $values['surname'], 'Student', true, true));
            
            $data = array('gibbonSchoolYearID' => $_SESSION[$guid]['gibbonSchoolYearID']);
            $sql = "SELECT gibbonSchoolYearID as value,name FROM gibbonSchoolYear ORDER BY sequenceNumber";
            $row = $form->addRow();
                $row->addLabel('gibbonSchoolYearIDStart', __('Start Year'));
                $row->addSelect('gibbonSchoolYearIDStart')->fromQuery($pdo, $sql)->placeholder()->isRequired();
                
            $row = $form->addRow();
                $row->addLabel('gibbonSchoolYearIDEnd', __('End Year'));
                $row->addSelect('gibbonSchoolYearIDEnd')->fromQuery($pdo, $sql)->placeholder()->isRequired();

            $sql = "SELECT gibbonPerson.gibbonPersonID as value, concat(gibbonPerson.firstName, ' ',gibbonPerson.surname) As name FROM gibbonPerson inner join ibDiplomaCASStaff on ibDiplomaCASStaff.gibbonPersonID = gibbonPerson.gibbonPersonID ORDER BY  gibbonPerson.firstName";
            $row = $form->addRow();
                $row->addLabel('gibbonPersonIDCASAdvisor', __('CAS Advisor'));
                $row->addSelect('gibbonPersonIDCASAdvisor')->fromQuery($pdo, $sql)->placeholder();
            
            $row = $form->addRow();
                $row->addFooter();
                $row->addSubmit();
                
            $form->loadAllValuesFrom($values);
            
            echo $form->getOutput();
        }
    }
}
?>
