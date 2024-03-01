<?php

namespace Unusualify\Modularity\Entities\Traits;

use Illuminate\Support\Str;

trait IsHostable
{
    use HasSlug, HasScopes, ModelHelpers;

    protected $hostableColumn = 'url';

    public static function hostables()
    {
        return static::query()->hostable()->published()->get();
    }

    public function scopeHostable($query)
    {
        return $query->notParentHostable()->whereNotNull($this->getHostableColumn());
    }

    public function scopeNotParentHostable($query)
    {
        foreach ($this->definedRelations('BelongsTo') as $key => $relationship) {
            $relatedModel = $this->{$relationship}()->getRelated();
            if(in_array(__TRAIT__, class_uses_recursive($relatedModel))){
                $relatedModelHostableColumn = $relatedModel->getHostableColumn();
                $query = $query->whereHas($relationship, function($query) use($relatedModelHostableColumn){
                    $query->whereNull($relatedModelHostableColumn);
                });
            }
        }

        return $query;
    }

    public function getHostableColumn()
    {
        return $this->hostableColumn;
    }

    public static function hostableRouteBindingParameter()
    {
        return static::hostableRouteBindingParameterFormat(get_class_short_name(static::class));
    }

    public static function hostableRouteBindingParameterFormat($name)
    {
        return "{". Str::snake($name) . "}";
    }

    public function hostableChildRouteParameters()
    {
        return array_map(fn($ch) => self::hostableRouteBindingParameterFormat(get_class_short_name($ch)), $this->hostableChilds());
    }

    public function hostableRouteArguments()
    {
        return array_reduce(array_merge($this->hostableParentRecords(), [$this]), function($carry, $model){

            $carry[Str::snake(get_class_short_name($model))] = $model->getSlug();

            return $carry;

        }, []);
    }

    public function hostableParents()
    {
        $parents = [];

        foreach ($this->definedRelations('BelongsTo') as $key => $relationship) {
            $relatedModel = $this->{$relationship}()->getRelated();
            if(in_array(__TRAIT__, class_uses_recursive($relatedModel))){
                $parents[] = $relatedModel;
            }
        }

        return $parents;
    }

    public function hostableParentRecords()
    {
        $parents = [];

        foreach ($this->definedRelations('BelongsTo') as $key => $relationship) {
            $relatedModel = $this->{$relationship}()->getRelated();
            if(in_array(__TRAIT__, class_uses_recursive($relatedModel))){
                $parents[] =  $this->{$relationship};
            }
        }

        return $parents;
    }

    public function hostableChilds()
    {
        $childs = [];

        foreach ($this->definedRelations('HasMany') as $key => $relationship) {
            $relatedModel = $this->{$relationship}()->getRelated();
            if(in_array(__TRAIT__, class_uses_recursive($relatedModel))){
                $childs[] = $relatedModel;
            }
        }

        return $childs;
    }

}
