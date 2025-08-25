<?php

namespace Unusualify\Modularity\Tests\Models;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Unusualify\Modularity\Entities\State;
use Unusualify\Modularity\Entities\User;
use Unusualify\Modularity\Tests\ModelTestCase;

class StateTest extends ModelTestCase
{
    use RefreshDatabase;

    public function test_get_table_state()
    {
        $state = new State;
        $this->assertEquals(modularityConfig('tables.states', 'um_states'), $state->getTable());
    }

    public function test_fillable_attributes()
    {
        $expectedFillable = [
            'published',
            'code',
            'icon',
            'color',
        ];

        $state = new State;
        $this->assertEquals($expectedFillable, $state->getFillable());
    }

    public function test_translated_attributes()
    {
        $expectedTranslatedAttributes = [
            'name',
            'active',
        ];

        $state = new State;
        $this->assertEquals($expectedTranslatedAttributes, $state->translatedAttributes);
    }

    public function test_create_state()
    {
        $state = State::create([
            'published' => 1,
            'code' => 'TEST_STATE',
            'icon' => 'mdi-test',
            'color' => '#FF5722',
        ]);

        $this->assertEquals(1, $state->published);
        $this->assertEquals('TEST_STATE', $state->code);
        $this->assertEquals('mdi-test', $state->icon);
        $this->assertEquals('#FF5722', $state->color);
    }

    public function test_update_state()
    {
        $state = State::create([
            'published' => 1,
            'code' => 'INITIAL_STATE',
            'icon' => 'mdi-initial',
            'color' => '#2196F3',
        ]);

        $state->update([
            'published' => 0,
            'code' => 'UPDATED_STATE',
            'icon' => 'mdi-updated',
            'color' => '#4CAF50',
        ]);

        $this->assertEquals(0, $state->published);
        $this->assertEquals('UPDATED_STATE', $state->code);
        $this->assertEquals('mdi-updated', $state->icon);
        $this->assertEquals('#4CAF50', $state->color);
    }

    public function test_delete_state()
    {
        $state1 = State::create([
            'published' => 1,
            'code' => 'STATE_1',
            'icon' => 'mdi-one',
            'color' => '#FF5722',
        ]);

        $state2 = State::create([
            'published' => 1,
            'code' => 'STATE_2',
            'icon' => 'mdi-two',
            'color' => '#2196F3',
        ]);

        $this->assertCount(2, State::all());

        $state2->delete();

        $this->assertFalse(State::all()->contains('id', $state2->id));
        $this->assertTrue(State::all()->contains('id', $state1->id));
        $this->assertCount(1, State::all());
    }

    public function test_has_translation_trait()
    {
        $this->assertTrue(in_array(
            \Unusualify\Modularity\Entities\Traits\HasTranslation::class,
            class_uses_recursive(new State)
        ));
    }

    public function test_translation_model_name()
    {
        $state = new State;
        $translationModelName = $state->getTranslationModelNameDefault();

        $this->assertStringContainsString('StateTranslation', $translationModelName);
    }

    public function test_state_with_translations()
    {
        app()->config->set('translatable.locales', ['en', 'tr']);

        $state = State::create([
            'published' => 1,
            'code' => 'TRANSLATED_STATE',
            'icon' => 'mdi-translate',
            'color' => '#9C27B0',
            'en' => [
                'name' => 'English State Name',
                'active' => 1,
            ],
            'tr' => [
                'name' => 'Turkish State Name',
                'active' => 1,
            ],
        ]);

        $this->assertEquals('English State Name', $state->translate('en')->name ?? null);
        $this->assertEquals('Turkish State Name', $state->translate('tr')->name ?? null);
    }

    public function test_state_with_has_stateable_integration()
    {
        app()->config->set('translatable.locales', ['en', 'tr']);

        // Test state creation that would be used with HasStateable trait
        $state = State::create([
            'published' => 1,
            'code' => 'in-review',
            'icon' => 'mdi-information-outline',
            'color' => 'info',
            'en' => [
                'name' => 'In Review',
                'active' => 1,
            ],
            'tr' => [
                'name' => 'İnceleniyor',
                'active' => 1,
            ],
        ]);

        $this->assertEquals('in-review', $state->code);
        $this->assertEquals('mdi-information-outline', $state->icon);
        $this->assertEquals('info', $state->color);
        $this->assertEquals('In Review', $state->translate('en')->name ?? null);
        $this->assertEquals('İnceleniyor', $state->translate('tr')->name ?? null);
    }

