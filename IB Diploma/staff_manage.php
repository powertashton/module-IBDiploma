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

if (isActionAccessible($guid, $connection2, '/modules/IB Diploma/staff_manage.php') == false) {
    //Acess denied
    $page->addError(__('You do not have access to this action.'));
} else {
    $page->breadcrumbs
        ->add(__('Manage CAS Staff'));
    if (isset($_GET['return'])) {
        returnProcess($guid, $_GET['return'], null, null);
    }
    
    try {
    $data = array();
    $sql = "SELECT ibDiplomaCASStaffID, ibDiplomaCASStaff.role, surname, preferredName FROM ibDiplomaCASStaff JOIN gibbonPerson ON (ibDiplomaCASStaff.gibbonPersonID=gibbonPerson.gibbonPersonID) WHERE status='Full' ORDER BY role, surname, preferredName";
    $result = $pdo->select($sql, $data)->toDataSet();
    } catch (PDOException $e) { $page->addError($e->getMessage());}
    
    $table = DataTable::create('casStaffManage')->withData($result);
    $table->addHeaderAction('add')->setURL('/modules/'.$_SESSION[$guid]['module'].'/staff_manage_add.php');
    
    $table->addColumn('name', __('Name'))->format(Format::using('name', ['', 'preferredName', 'surname', 'Student', false]));
    $table->addColumn('role', __('Role'));
    $table->addActionColumn()
        ->addParam('ibDiplomaCASStaffID')
        ->format(function ($row, $actions) use ($guid) {
            $actions->addAction('edit', __('Edit'))
                    ->setURL('/modules/'.$_SESSION[$guid]['module'].'/staff_manage_edit.php');
            $actions->addAction('delete', __('Delete'))
                    ->setURL('/modules/'.$_SESSION[$guid]['module'].'/staff_manage_delete.php');
        }); 
    echo $table->render($result);

}
