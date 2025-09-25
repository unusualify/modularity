<?php

declare(strict_types=1);

namespace Unusualify\Modularity\Relations;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\HasOne;

class CreatorCompanyRelation extends HasOne
{
    protected string $creatorRecordTable;
    protected string $userTable;
    protected string $companyTable;
    protected string $creatableType;

    public function __construct(
        Builder $query,
        $parent,
        string $foreignKey,
        string $localKey,
        string $creatorRecordTable,
        string $userTable,
        string $companyTable,
        string $creatableType
    ) {
        parent::__construct($query, $parent, $foreignKey, $localKey);
        $this->creatorRecordTable = $creatorRecordTable;
        $this->userTable = $userTable;
        $this->companyTable = $companyTable;
        $this->creatableType = $creatableType;
    }

    public function getRelationExistenceQuery(Builder $query, Builder $parentQuery, $columns = ['*'])
    {
        $query = parent::getRelationExistenceQuery($query, $parentQuery, $columns);

        // Ensure creator_records and users are present in existence queries
        $query->join(
            $this->userTable,
            $this->userTable . '.company_id',
            '=',
            $this->companyTable . '.id'
        )->join(
            $this->creatorRecordTable,
            function ($join) {
                $join->on($this->creatorRecordTable . '.creator_id', '=', $this->userTable . '.id')
                    ->where($this->creatorRecordTable . '.creatable_type', '=', $this->creatableType);
            }
        );

        return $query;
    }
}
