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
    public function getCountFor($method, $args = [])
    {
        // dd($method);
        $query = $this->model->newQuery();

        if (method_exists($this->getModel(), 'scope' . ucfirst($method))) {
            return $this->filter($query, $this->countScope)->$method(...$args)->count();
        }

        throw new \Exception('Method scope' . ucfirst($method) . ' does not exist');
    }
}
