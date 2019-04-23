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

if (isActionAccessible($guid, $connection2, '/modules/IB Diploma/staff_manage_add.php') == false) {

    //Acess denied
    $page->addError(__('You do not have access to this action.'));
} else {
    $page->breadcrumbs
        ->add(__('Manage CAS Staff'), 'staff_manage.php')
        ->add(__('Add CAS Staff'));
        
    $returns = array();
    $returns['error4'] = __('Add failed because the selected person is already registered.');
    $editLink = '';
    if (isset($_GET['editID'])) {
        $editLink = $_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/IB Diploma/staff_manage_edit.php&ibDiplomaCASStaffID='.$_GET['editID'];
    }
    if (isset($_GET['return'])) {
        returnProcess($guid, $_GET['return'], $editLink, $returns);
    }

    try{
    $data = array();
    $sql = "SELECT * FROM gibbonPerson JOIN gibbonStaff ON (gibbonPerson.gibbonPersonID=gibbonStaff.gibbonPersonID) WHERE status='Full' ORDER BY surname, preferredName";
    $result = $connection2->prepare($sql);
    $result->execute($data);
    } catch (PDOException $e) {
    }
    
    
    $form = Form::create('addStaff',  $_SESSION[$guid]['absoluteURL'].'/modules/'.$_SESSION[$guid]['module'].'/staff_manage_addProcess.php', 'post');
    
    $form->addHiddenValue('address', $_SESSION[$guid]['address']);
    $form->setFactory(DatabaseFormFactory::create($pdo));


    $row = $form->addRow();
        $row->addLabel('gibbonPersonID', __('Staff'));
        $row->addSelectStaff('gibbonPersonID')->placeholder()->isRequired();

     $row = $form->addRow();
         $row->addLabel('role', __('Role'));
        $row->addSelect('role')->fromArray(array('Coordinator' =>__('Coordinator'), 'Advisor' => __('Advisor')))->placeholder()->isRequired();

    $row = $form->addRow();
    $row->addFooter();
    $row->addSubmit();

    echo $form->getOutput();
}
?>
