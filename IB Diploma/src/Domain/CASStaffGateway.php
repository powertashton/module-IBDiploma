<?php
namespace Gibbon\Module\IBDiploma\Domain;

use Gibbon\Domain\Traits\TableAware;
use Gibbon\Domain\QueryCriteria;
use Gibbon\Domain\QueryableGateway;

/**
 * CAS Staff Gateway
 *
 * @version v21
 * @since   v21
 */
class CASStaffGateway extends QueryableGateway
{
    use TableAware;

    private static $tableName = 'ibDiplomaCASStaff';
    private static $primaryKey = 'ibDiplomaCASStaffID';
    private static $searchableColumns = ['role', 'surname', 'preferredName'];

    //SELECT ibDiplomaCASStaffID, ibDiplomaCASStaff.role, surname, preferredName FROM ibDiplomaCASStaff JOIN gibbonPerson ON (ibDiplomaCASStaff.gibbonPersonID=gibbonPerson.gibbonPersonID) WHERE status='Full' ORDER BY role, surname, preferredName
    public function queryCASStaff($criteria) {      
        $query = $this
            ->newQuery()
            ->from('ibDiplomaCASStaff')
            ->cols(['gibbonPerson.gibbonPersonID', 'ibDiplomaCASStaffID', 'ibDiplomaCASStaff.role', 'surname', 'preferredName'])
            ->leftjoin('gibbonPerson', 'ibDiplomaCASStaff.gibbonPersonID=gibbonPerson.gibbonPersonID')
            ->where('gibbonPerson.status="Full"');

       return $this->runQuery($query, $criteria);
    }
}
        
