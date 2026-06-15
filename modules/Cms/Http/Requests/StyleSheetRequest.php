<?php

namespace Modules\Cms\Http\Requests;

use Unusualify\Modularous\Http\Requests\Request;

class StyleSheetRequest extends Request
{
    /**
     * @return array<string, mixed>
     */
    public function rulesForAll(): array
    {
        return [
            'slug' => 'nullable|string|max:191',
            'driver' => 'required|string|in:custom,bootstrap,tailwind,hybrid',
            'framework_version' => 'nullable|string|max:64',
            'framework_source' => 'nullable|string|in:cdn,vendor,build',
            'definition' => 'nullable|array',
            'scss_source' => 'nullable|string',
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function rulesForCreate(): array
    {
        return $this->rulesForAll();
    }

    /**
     * @return array<string, mixed>
     */
    public function rulesForUpdate(): array
    {
        return $this->rulesForAll();
    }
}
