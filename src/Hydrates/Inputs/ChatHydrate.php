<?php

namespace Unusualify\Modularity\Hydrates\Inputs;

class ChatHydrate extends InputHydrate
{
    /**
     * Default values to set before hydrating
     *
     *
     * @var array
     */
    public $requirements = [
        'default' => -1,
    ];

    /**
     * Manipulate Input Schema Structure
     *
     * @return void
     */
    public function hydrate()
    {
        $input = $this->input;

        // add your logic
        $input['type'] = 'input-chat';

        $input['endpoints'] = [
            'index' => route('admin.chatable.index', ['chat' => ':id']),
            'store' => route('admin.chatable.store', ['chat' => ':id']),
            'show' => route('admin.chatable.show', ['chat_message' => ':id']),
            'update' => route('admin.chatable.update', ['chat_message' => ':id']),
            'destroy' => route('admin.chatable.destroy', ['chat_message' => ':id']),
            'attachments' => route('admin.chatable.attachments', ['chat' => ':id']),
        ];

        $acceptedFileTypes = $input['accepted-file-types']
            ?? 'application/pdf,application/msword,application/vnd.openxmlformats-officedocument.wordprocessingml.document,application/x-iwork-pages-sffpages';

        $maxAttachments = $input['max-attachments'] ?? 2;
        $input['filepond'] = modularity_format_input([
            'type' => 'filepond',
            'name' => 'attachments',
            'accepted-file-types' => $acceptedFileTypes,
            'max' => $maxAttachments,
        ]);

        $input['name'] = '_chat_id';
        $input['label'] = 'Messages';

        $input['creatable'] = 'hidden';

        return $input;
    }
}
