<?php

namespace Unusualify\Modularity\Http\Requests;

use Closure;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Unusualify\Modularity\Traits\ManageTraits;

abstract class Request extends FormRequest
{
    use ManageTraits;

    /**
     * Determines if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Gets the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        switch ($this->method()) {
            case 'POST':{
                return $this->hydrateRules(array_merge($this->rulesForAll(),$this->rulesForCreate()));
            }
            case 'PUT':{
                return $this->hydrateRules(array_merge($this->rulesForAll(),$this->rulesForUpdate()));
            }
            default:break;
        }

        return [];
    }

    public function hydrateRules($rules) {
        $locales = getLocales();
        $localeActive = false;
        $model = $this->model();

        if($model){
            $translatedAttributes = (method_exists($model, 'isTranslatable') && $model->isTranslatable()) ? $model->getTranslatedAttributes() : [];
            $translatedRules = Arr::only($rules, $translatedAttributes);
            $rules = Arr::except($rules, $translatedAttributes);

            if(count($translatedAttributes) > 0){
                foreach ($locales as $locale) {

                    // $language = Collection::make($this->request->all('languages'))->where('value', $locale)->first();
                    // $currentLocaleActive = $language['published'] ?? false;
                    $currentLocaleActive = true;

                    $rules = $this->updateRules( $rules, $translatedRules, $locale, $currentLocaleActive);

                    if ($currentLocaleActive) {
                        $localeActive = true;
                    }
                }
            }
        }
        // dd($rules);
        return $rules;
    }

    /**
     * Gets the validation rules that apply to the translated fields.
     *
     * @return array
     */
    protected function rulesForTranslatedFields($rules, $fields)
    {
        $locales = getLocales();
        $localeActive = false;

        if ($this->request->has('languages')) {
            foreach ($locales as $locale) {
                $language = Collection::make($this->request->all('languages'))->where('value', $locale)->first();
                $currentLocaleActive = $language['published'] ?? false;
                $rules = $this->updateRules($rules, $fields, $locale, $currentLocaleActive);

                if ($currentLocaleActive) {
                    $localeActive = true;
                }
            }
        }

        if (! $localeActive) {
            $rules = $this->updateRules($rules, $fields, reset($locales));
        }

        return $rules;
    }

    /**
     * @param array $rules
     * @param array $fields
     * @param string $locale
     * @param bool $localeActive
     * @return array
     */
    private function updateRules($rules, $fields, $locale, $localeActive = true)
    {
        $fieldNames = array_keys($fields);
        // $table = $this->model()->getTable();
        // $table = get_class($this->model());
        // $translationTable =  App::make($this->model()->getTranslationModelName())->getTable();
        $translationTableClass =  $this->model()->getTranslationModelName();
        $request = $this->request;

        foreach ($fields as $field => $fieldRules) {
            if (is_string($fieldRules)) {
                $fieldRules = explode('|', $fieldRules);
            }

            $fieldRules = Collection::make($fieldRules);

            // Remove required rules, when locale is not active
            if (! $localeActive) {
                $hasRequiredRule = $fieldRules->contains(function ($rule) {
                    return $this->ruleStartsWith($rule, 'required');
                });

                $fieldRules = $fieldRules->reject(function ($rule) {
                    return $this->ruleStartsWith($rule, 'required');
                });

                // @TODO: Can be replaced with doesntContain in twill 3.x
                if ($hasRequiredRule && !in_array($fieldRules, $fieldRules->toArray())) {
                    $fieldRules->add('nullable');
                }
            }


            $rules["{$field}.{$locale}"] = $fieldRules->map(function ($rule) use ($locale, $fieldNames, $translationTableClass, $request) {
                // allows using validation rule that references other fields even for translated fields
                if ($this->ruleStartsWith($rule, 'required_') && Str::contains($rule, $fieldNames)) {
                    foreach ($fieldNames as $fieldName) {
                        $rule = str_replace($fieldName, "{$fieldName}.{$locale}", $rule);
                    }
                }

                if ($this->ruleStartsWith($rule, 'unique_translation') && Str::contains($rule, $fieldNames)) {
                    // $unique_fields = explode(',', explode(':',$rule)[1]);

                    $rule = function (string $attribute, mixed $value, Closure $fail) use($translationTableClass){
                        [$_field, $_locale] = explode('.', $attribute);
                        $records = $translationTableClass::query()
                            ->where($_field, $value)
                            ->where('locale', $_locale)
                            ->get();
                        if($records->count() > 0){
                            $fail("The field exists on system, please enter an unique {$_field}.");
                        }
                    };
                }

                return $rule;
            })->toArray();
        }
        // dd($rules);
        return $rules;
    }

    /**
     * @param mixed $rule
     * @param string $needle
     *
     * @return bool
     */
    private function ruleStartsWith($rule, $needle)
    {
        return is_string($rule) && Str::startsWith($rule, $needle);
    }

    /**
     * Gets the error messages for the defined validation rules.
     *
     * @param array $messages
     * @param array $fields
     * @return array
     */
    protected function messagesForTranslatedFields($messages, $fields)
    {
        foreach (getLocales() as $locale) {
            $messages = $this->updateMessages($messages, $fields, $locale);
        }

        return $messages;
    }

    /**
     * @param array $messages
     * @param array $fields
     * @param string $locale
     * @return array
     */
    private function updateMessages($messages, $fields, $locale)
    {
        foreach ($fields as $field => $message) {
            $fieldSplitted = explode('.', $field);
            $rule = array_pop($fieldSplitted);
            $field = implode('.', $fieldSplitted);
            $messages["{$field}.{$locale}.$rule"] = str_replace('{lang}', $locale, $message);
        }

        return $messages;
    }
}
