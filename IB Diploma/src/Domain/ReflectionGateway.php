<?php
namespace Gibbon\Module\IBDiploma\Domain;

use Gibbon\Domain\Traits\TableAware;
use Gibbon\Domain\QueryCriteria;
use Gibbon\Domain\QueryableGateway;

/**
 * CAS Reflection Gateway
 *
 * @version v21
 * @since   v21
 */
class ReflectionGateway extends QueryableGateway
{
    use TableAware;

    private static $tableName = 'ibDiplomaCASReflection';
    private static $primaryKey = 'ibDiplomaCASReflectionID';
    private static $searchableColumns = [];

    
    public function queryCASReflection($criteria, $gibbonPersonID) {      
        $query = $this
            ->newQuery()
            ->from('ibDiplomaCASReflection')
            ->cols(['ibDiplomaCASCommitmentID', 'ibDiplomaCASReflectionID', 'title', 'reflection', 'timestamp', 'gibbonPersonID'])
            ->where('ibDiplomaCASReflection.gibbonPersonID=:gibbonPersonID')->bindvalue('gibbonPersonID', $gibbonPersonID);
        
        $criteria->addFilterRules([
            'ibDiplomaCASCommitmentID' => function ($query, $ibDiplomaCASCommitmentID) {
                return $query
                    ->where('ibDiplomaCASReflection.ibDiplomaCASCommitmentID=:ibDiplomaCASCommitmentID')->bindvalue('ibDiplomaCASCommitmentID', $ibDiplomaCASCommitmentID);
            }
         ]);
        
       return $this->runQuery($query, $criteria);
    }
}
        
