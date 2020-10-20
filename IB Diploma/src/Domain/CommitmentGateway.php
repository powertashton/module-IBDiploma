<?php

namespace Gibbon\Module\IBDiploma\Domain;

use Gibbon\Domain\Traits\TableAware;
use Gibbon\Domain\QueryCriteria;
use Gibbon\Domain\QueryableGateway;

/**
 * Commitment Gateway
 *
 * @version v21
 * @since   v21
 */
class CommitmentGateway extends QueryableGateway
{
    use TableAware;

    private static $tableName = 'ibDiplomaCASCommitment';
    private static $primaryKey = 'ibDiplomaCASCommitmentID';
    private static $searchableColumns = [];

    public function queryCommitments($criteria) {      
        $query = $this
            ->newQuery()
            ->from('ibDiplomaStudent')
            ->cols(['ibDiplomaCASCommitment.*', 'gibbonPerson.gibbonPersonID', 'gibbonStudentEnrolment.gibbonYearGroupID', 'gibbonStudentEnrolment.gibbonRollGroupID', 'ibDiplomaStudentID', 'surname', 'preferredName', 'start.name AS start', 'end.name AS end', 'gibbonYearGroup.nameShort AS yearGroup', 'gibbonRollGroup.nameShort AS rollGroup', 'gibbonPersonIDCASAdvisor', 'casStatusSchool'])
            ->leftjoin('gibbonPerson', 'ibDiplomaStudent.gibbonPersonID=gibbonPerson.gibbonPersonID' )
            ->leftjoin('gibbonStudentEnrolment', 'ibDiplomaStudent.gibbonPersonID=gibbonStudentEnrolment.gibbonPersonID')
            ->leftJoin('gibbonSchoolYear AS start', 'start.gibbonSchoolYearID=ibDiplomaStudent.gibbonSchoolYearIDStart')
            ->leftJoin('gibbonSchoolYear AS end','end.gibbonSchoolYearID=ibDiplomaStudent.gibbonSchoolYearIDEnd')
            ->leftJoin('gibbonYearGroup','gibbonStudentEnrolment.gibbonYearGroupID=gibbonYearGroup.gibbonYearGroupID')
            ->leftJoin('gibbonRollGroup', 'gibbonStudentEnrolment.gibbonRollGroupID=gibbonRollGroup.gibbonRollGroupID')
            ->innerJoin('ibDiplomaCASCommitment', 'ibDiplomaCASCommitment.gibbonPersonID=gibbonPerson.gibbonPersonID')
            ->where('gibbonPerson.status="Full"');
            
            $criteria->addFilterRules([
            'gibbonStudentEnrolment' => function ($query, $gibbonSchoolYearID) {
                return $query
                    ->where('gibbonStudentEnrolment.gibbonSchoolYearID=:gibbonSchoolYearID ')->bindvalue('gibbonSchoolYearID', $gibbonSchoolYearID);
            },
            'sequenceStart' => function ($query, $gibbonSchoolYearSequenceNumber) {
                return $query
                    ->where('start.sequenceNumber<=:sequenceStart')->bindvalue('sequenceStart', $gibbonSchoolYearSequenceNumber);
            },
            'sequenceEnd' => function ($query, $gibbonSchoolYearSequenceNumber) {
                return $query
                    ->where('end.sequenceNumber>=:sequenceEnd')->bindvalue('sequenceEnd', $gibbonSchoolYearSequenceNumber);
            },
            'gibbonPersonID' => function ($query, $gibbonPersonID) {
                return $query
                    ->where('ibDiplomaCASCommitment.gibbonPersonID=:gibbonPersonID')->bindvalue('gibbonPersonID', $gibbonPersonID);
            },
            'gibbonPersonIDCASAdvisor' => function ($query, $approval) {
                return $query
                    ->where('ibDiplomaCASCommitment.gibbonPersonIDCASAdvisor=:gibbonPersonIDCASAdvisor')->bindvalue('gibbonPersonIDCASAdvisor', $gibbonPersonIDCASAdvisor);
            },
            'approval' => function ($query, $approval) {
                return $query
                    ->where('approval=:approval')->bindvalue('approval', $approval);
            }
        ]);

       return $this->runQuery($query, $criteria);
    }

    
}
