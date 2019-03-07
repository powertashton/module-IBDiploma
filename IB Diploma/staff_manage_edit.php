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
    echo "<div class='error'>";
    echo 'You do not have access to this action.';
    echo '</div>';
} else {
    //Proceed!
    echo "<div class='trail'>";
    echo "<div class='trailHead'><a href='".$_SESSION[$guid]['absoluteURL']."'>Home</a> > <a href='".$_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/'.getModuleName($_GET['q']).'/'.getModuleEntry($_GET['q'], $connection2, $guid)."'>".getModuleName($_GET['q'])."</a> > <a href='".$_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/'.getModuleName($_GET['q'])."/staff_manage.php'>Manage CAS Staff</a> > </div><div class='trailEnd'>Edit CAS Staff</div>";
    echo '</div>';

    if (isset($_GET['return'])) {
        returnProcess($guid, $_GET['return'], null, null);
    }

    //Check if school year specified
    $ibDiplomaCASStaffID = $_GET['ibDiplomaCASStaffID'];
    if ($ibDiplomaCASStaffID == 'Y') { echo "<div class='error'>";
        echo 'You have not specified an activity.';
        echo '</div>';
    } else {
        try {
            $data = array('ibDiplomaCASStaffID' => $ibDiplomaCASStaffID);
            $sql = "SELECT ibDiplomaCASStaffID, ibDiplomaCASStaff.role, surname, preferredName FROM ibDiplomaCASStaff JOIN gibbonPerson ON (ibDiplomaCASStaff.gibbonPersonID=gibbonPerson.gibbonPersonID) WHERE status='Full' AND ibDiplomaCASStaffID=:ibDiplomaCASStaffID";
            $result = $connection2->prepare($sql);
            $result->execute($data);
        } catch (PDOException $e) {
            echo "<div class='error'>".$e->getMessage().'</div>';
        }

        if ($result->rowCount() == 0) {
            echo "<div class='error'>";
            echo 'The selected activity does not exist.';
            echo '</div>';
        } else {
            //Let's go!
            $values = $result->fetch();
           
            
            $form = Form::create('editStaff', $_SESSION[$guid]['absoluteURL'].'/modules/'.$_SESSION[$guid]['module'].'/staff_manage_editProcess.php?ibDiplomaCASStaffID='.$ibDiplomaCASStaffID.'', 'post');
 			
 			$form->setFactory(DatabaseFormFactory::create($pdo));
			$form->setClass('smallIntBorder fullWidth');
			
 			$form->addHiddenValue('address', $_SESSION[$guid]['address']);
			$form->addHiddenValue('ibDiplomaCASStaffID', $ibDiplomaCASStaffID);
			
 			$form->setFactory(DatabaseFormFactory::create($pdo));
			$form->setClass('smallIntBorder fullWidth');
			
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
