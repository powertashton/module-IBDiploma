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

use Gibbon\Module\IBDiploma\Domain\CASStaffGateway;
use Gibbon\Forms\DatabaseFormFactory;
use Gibbon\Forms\Form;
use Gibbon\Services\Format;
use Gibbon\Domain\User\UserGateway;
use Gibbon\Tables\DataTable;
use Gibbon\Domain\DataSet;

//Module includes
include './modules/'.$_SESSION[$guid]['module'].'/moduleFunctions.php';

if (isActionAccessible($guid, $connection2, '/modules/IB Diploma/staff_manage.php') == false) {
    //Acess denied
    $page->addError(__('You do not have access to this action.'));
} else {
    $page->breadcrumbs
        ->add(__('Manage CAS Staff'));
    if (isset($_GET['return'])) {
        returnProcess($guid, $_GET['return'], null, null);
    }
    $CASStaffGateway = $container->get(CASStaffGateway::class);
    $criteria = $CASStaffGateway->newQueryCriteria()->fromPOST();
    $staff = $CASStaffGateway->queryCASStaff($criteria);
    
    $userGateway = $container->get(UserGateway::class);
    
    $table = DataTable::createPaginated('CASStaff', $criteria);
    $table->addColumn('gibbonPersonID', __('Name')) 
                ->description(__('Role'))
                ->format(function ($row) use ($userGateway) {
                    $staff = $userGateway->getByID($row['gibbonPersonID']);
                    
                    return Format::name($staff['title'], $staff['preferredName'], $staff['surname'], 'Staff') . '<br/>'. Format::small(__($row['role']));
                });

    $table->addActionColumn()
            ->addParam('ibDiplomaCASStaffID')
            ->format(function ($row, $actions) use ($gibbon) {
                $actions->addAction('edit', __('Edit'))
                        ->setURL('/modules/' . $gibbon->session->get('module') . '/staff_manage_edit.php');
                 $actions->addAction('delete', __('Delete'))
                        ->setURL('/modules/' . $gibbon->session->get('module') . '/staff_manage_delete.php');
            });

    echo $table->render($staff);
    
}
