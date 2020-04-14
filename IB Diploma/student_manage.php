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

//Module includes
include './modules/'.$_SESSION[$guid]['module'].'/moduleFunctions.php';
use Gibbon\Services\Format;
use Gibbon\Tables\DataTable;


if (isActionAccessible($guid, $connection2, '/modules/IB Diploma/student_manage.php') == false) {
    //Acess denied
    $page->addError(__('You do not have access to this action.'));
} else {
        $page->breadcrumbs
        ->add(__('Manage Student Enrolment'));
    
    echo '<p>';
    echo 'This page only displays students enroled in the current school year.';
    echo '</p>';

    if (isset($_GET['return'])) {
        returnProcess($guid, $_GET['return'], null, null);
    }
    try {
        $data = array('gibbonSchoolYearID' => $_SESSION[$guid]['gibbonSchoolYearID']);
        $sql = "SELECT ibDiplomaStudentID, student.surname, student.preferredName, start.name AS start, end.name AS end, gibbonYearGroup.nameShort AS yearGroup, gibbonRollGroup.nameShort AS rollGroup, gibbonPersonIDCASAdvisor, advisor.surname AS advisorSurname, advisor.preferredName as advisorPreferredName FROM ibDiplomaStudent JOIN gibbonPerson AS student ON (ibDiplomaStudent.gibbonPersonID=student.gibbonPersonID) JOIN gibbonStudentEnrolment ON (ibDiplomaStudent.gibbonPersonID=gibbonStudentEnrolment.gibbonPersonID) LEFT JOIN gibbonSchoolYear AS start ON (start.gibbonSchoolYearID=ibDiplomaStudent.gibbonSchoolYearIDStart) LEFT JOIN gibbonSchoolYear AS end ON (end.gibbonSchoolYearID=ibDiplomaStudent.gibbonSchoolYearIDEnd) LEFT JOIN gibbonYearGroup ON (gibbonStudentEnrolment.gibbonYearGroupID=gibbonYearGroup.gibbonYearGroupID) LEFT JOIN gibbonRollGroup ON (gibbonStudentEnrolment.gibbonRollGroupID=gibbonRollGroup.gibbonRollGroupID) RIGHT JOIN gibbonPerson AS advisor ON (ibDiplomaStudent.gibbonPersonIDCASAdvisor = advisor.gibbonPersonID) WHERE gibbonStudentEnrolment.gibbonSchoolYearID=:gibbonSchoolYearID AND student.status='Full' ORDER BY start.sequenceNumber DESC, surname, preferredName";
        $result = $pdo->select($sql, $data)->toDataSet();
    } catch (PDOException $e) { $page->addError(__('Students cannot be displayed.')); }
    
    $table = DataTable::create('casStudentManage')->withData($result);
    $table->addHeaderAction('add')->setURL('/modules/'.$_SESSION[$guid]['module'].'/student_manage_add.php');
    
    $table->addColumn('name', __('Name'))->format(Format::using('name', ['', 'preferredName', 'surname', 'Student', true]));
    $table->addColumn('rollGroup', __('Roll Group'));
    $table->addColumn('start', __('Start')); 
    $table->addColumn('end', __('End'));   
    $table->addColumn('advisor', __('Advisor'))->format(Format::using('name', ['title', 'advisorPreferredName', 'advisorSurname', 'Staff', false, true]));               
    $table->addActionColumn()
        ->addParam('ibDiplomaStudentID')
        ->format(function ($valuesContributions, $actions) use ($guid) {
            $actions->addAction('edit', __('Edit'))
                    ->setURL('/modules/'.$_SESSION[$guid]['module'].'/student_manage_edit.php');
            $actions->addAction('delete', __('Delete'))
                    ->setURL('/modules/'.$_SESSION[$guid]['module'].'/student_manage_delete.php');
        });     
    echo $table->render($result);
    
}
