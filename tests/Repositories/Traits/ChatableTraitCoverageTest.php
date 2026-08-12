<?php

declare(strict_types=1);

namespace Unusualify\Modularous\Tests\Repositories\Traits;

use Unusualify\Modularous\Entities\User;
use Unusualify\Modularous\Repositories\Traits\ChatableTrait;
use Unusualify\Modularous\Tests\TestCase;

class ChatableTraitCoverageTest extends TestCase
{
    /** @test */
    public function form_fields_casts_authorized_id_without_uuid_trait(): void
    {
        $repo = new Phase5ChatableRepository;
        $object = new class
        {
            public bool $authorization_record_exists = true;

            public object $authorizationRecord;

            public function __construct()
            {
                $this->authorizationRecord = (object) [
                    'authorized_id' => '15',
                    'authorized_type' => \stdClass::class,
                ];
            }
        };

        $fields = $repo->getFormFieldsChatableTrait($object, [], [
            'authorized_id' => true,
        ]);

        $this->assertSame(15, $fields['authorized_id']);
        $this->assertSame(\stdClass::class, $fields['authorized_type']);
    }

    /** @test */
    public function form_fields_keeps_uuid_authorized_id_as_string(): void
    {
        $uuidType = null;
        foreach ([
            \Unusualify\Modularous\Entities\User::class,
            \Unusualify\Modularous\Entities\Company::class,
        ] as $candidate) {
            if (in_array(
                \Unusualify\Modularous\Entities\Traits\HasUuid::class,
                class_uses_recursive($candidate),
                true
            )) {
                $uuidType = $candidate;
                break;
            }
        }

        if ($uuidType === null) {
            $this->markTestSkipped('No HasUuid model available for authorized_type branch');
        }

        $repo = new Phase5ChatableRepository;
        $object = new class($uuidType)
        {
            public bool $authorization_record_exists = true;

            public object $authorizationRecord;

            public function __construct(string $type)
            {
                $this->authorizationRecord = (object) [
                    'authorized_id' => 'uuid-1',
                    'authorized_type' => $type,
                ];
            }
        };

        $fields = $repo->getFormFieldsChatableTrait($object, ['keep' => 1], [
            'authorized_id' => true,
        ]);

        $this->assertSame('uuid-1', $fields['authorized_id']);
        $this->assertSame(1, $fields['keep']);
    }

    /** @test */
    public function form_fields_skips_when_authorization_missing(): void
    {
        $repo = new Phase5ChatableRepository;
        $object = new class
        {
            public bool $authorization_record_exists = false;
        };

        $this->assertSame(
            ['a' => 1],
            $repo->getFormFieldsChatableTrait($object, ['a' => 1], ['authorized_id' => true])
        );
    }

    /** @test */
    public function attribute_helpers_and_default_form_action_schema(): void
    {
        $repo = new Phase5ChatableRepository;
        $this->assertSame([], $repo->addAttributesToChatableFormAction());
        $this->assertSame([], $repo->addAttributesToChatableFormActionInput());
        $this->assertSame([], $repo->addAttributesToChatableInput());
        $this->assertSame([], $repo->getFormActionsChatableTrait(null));
        $this->assertSame([], $repo->getAppendFormSchemaChatableTrait());

        $repo->shouldUseDefaultChatableFormAction = true;
        $repo->shouldUseDefaultChatableInput = true;

        $actions = $repo->getFormActionsChatableTrait(new User);
        $this->assertArrayHasKey('chat', $actions);
        $this->assertSame('modal', $actions['chat']['type']);
        $this->assertSame('chat', $actions['chat']['schema'][0]['type']);

        $schema = $repo->getAppendFormSchemaChatableTrait();
        $this->assertSame('chat', $schema[0]['type']);
        $this->assertFalse($schema[0]['creatable']);
    }
}

class Phase5ChatableRepository
{
    use ChatableTrait;
}
