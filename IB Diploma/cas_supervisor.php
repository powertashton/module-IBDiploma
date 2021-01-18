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
use Gibbon\Tables\DataTable;
use Gibbon\Services\Format;

//Module includes
include './modules/'.$_SESSION[$guid]['module'].'/moduleFunctions.php';

$key = null;
if (isset($_GET['key'])) {
    $key = $_GET['key'];
}
try {
    $dataKey = array('key' => $key);
    $sqlKey = "SELECT ibDiplomaCASSupervisorFeedback.*, ibDiplomaCASCommitment.*, surname, preferredName FROM ibDiplomaCASSupervisorFeedback JOIN ibDiplomaCASCommitment ON (ibDiplomaCASSupervisorFeedback.ibDiplomaCASCommitmentID=ibDiplomaCASCommitment.ibDiplomaCASCommitmentID) JOIN gibbonPerson ON (ibDiplomaCASCommitment.gibbonPersonID=gibbonPerson.gibbonPersonID) WHERE gibbonPerson.status='Full' AND ibDiplomaCASSupervisorFeedback.key=:key";
    $resultKey = $connection2->prepare($sqlKey);
    $resultKey->execute($dataKey);
} catch (PDOException $e) {
    $page->addError($e->getMessage());
}

if ($resultKey->rowCount() < 1) {
    $page->addError(__('The supervisor feedback form cannot be displayed.'));
} else {
    $page->breadcrumbs
        ->add(__('CAS Supervisor Feedback Form'));
        
    if (isset($_GET['return'])) {
        returnProcess($guid, $_GET['return'], null, null);
    }
    if (!isset($updateReturn)) {
        $updateReturn = NULL;
    }
    if ($updateReturn != 'success0') {
        $rowKey = $resultKey->fetch();

        //Check for pre-existing complete form for this commitment
        try {
            $dataComplete = array('ibDiplomaCASCommitmentID' => $rowKey['ibDiplomaCASCommitmentID']);
            $sqlComplete = "SELECT * FROM ibDiplomaCASSupervisorFeedback WHERE ibDiplomaCASCommitmentID=:ibDiplomaCASCommitmentID AND complete='Y'";
            $resultComplete = $connection2->prepare($sqlComplete);
            $resultComplete->execute($dataComplete);
        } catch (PDOException $e) {
            $page->addError($e->getMessage());
        }

        if ($resultComplete->rowCount() > 0) {
            $page->addError(__('Supervisor feedback has already been completed for this commitment.'));
        } else {
        
            $table = DataTable::createDetails('student');
            $table->addColumn('student', __('Student'))->format(Format::using('name', ['', 'preferredName', 'surname', 'Student', 'true']));
            $table->addColumn('name', __('Commitment'));
            $table->addColumn('timing', __('Timing'))
                        ->notSortable()
                        ->format(function ($row) {
                            if (substr($row['dateStart'], 0, 4) == substr($row['dateEnd'], 0, 4)) {
                                if (substr($row['dateStart'], 5, 2) == substr($row['dateEnd'], 5, 2)) {
                                    return date('F', mktime(0, 0, 0, substr($row['dateStart'], 5, 2))).' '.substr($row['dateStart'], 0, 4);
                                } else {
                                    return date('F', mktime(0, 0, 0, substr($row['dateStart'], 5, 2))).' - '.date('F', mktime(0, 0, 0, substr($row['dateEnd'], 5, 2))).' '.substr($row['dateStart'], 0, 4);
                                }
                            } else {
                                return date('F', mktime(0, 0, 0, substr($row['dateStart'], 5, 2))).' '.substr($row['dateStart'], 0, 4).' - '.date('F', mktime(0, 0, 0, substr($row['dateEnd'], 5, 2))).' '.substr($row['dateEnd'], 0, 4);
                            }
                        });
            echo $table->render([$rowKey]);
            
            $form = Form::create('supervisorEvaluation',$_SESSION[$guid]['absoluteURL'].'/modules/'.$_SESSION[$guid]['module']."/cas_supervisorProcess.php", "post");
            
            $form->addHiddenValue('address', $_SESSION[$guid]['address']);
            $form->addHiddenValue('key', $key);
            
                $row = $form->addRow();
                    $row->addLabel('attendance', __('Attendance'));
                    $row->addSelect('attendance')
                    ->fromArray(array('60%' =>__('60%'), '60-84%' => __('60-84%'),'85-99%' =>__('85-99%'),'100%' =>__('100%')))
                    ->placeholder()
                    ->isRequired();
                        
                $row = $form->addRow();
                    $column = $row->addColumn();
                    $column ->addLabel('evaluation', __('Evaluation'))->description(__('Please use the space below to briefly evaluate '.$rowKey['preferredName'].'s participation in this commitment. You may wish to consider: <i><ul style="margin-bottom: 0px"><li>Attitude</li><li>Enthusiasm</li><li>Dedication</li></ul></i>'));
                    $column ->addTextArea('evaluation')
                    ->setRows(10)
                    ->setClass('fullWidth')
                    ->isRequired();
            
            $row = $form->addRow();
                $row->addFooter();
                $row->addSubmit();
            echo $form->getOutput();
            
        }
    }
}
?>


