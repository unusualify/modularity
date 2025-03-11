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
        'height' => '40vh',
        'bodyHeight' => '26vh',
        'variant' => 'outlined', // ['elevated', 'flat', 'tonal', 'outlined', 'text', 'plain'] https://vuetifyjs.com/en/components/cards/#variants
        'elevation' => 0,
        'color' => 'grey-lighten-2',
        'inputVariant' => 'outlined',
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
            'pinnedMessage' => route('admin.chatable.pinned-message', ['chat' => ':id']),
        ];

        if (isset($input['acceptedExtensions']) && is_array($input['acceptedExtensions'])) {
            $input['accepted-file-types'] = $this->getAcceptedFileTypes($input['acceptedExtensions']);
            unset($input['acceptedExtensions']);
        }

        $filepondAcceptedFileTypes = isset($input['acceptedExtensions']) && is_array($input['acceptedExtensions'])
            ? $input['acceptedExtensions']
            : ['pdf', 'doc', 'docx', 'pages'];

        $acceptedFileTypes = $input['accepted-file-types']
            ?? $this->getAcceptedFileTypes($filepondAcceptedFileTypes);

        $maxAttachments = $input['max-attachments'] ?? 3;
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
