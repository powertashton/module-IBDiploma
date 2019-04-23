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

if (isActionAccessible($guid, $connection2, '/modules/IB Diploma/cas_student_reflections_delete.php') == false) {
    //Acess denied
    $page->addError(__('You do not have access to this action.'));
} else {
    if (enroled($guid, $_SESSION[$guid]['gibbonPersonID'], $connection2) == false) {
        //Acess denied
        $page->addError(__('You are not enroled in the IB Diploma programme.'));
    } else {
        //Proceed!
        $page->breadcrumbs
            ->add(__('Reflections'), 'cas_student_reflections.php')
            ->add(__('Delete Reflection'));
            
        if (isset($_GET['return'])) {
            returnProcess($guid, $_GET['return'], null, null);
        }

        //Check if school year specified
        $ibDiplomaCASReflectionID = $_GET['ibDiplomaCASReflectionID'];
        if ($ibDiplomaCASReflectionID == '') {
            $page->addError(__('You have not specified a reflection'));
        } else {
            try {
                $data = array('gibbonPersonID' => $_SESSION[$guid]['gibbonPersonID'], 'ibDiplomaCASReflectionID' => $ibDiplomaCASReflectionID);
                $sql = 'SELECT * FROM ibDiplomaCASReflection WHERE gibbonPersonID=:gibbonPersonID AND ibDiplomaCASReflectionID=:ibDiplomaCASReflectionID';
                $result = $connection2->prepare($sql);
                $result->execute($data);
            } catch (PDOException $e) {
                $page->addError($e->getMessage());
            }

            if ($result->rowCount() != 1) {
                $page->addError(__('The selected reflection does not exist.'));
            } else {
                //Let's go!
                $values = $result->fetch();
                
                $form = DeleteForm::createForm($_SESSION[$guid]['absoluteURL'].'/modules/'.$_SESSION[$guid]['module']."/cas_student_reflections_deleteProcess.php?ibDiplomaCASReflectionID=$ibDiplomaCASReflectionID");
                echo $form->getOutput();
            }
        }
    }
}
?>
