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

if (isActionAccessible($guid, $connection2, '/modules/IB Diploma/cas_student_myCommitments_add.php') == false) {
    //Acess denied
    echo "<div class='error'>";
    echo 'You do not have access to this action.';
    echo '</div>';
} else {
    if (enroled($guid, $_SESSION[$guid]['gibbonPersonID'], $connection2) == false) {
        //Acess denied
        echo "<div class='error'>";
        echo 'You are not enroled in the IB Diploma programme.';
        echo '</div>';
    } else {
        echo "<div class='trail'>";
        echo "<div class='trailHead'><a href='".$_SESSION[$guid]['absoluteURL']."'>Home</a> > <a href='".$_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/'.getModuleName($_GET['q']).'/'.getModuleEntry($_GET['q'], $connection2, $guid)."'>".getModuleName($_GET['q'])."</a> > <a href='".$_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/'.getModuleName($_GET['q'])."/cas_student_myCommitments.php'>My Commitments</a> > </div><div class='trailEnd'>Add Commitment</div>";
        echo '</div>';

        $returns = array();
        $editLink = '';
        if (isset($_GET['editID'])) {
            $editLink = $_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/IB Diploma/cas_student_myCommitments_edit.php&ibDiplomaCASCommitmentID='.$_GET['editID'];
        }
        if (isset($_GET['return'])) {
            returnProcess($guid, $_GET['return'], $editLink, $returns);
        }

        $step = null;
        if (isset($_GET['step'])) {
            $step = $_GET['step'];
        }
        if ($step != 1 and $step != 2) {
            $step = 1;
        }

        //Step 1
        if ($step == 1) {
        
        $form = Form::create('action',$_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/'.$_SESSION[$guid]['module'].'/cas_student_myCommitments_add.php&step=2');
            $form->setClass('smallIntBorder fullWidth');
            $form->addHiddenValue('address', $_SESSION[$guid]['address']);
            $form->addHiddenValue('step', 2);
           
           	$form->addRow()->addHeading(__('Commitment Source'));
           	
            $row = $form->addRow();
			$row->addLabel('Commitment Type', __('Commitment Type'));
			$row->addRadio("type1")->fromArray(array("New" =>__("New"), "From School Activity" =>__("From School Activity")))->inline();
           

				$dataSelect = array('gibbonPersonID' => $_SESSION[$guid]['gibbonPersonID'], 'gibbonSchoolYearID' => $_SESSION[$guid]['gibbonSchoolYearID']);
				$sqlSelect = "SELECT gibbonActivity.gibbonActivityID as value, gibbonActivity.name as name FROM gibbonActivity JOIN gibbonActivityStudent ON (gibbonActivity.gibbonActivityID=gibbonActivityStudent.gibbonActivityID) WHERE active='Y' AND gibbonPersonID=:gibbonPersonID AND gibbonSchoolYearID=:gibbonSchoolYearID ORDER BY name";

            
            $form->toggleVisibilityByClass('chooseActivity')->onRadio('type1')->when('From School Activity');
            $row = $form->addRow()->addClass('chooseActivity');
				$row->addLabel('chooseActivity', __('Type'));
				$row->addSelect('chooseActivity')->fromQuery($pdo, $sqlSelect, $dataSelect)->placeholder(__('Please select...'));
					
            $row = $form->addRow();
				$row->addFooter();
				$row->addSubmit("Go");
			echo $form->getOutput();
			

        } else {
            $type = $_POST['type1'];
            if ($type != 'New' and $type != 'From School Activity') {
                $type = 'New';
            }
            if ($type == 'From School Activity') {
                $gibbonActivityID = $_POST['chooseActivity'];
                if ($gibbonActivityID == '') {
                    echo "<div class='warning'>";
                    echo 'You have not specified an activity.';
                    echo '</div>';
                } else {
                    try {
                        $dataActivity = array('gibbonActivityID' => $gibbonActivityID);
                        $sqlActivity = 'SELECT * FROM gibbonActivity WHERE gibbonActivityID=:gibbonActivityID';
                        $resultActivity = $connection2->prepare($sqlActivity);
                        $resultActivity->execute($dataActivity);
                    } catch (PDOException $e) {
                        echo "<div class='error'>".$e->getMessage().'</div>';
                    }

                    if ($resultActivity->rowCount() != 1) {
                        echo "<div class='warning'>";
                        echo 'The specified activity does not exist.';
                        echo '</div>';
                    } else {
                        $rowActivity = $resultActivity->fetch();
                    }
                }
            }
            
            
            if ($type == 'From School Activity' and $gibbonActivityID != '') {
                        try {
                            $dataCoord = array('gibbonActivityID' => $gibbonActivityID);
                            $sqlCoord = "SELECT surname, preferredName, email, phone1 FROM gibbonActivityStaff JOIN gibbonPerson ON (gibbonActivityStaff.gibbonPersonID=gibbonPerson.gibbonPersonID) WHERE gibbonActivityID=:gibbonActivityID AND role='Organiser'";
                            $resultCoord = $connection2->prepare($sqlCoord);
                            $resultCoord->execute($dataCoord);
                        } catch (PDOException $e) {
                            echo "<div class='error'>".$e->getMessage().'</div>';
                        }

                        if ($resultCoord->rowCount() > 0) {
                            $rowCoord = $resultCoord->fetch();
                        }
                    } 
                    
                    //If experience is not from school activity, page will throw errors for undefined variables for the setValues but is not neccesarily an actual issue for a standard user
                    //Potentially TODO: Address that
                    
					$form = Form::create('action', $_SESSION[$guid]['absoluteURL'].'/modules/'.$_SESSION[$guid]['module'].'/cas_student_myCommitments_addProcess.php');
						$form->setClass('smallIntBorder fullWidth');
						$form->addHiddenValue('address', $_SESSION[$guid]['address']);
						$form->setFactory(DatabaseFormFactory::create($pdo));
						
						$form->addRow()->addHeading(__('Basic Information'));
						
						$row = $form->addRow();
							$row->addLabel('name', __('Name'));
							$row->addTextField('name')->setValue($rowActivity['name'])->maxLength(30)->isRequired();
							
						$row = $form->addRow();
							$row->addLabel('status', __('Status'));
							$row->addSelect('status')->fromArray(array('Planning' =>__('Planning'), 'In Progress' => __('In Progress'), 'Complete' =>__('Complete')))->isRequired();

						$row = $form->addRow();
							$row->addLabel('dateStart', __('Start Date'));
							$row->addDate('dateStart')->setValue(dateConvertBack($guid, $rowActivity['programStart']))->isRequired();

						$row = $form->addRow();
							$row->addLabel('dateEnd', __('End Date'));
							$row->addDate('dateEnd')->setValue(dateConvertBack($guid, $rowActivity['programEnd']));

						$row = $form->addRow();
							$column = $row->addColumn();
								$column->addLabel('description', __('Description'))->description(__('Use this space to describe the activity you are undertaking. You may wish to include:<i><ul><li>What is the nature of the activity?</li><li>How long will it last?</li><li>How frequently will your take part?</li><li>How is it new and challenging?</li><li>What do you hope to accomplish?</li></ul></i>'));
								$column->addTextArea('description')->setRows(10)->setValue($rowActivity['description'])->setClass('fullWidth');

						
						$form->addRow()->addHeading(__('Supervisor'));
						$row = $form->addRow();
							$row->addLabel('supervisorName', __('Supervisor Name'));
							$row->addTextField('supervisorName')->setValue(formatName('', $rowCoord['preferredName'], $rowCoord['surname'], 'Staff', true, true))->maxLength(30)->isRequired();
						
						$row = $form->addRow();
							$row->addLabel('supervisorEmail', __('Supervisor Email'));
							$row->addEmail('supervisorEmail')->setValue($rowCoord['email'])->maxLength(30)->isRequired();
							
						
						$row = $form->addRow();
							$row->addLabel('supervisorPhone', __('Supervisor Phone'));
							$row->addTextField('supervisorPhone')->setValue($rowCoord['phone1'])->maxLength(30)->isRequired();
							
						$row = $form->addRow();
						$row->addFooter();
						$row->addSubmit();
						echo $form->getOutput();
        }
    }
}
?>
