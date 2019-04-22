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

if (isActionAccessible($guid, $connection2, '/modules/IB Diploma/cas_student_myCommitments_edit.php') == false) {

    //Acess denied
    $page->addError(__('You do not have access to this action.'));
} else {
    if (enroled($guid, $_SESSION[$guid]['gibbonPersonID'], $connection2) == false) {
        //Acess denied
        $page->addError(__('You are not enroled in the IB Diploma programme.'));
    } else {
        //Proceed!
        $page->breadcrumbs
            ->add(__('My Commitments'), 'cas_student_myCommitments.php')
            ->add(__('Edit Commitment'));
            
        if (isset($_GET['return'])) {
            returnProcess($guid, $_GET['return'], null, null);
        }

        //Check if school year specified
        $ibDiplomaCASCommitmentID = $_GET['ibDiplomaCASCommitmentID'];
        if ($ibDiplomaCASCommitmentID == 'Y') {
            $page->addError(__('You have not specified an activity.'));
        } else {
            try {
                $data = array('gibbonPersonID' => $_SESSION[$guid]['gibbonPersonID'], 'ibDiplomaCASCommitmentID' => $ibDiplomaCASCommitmentID);
                $sql = 'SELECT * FROM ibDiplomaCASCommitment WHERE gibbonPersonID=:gibbonPersonID AND ibDiplomaCASCommitmentID=:ibDiplomaCASCommitmentID';
                $result = $connection2->prepare($sql);
                $result->execute($data);
            } catch (PDOException $e) {
                $page->addError($e->getMessage());
            }

            if ($result->rowCount() != 1) {
                $page->addError(__('The specified commitment could not be loaded.'));
            } else {

               $values = $result->fetch();
               $form = Form::create('commitmentEdit', $_SESSION[$guid]['absoluteURL'].'/modules/'.$_SESSION[$guid]['module'].'/cas_student_myCommitments_editProcess.php');
                        
                        $form->addHiddenValue('address', $_SESSION[$guid]['address']);
                        $form->addHiddenValue('ibDiplomaCASCommitmentID', $ibDiplomaCASCommitmentID);
                        $form->setFactory(DatabaseFormFactory::create($pdo));


                        $form->addRow()->addHeading(__('Basic Information'));
                        
                        $row = $form->addRow();
                            $row->addLabel('name', __('Name'));
                            $row->addTextField('name')->setValue($values['name'])->maxLength(30)->readOnly()->isRequired();
                            
                        $row = $form->addRow();
                            $row->addLabel('status', __('Status'));
                            $row->addSelect('status')->fromArray(array('Planning' =>__('Planning'), 'In Progress' => __('In Progress'), 'Complete' =>__('Complete')))->selected($values['status'])->isRequired();

                        $row = $form->addRow();
                            $row->addLabel('dateStart', __('Start Date'));
                            $row->addDate('dateStart')->setValue(dateConvertBack($guid, $values['dateStart']))->isRequired();

                        $row = $form->addRow();
                            $row->addLabel('dateEnd', __('End Date'));
                            $row->addDate('dateEnd')->setValue(dateConvertBack($guid, $values['dateEnd']));

                        $row = $form->addRow();
                            $column = $row->addColumn();
                                $column->addLabel('description', __('Description'))->description(__('Use this space to describe the activity you are undertaking. You may wish to include:<i><ul><li>What is the nature of the activity?</li><li>How long will it last?</li><li>How frequently will your take part?</li><li>How is it new and challenging?</li><li>What do you hope to accomplish?</li></ul></i>'));
                                $column->addTextArea('description')->setRows(10)->setValue($values['description'])->setClass('fullWidth');
                                
                        
                        $form->addRow()->addHeading(__('Supervisor'));
                        $row = $form->addRow();
                            $row->addLabel('supervisorName', __('Supervisor Name'));
                            $row->addTextField('supervisorName')->setValue($values['supervisorName'])->maxLength(30)->isRequired();
                        
                        $row = $form->addRow();
                            $row->addLabel('supervisorEmail', __('Supervisor Email'));
                            $row->addEmail('supervisorEmail')->setValue($values['supervisorEmail'])->isRequired();
                        
                        
                        $row = $form->addRow();
                            $row->addLabel('supervisorPhone', __('Supervisor Phone'))->description(__('Type, country code, number.'));
                            $row->addTextField('supervisorPhone')->setValue($values['supervisorPhone'])->maxLength(30)->isRequired();
                        
                        $row = $form->addRow();
                        $row->addFooter();
                        $row->addSubmit();
                        echo $form->getOutput();
            
            }
        }
    }
}
?>
