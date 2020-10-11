<?php
namespace Gibbon\Module\HelpDesk\Domain;

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
    
    
}
