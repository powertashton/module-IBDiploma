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

use Gibbon\Forms\Prefab\DeleteForm;

@session_start();

//Module includes
include './modules/'.$_SESSION[$guid]['module'].'/moduleFunctions.php';

if (isActionAccessible($guid, $connection2, '/modules/IB Diploma/staff_manage_delete.php') == false) {

    //Acess denied
    $page->addError(__('You do not have access to this action.'));
} else {
    //Proceed!
    $page->breadcrumbs
        ->add(__('Manage CAS Staff'), 'staff_manage.php')
        ->add(__('Delete CAS Staff'));
    if (isset($_GET['return'])) {
        returnProcess($guid, $_GET['return'], null, null);
    }
    
    //Check if school year specified
    $ibDiplomaCASStaffID = $_GET['ibDiplomaCASStaffID'];
    if ($ibDiplomaCASStaffID == '') { $page->addError(__('You have not specified a staff member.'));
    } else {
        try {
            $data = array('ibDiplomaCASStaffID' => $ibDiplomaCASStaffID);
            $sql = "SELECT ibDiplomaCASStaffID, ibDiplomaCASStaff.role, surname, preferredName FROM ibDiplomaCASStaff JOIN gibbonPerson ON (ibDiplomaCASStaff.gibbonPersonID=gibbonPerson.gibbonPersonID) WHERE status='Full' AND ibDiplomaCASStaffID=:ibDiplomaCASStaffID";
            $result = $connection2->prepare($sql);
            $result->execute($data);
        } catch (PDOException $e) {
            $page->addError($e->getMessage());
        }

        if ($result->rowCount() != 1) {
            $page->addError(__('The selected staff member does not exist.'));
        } else {
            //Let's go!
            $values = $result->fetch();
            
            $form = DeleteForm::createForm($_SESSION[$guid]['absoluteURL'].'/modules/'.$_SESSION[$guid]['module']."/staff_manage_deleteProcess.php?ibDiplomaCASStaffID=$ibDiplomaCASStaffID?");
            echo $form->getOutput();

        }
    }
}
?>
