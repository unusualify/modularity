<?php

namespace Unusualify\Modularity\Repositories\Contracts;

interface Repository
{
    /**
     * Get the like operator for the repository.
     *
     * @return string
     */
    public function getLikeOperator();

    /**
     * Get the reserved fields for the repository.
     *
     * @return array
     */
    public function getReservedFields();

    /**
     * Get the model for the repository.
     *
     * @return \Illuminate\Database\Eloquent\Model
     */
    public function getModel();
}
