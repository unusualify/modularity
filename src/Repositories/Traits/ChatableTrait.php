<?php

namespace Unusualify\Modularous\Repositories\Traits;

use Unusualify\Modularous\Entities\User;
use Unusualify\Modularous\Models\Model;

trait ChatableTrait
{
    public bool $shouldUseDefaultChatableFormAction = false;

    public bool $shouldUseDefaultChatableInput = false;

    /**
     * @param Model $object
     * @param array $fields
     * @param array $schema
     * @return array
     */
    public function getFormFieldsChatableTrait($object, $fields, $schema = [])
    {
        // set, cast, unset or manipulate the fields by using object, fields and schema
        if (isset($schema['authorized_id']) && $object->authorization_record_exists) {
            $fields['authorized_id'] = $object->authorizationRecord->authorized_id;
            $fields['authorized_type'] = $object->authorizationRecord->authorized_type;
            if (! in_array('Unusualify\Modularous\Entities\Traits\HasUuid', class_uses_recursive($fields['authorized_type']))) {
                $fields['authorized_id'] = intval($fields['authorized_id']);
            }
        }

        return $fields;
    }

    public function addAttributesToChatableFormAction(): array
    {
        return [];
    }

    public function addAttributesToChatableFormActionInput(): array
    {
        return [];
    }

    public function addAttributesToChatableInput(): array
    {
        return [];
    }

    public function getFormActionsChatableTrait(?User $user, $scope = null): array
    {
        if (! $this->shouldUseDefaultChatableFormAction) {
            return [];
        }

        return [
            'chat' => array_merge([
                'type' => 'modal',
                'label' => __('messages.chatable.form-action.input.label'),
                'tooltip' => __('messages.chatable.form-action.input.tooltip'),
                'forceLabel' => true,
                'allowedRoles' => ['superadmin', 'admin', 'manager', 'client-manager', 'client-assistant'],
                // 'badge' => '$unread_chat_messages_for_you_count',
                'class' => 'full-width-action',  // Custom class for styling
                'formAttributes' => [
                    'hasSubmit' => false,
                    'hasDivider' => false,
                    'rowAttribute' => [
                        'noGutters' => true,
                        'class' => '',
                    ],
                ],
                'schema' => [
                    array_merge([
                        'type' => 'chat',
                        'noDivider' => true,
                        'noTab' => true, //  use tab to insert a tab character in message box
                        'noEmoji' => true,
                        'subtitle' => __('messages.chatable.form-action.input.subtitle'),
                        'bodyHeight' => '54vh',
                        'creatable' => true,
                        'label' => __('messages.chatable.form-action.input.label'),
                        'col' => ['cols' => 12],
                        'perPage' => 5,
                        'acceptedExtensions' => ['pdf', 'doc', 'docx', 'jpeg', 'jpg', 'png'],
                        // 'noLinkify' => true,
                        // 'conditions' => [
                        //     ['state.code', 'not in', ['draft', 'rejected']],
                        // ],
                        // 'noSubmit' => true,
                        // 'name' => 'chat',
                        // 'allowedRoles' => ['admin', 'manager', 'editor', 'client-manager', 'client-assistant'],
                    ], $this->addAttributesToChatableFormActionInput()),
                ],
            ], $this->addAttributesToChatableFormAction()),
        ];
    }

    public function getAppendFormSchemaChatableTrait($scope = null): array
    {
        if (! $this->shouldUseDefaultChatableInput) {
            return [];
        }

        return [
            array_merge([
                'type' => 'chat',
                'label' => __('messages.chatable.input.label'),
                'col' => ['cols' => 12],
                'creatable' => false,
                'perPage' => 10,
                'height' => '40vh',
                'bodyHeight' => '25vh',
                'disabledOnCondition' => true,
                'noTab' => true,
                'noEmoji' => true,
                // 'noLinkify' => true,
                // 'conditions' => [
                //     ['state.code', 'in', ['in-progress'], 'and'],
                // ],
            ], $this->addAttributesToChatableInput()),
        ];
    }
}
