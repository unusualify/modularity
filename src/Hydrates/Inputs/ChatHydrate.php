<?php

namespace Unusualify\Modularous\Hydrates\Inputs;

use Unusualify\Modularous\Entities\ChatMessage;

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
            'edit' => route('admin.chatable.edit', ['chat_message' => ':id']),
            'destroy' => route('admin.chatable.destroy', ['chat_message' => ':id']),
            'readAll' => route('admin.chatable.read-all', ['chat' => ':id']),
            'attachments' => route('admin.chatable.attachments', ['chat' => ':id']),
            'pinnedMessage' => route('admin.chatable.pinned-message', ['chat' => ':id']),
            'pinnedMessages' => route('admin.chatable.pinned-messages', ['chat' => ':id']),
            'starredMessages' => route('admin.chatable.starred-messages', ['chat' => ':id']),
            'unstarAll' => route('admin.chatable.unstar-all', ['chat' => ':id']),
        ];

        $acceptedExtensions = isset($input['acceptedExtensions']) && is_array($input['acceptedExtensions'])
            ? $input['acceptedExtensions']
            : null;

        if ($acceptedExtensions !== null) {
            $input['accepted-file-types'] = $this->getAcceptedFileTypes($acceptedExtensions);
            unset($input['acceptedExtensions']);
        }

        $filepondAcceptedFileTypes = $acceptedExtensions ?? ['pdf', 'doc', 'docx', 'pages'];

        $acceptedFileTypes = $input['accepted-file-types']
            ?? $this->getAcceptedFileTypes($filepondAcceptedFileTypes);

        $maxAttachments = $input['max-attachments'] ?? 3;
        $input['filepond'] = modularous_format_input([
            'type' => 'filepond',
            'name' => 'filepond',
            'accepted-file-types' => $acceptedFileTypes,
            'max' => $maxAttachments,
            'noRules' => true,
        ])['filepond'];

        $input['maxPinnedMessages'] ??= ChatMessage::getMaxPinnedMessages();
        $input['pinDurations'] ??= $this->getPinDurationItems();

        $input['name'] = '_chat_id';
        $input['label'] ??= __('messages.chatable.input.label');

        $input['noSubmit'] = true;
        $input['creatable'] = 'hidden';
        $input['rules'] = '';

        return $input;
    }

    /**
     * Pin duration options for the frontend, e.g. [['value' => 24, 'title' => '24 hours'], ...].
     *
     * Durations that are whole days (except a single day) are shown in days, like WhatsApp.
     *
     * @return array<int, array{value: int, title: string}>
     */
    protected function getPinDurationItems(): array
    {
        return collect(ChatMessage::getPinDurations())
            ->map(fn (int $hours) => [
                'value' => $hours,
                'title' => $hours > 24 && $hours % 24 === 0
                    ? trans_choice(':count day|:count days', $hours / 24, ['count' => $hours / 24])
                    : trans_choice(':count hour|:count hours', $hours, ['count' => $hours]),
            ])
            ->all();
    }
}
