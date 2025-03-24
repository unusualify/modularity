<?php

namespace Unusualify\Modularity\Repositories\Logic;

trait CountBuilders
{
    use MethodTransformers;

    /**
     * @return int
     */
    public function getCountForAll()
    {
        $query = $this->model->newQuery();

        return $this->filter($query, $this->countScope)->count();
    }

    /**
     * @return int
     */
    public function getCountForPublished()
    {
        $query = $this->model->newQuery();

        return $this->filter($query, $this->countScope)->published()->count();
    }

    /**
     * @return int
     */
    public function getCountForDraft()
    {
        $query = $this->model->newQuery();

        return $this->filter($query, $this->countScope)->draft()->count();
    }

    /**
     * @return int
     */
    public function getCountForTrash()
    {
        $query = $this->model->newQuery();

        return $this->filter($query, $this->countScope)->onlyTrashed()->count();
    }

    /**
     * @return int
     */
    public function getCountFor($method)
    {
        // dd($method);
        $methodName = 'scope' . ucfirst($method[0]);

        return $this->model->$methodName();
    }

}