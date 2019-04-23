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

if (isActionAccessible($guid, $connection2, '/modules/IB Diploma/staff_manage_edit.php') == false) {

    //Acess denied
    $page->addError(__('You do not have access to this action.'));
} else {
    //Proceed!
    $page->breadcrumbs
        ->add(__('Manage CAS Staff'), 'staff_manage.php')
        ->add(__('Edit CAS Staff'));
    if (isset($_GET['return'])) {
        returnProcess($guid, $_GET['return'], null, null);
    }

    //Check if school year specified
    $ibDiplomaCASStaffID = $_GET['ibDiplomaCASStaffID'];
    if ($ibDiplomaCASStaffID == 'Y') {$page->addError(__('You have not specified an activity.'));
    } else {
        try {
            $data = array('ibDiplomaCASStaffID' => $ibDiplomaCASStaffID);
            $sql = "SELECT ibDiplomaCASStaffID, ibDiplomaCASStaff.role, surname, preferredName FROM ibDiplomaCASStaff JOIN gibbonPerson ON (ibDiplomaCASStaff.gibbonPersonID=gibbonPerson.gibbonPersonID) WHERE status='Full' AND ibDiplomaCASStaffID=:ibDiplomaCASStaffID";
            $result = $connection2->prepare($sql);
            $result->execute($data);
        } catch (PDOException $e) {
            $page->addError($e->getMessage());
        }

        if ($result->rowCount() == 0) {
            $page->addError(__('The selected activity does not exist.'));
        } else {
            //Let's go!
            $values = $result->fetch();
           
            
            $form = Form::create('editStaff', $_SESSION[$guid]['absoluteURL'].'/modules/'.$_SESSION[$guid]['module'].'/staff_manage_editProcess.php?ibDiplomaCASStaffID='.$ibDiplomaCASStaffID.'', 'post');
             
             $form->setFactory(DatabaseFormFactory::create($pdo));
            
            
             $form->addHiddenValue('address', $_SESSION[$guid]['address']);
            $form->addHiddenValue('ibDiplomaCASStaffID', $ibDiplomaCASStaffID);
            
             $form->setFactory(DatabaseFormFactory::create($pdo));
            
            
            $row = $form->addRow();
                $row->addLabel('Staff',__('Staff'));
                $row->addTextField('gibbonPersonName')->readOnly()->setValue(formatName('', $values['preferredName'], $values['surname'], 'Staff', true, true));
                 
            $row = $form->addRow();
                $row->addLabel('role',__('Role'));
                $row->addSelect('role')->fromArray(array('Coordinator' => __('Coordinator'), 'Advisor' => __('Advisor')))->selected($values['role'])->isRequired();

            
            $row = $form->addRow();
                $row->addFooter();
                $row->addSubmit();
                
            $form->loadAllValuesFrom($values);
            
            echo $form->getOutput();    

        }
    }
}
?>