    public function test_state_codes_for_default_states()
    {
        // Test typical state codes used in HasStateable models
        $stateCodes = [
            'draft' => ['icon' => 'mdi-file-document-outline', 'color' => 'warning'],
            'in-review' => ['icon' => 'mdi-information-outline', 'color' => 'info'],
            'approved' => ['icon' => 'mdi-check-circle-outline', 'color' => 'success'],
            'rejected' => ['icon' => 'mdi-close-circle-outline', 'color' => 'error'],
            'cancelled' => ['icon' => 'mdi-cancel', 'color' => 'error'],
        ];

        $createdStates = [];
        foreach ($stateCodes as $code => $attributes) {
            $state = State::create([
                'published' => 1,
                'code' => $code,
                'icon' => $attributes['icon'],
                'color' => $attributes['color'],
            ]);
            $createdStates[] = $state;
        }

        $this->assertCount(5, $createdStates);

        // Verify each state was created correctly
        foreach ($createdStates as $state) {
            $this->assertContains($state->code, array_keys($stateCodes));
            $this->assertEquals($stateCodes[$state->code]['icon'], $state->icon);
            $this->assertEquals($stateCodes[$state->code]['color'], $state->color);
        }
    }

    public function test_state_translation_deletion()
    {
        Event::fake();

        $state = State::create([
            'published' => 1,
            'code' => 'TEST_DELETION',
            'icon' => 'mdi-test',
            'color' => 'info',
            'en' => [
                'name' => 'Test Deletion',
                'active' => 1,
            ],
        ]);

        $this->assertTrue($state->hasTranslation('en'));

        // Test that translations are deleted when state is deleted (via HasTranslation trait)
        $state->delete();

        $this->assertDatabaseMissing(modularityConfig('tables.states', 'um_states'), ['id' => $state->id]);
    }

    public function test_state_active_translation_check()
    {
        $state = State::create([
            'published' => 1,
            'code' => 'ACTIVE_CHECK',
            'icon' => 'mdi-check',
            'color' => 'success',
            'en' => [
                'name' => 'Active Check',
                'active' => 1,
            ],
            'tr' => [
                'name' => 'Aktif Kontrol',
                'active' => 0, // Inactive translation
            ],
        ]);

        $this->assertTrue($state->hasActiveTranslation('en'));
        $this->assertFalse($state->hasActiveTranslation('tr'));
    }

    public function test_state_get_active_languages()
    {
        $state = State::create([
            'published' => 1,
            'code' => 'MULTI_LANG',
            'icon' => 'mdi-translate',
            'color' => 'primary',
            'en' => [
                'name' => 'Multi Language',
                'active' => 1,
            ],
            'tr' => [
                'name' => 'Çoklu Dil',
                'active' => 1,
            ],
        ]);

        $activeLanguages = $state->getActiveLanguages();

        $this->assertIsArray($activeLanguages->toArray());
        $this->assertGreaterThan(0, $activeLanguages->count());

        // Check structure of active languages
        $firstLanguage = $activeLanguages->first();
        $this->assertArrayHasKey('shortlabel', $firstLanguage);
        $this->assertArrayHasKey('label', $firstLanguage);
        $this->assertArrayHasKey('value', $firstLanguage);
        $this->assertArrayHasKey('published', $firstLanguage);
    }

    public function test_state_translated_attribute()
    {
        app()->config->set('translatable.locales', ['en', 'tr']);

        $state = State::create([
            'published' => 1,
            'code' => 'TRANSLATED_ATTR',
            'icon' => 'mdi-attribute',
            'color' => 'secondary',
            'en' => [
                'name' => 'English Name',
                'active' => 1,
            ],
            'tr' => [
                'name' => 'Turkish Name',
                'active' => 1,
            ],
        ]);

        $translatedNames = $state->translatedAttribute('name');

        $this->assertInstanceOf(\Illuminate\Support\Collection::class, $translatedNames);
        $this->assertEquals('English Name', $translatedNames->get('en'));
        $this->assertEquals('Turkish Name', $translatedNames->get('tr'));
    }

    public function test_state_scope_with_active_translations()
    {
        // Create states with active and inactive translations
        State::create([
            'published' => 1,
            'code' => 'ACTIVE_STATE',
            'icon' => 'mdi-check',
            'color' => 'success',
            'en' => [
                'name' => 'Active State',
                'active' => 1,
            ],
        ]);

        State::create([
            'published' => 1,
            'code' => 'INACTIVE_STATE',
            'icon' => 'mdi-close',
            'color' => 'error',
            'en' => [
                'name' => 'Inactive State',
                'active' => 1,
            ],
        ]);

        $activeStates = State::withActiveTranslations('en')->get();

        $this->assertGreaterThan(0, $activeStates->count());

        // Verify that only states with active translations are returned
        foreach ($activeStates as $state) {
            $this->assertTrue($state->hasActiveTranslation('en'));
        }
    }

    public function test_state_extends_base_model()
    {
        $state = new State;
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Model::class, $state);
    }

    public function test_has_timestamps()
    {
        $state = State::create([
            'published' => 1,
            'code' => 'TIMESTAMP_STATE',
            'color' => 'success',
            'icon' => 'mdi-check',
        ]);

        $this->assertTrue($state->timestamps);
        $this->assertNotNull($state->created_at);
        $this->assertNotNull($state->updated_at);
    }
}
