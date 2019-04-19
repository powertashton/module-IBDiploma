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

@session_start();

use Gibbon\Forms\Form;
use Gibbon\Forms\DatabaseFormFactory;

//Module includes
include './modules/'.$_SESSION[$guid]['module'].'/moduleFunctions.php';

if (isActionAccessible($guid, $connection2, '/modules/IB Diploma/student_manage_add.php') == false) {
    //Acess denied
    echo "<div class='error'>";
    echo 'You do not have access to this action.';
    echo '</div>';
} else {
    echo "<div class='trail'>";
    echo "<div class='trailHead'><a href='".$_SESSION[$guid]['absoluteURL']."'>Home</a> > <a href='".$_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/'.getModuleName($_GET['q']).'/'.getModuleEntry($_GET['q'], $connection2, $guid)."'>".getModuleName($_GET['q'])."</a> > <a href='".$_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/'.getModuleName($_GET['q'])."/student_manage.php'>Student Enrolment</a> > </div><div class='trailEnd'>Add Student Enrolment</div>";
    echo '</div>';

    if (isset($_GET['return'])) {
        returnProcess($guid, $_GET['return'], null, null);
    }
    
    $form = Form::create('addStudent', $_SESSION[$guid]['absoluteURL'].'/modules/'.$_SESSION[$guid]['module'].'/student_manage_addProcess.php');
    $form->setFactory(DatabaseFormFactory::create($pdo));
    $form->addHiddenValue('address', $_SESSION[$guid]['address']);
    
    
    $row = $form->addRow();
        $row->addLabel('gibbonPersonID', __('Students'));
        $row->addSelectStudent('gibbonPersonID',$_SESSION[$guid]['gibbonSchoolYearID'], array('allStudents' => true, 'byName' => false, 'byRoll' => true, 'showRoll' => true))->selectMultiple()->isRequired();
        
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
            
    echo $form->getOutput();


}
?>
