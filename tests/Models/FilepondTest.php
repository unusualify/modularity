<?php

namespace Unusualify\Modularity\Tests\Models;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Support\Facades\Event;
use Modules\SystemNotification\Events\FilepondCreated;
use Modules\SystemNotification\Events\FilepondDeleted;
use Modules\SystemNotification\Events\FilepondUpdated;
use Unusualify\Modularity\Entities\Filepond;
use Unusualify\Modularity\Entities\User;
use Unusualify\Modularity\Tests\ModelTestCase;

class FilepondTest extends ModelTestCase
{
    use RefreshDatabase;

    public function test_get_table_filepond()
    {
        $filepond = new Filepond;
        $this->assertEquals(modularityConfig('tables.fileponds', 'um_fileponds'), $filepond->getTable());
    }

    public function test_fillable_attributes()
    {
        $expectedFillable = [
            'uuid',
            'file_name',
            'filepondable_id',
            'filepondable_type',
            'role',
            'locale',
        ];

        $filepond = new Filepond;
        $this->assertEquals($expectedFillable, $filepond->getFillable());
    }

    public function test_create_filepond()
    {
        Event::fake([
            FilepondCreated::class,
        ]);

        $user = User::factory()->create();

        $filepond = Filepond::create([
            'uuid' => 'test-uuid-123',
            'file_name' => 'test-file.jpg',
            'filepondable_id' => $user->id,
            'filepondable_type' => get_class($user),
            'role' => 'avatar',
            'locale' => 'en',
        ]);

        $this->assertEquals('test-uuid-123', $filepond->uuid);
        $this->assertEquals('test-file.jpg', $filepond->file_name);
        $this->assertEquals($user->id, $filepond->filepondable_id);
        $this->assertEquals(get_class($user), $filepond->filepondable_type);
        $this->assertEquals('avatar', $filepond->role);
        $this->assertEquals('en', $filepond->locale);

        Event::assertDispatched(FilepondCreated::class);
    }

    public function test_update_filepond()
    {
        Event::fake([
            FilepondCreated::class,
            FilepondUpdated::class,
        ]);

        $user = User::factory()->create();

        $filepond = Filepond::create([
            'uuid' => 'original-uuid-123',
            'file_name' => 'original-file.jpg',
            'filepondable_id' => $user->id,
            'filepondable_type' => get_class($user),
            'role' => 'original',
            'locale' => 'en',
        ]);

        $filepond->update([
            'uuid' => 'updated-uuid-456',
            'file_name' => 'updated-file.jpg',
            'role' => 'updated',
            'locale' => 'tr',
        ]);

        $this->assertEquals('updated-uuid-456', $filepond->uuid);
        $this->assertEquals('updated-file.jpg', $filepond->file_name);
        $this->assertEquals('updated', $filepond->role);
        $this->assertEquals('tr', $filepond->locale);

        Event::assertDispatched(FilepondCreated::class);
        Event::assertDispatched(FilepondUpdated::class);
    }

    public function test_delete_filepond()
    {
        Event::fake([
            FilepondDeleted::class,
        ]);

        $user = User::factory()->create();

        $filepond1 = Filepond::create([
            'uuid' => 'filepond-1-uuid',
            'file_name' => 'filepond-1.jpg',
            'filepondable_id' => $user->id,
            'filepondable_type' => get_class($user),
            'role' => 'test',
            'locale' => 'en',
        ]);

        $filepond2 = Filepond::create([
            'uuid' => 'filepond-2-uuid',
            'file_name' => 'filepond-2.jpg',
            'filepondable_id' => $user->id,
            'filepondable_type' => get_class($user),
            'role' => 'test',
            'locale' => 'en',
        ]);

        $this->assertCount(2, Filepond::all());

        $filepond2->delete();

        $this->assertFalse(Filepond::all()->contains('id', $filepond2->id));
        $this->assertTrue(Filepond::all()->contains('id', $filepond1->id));
        $this->assertCount(1, Filepond::all());

        Event::assertDispatched(FilepondDeleted::class);
    }

    public function test_filepondable_relationship()
    {
        $user = User::factory()->create();

        $filepond = Filepond::create([
            'uuid' => 'relationship-test-uuid',
            'file_name' => 'relationship-test.jpg',
            'filepondable_id' => $user->id,
            'filepondable_type' => get_class($user),
            'role' => 'test',
            'locale' => 'en',
        ]);

        $relation = $filepond->filepondable();
        $this->assertInstanceOf(MorphTo::class, $relation);
        $this->assertInstanceOf(User::class, $filepond->filepondable);
        $this->assertEquals($user->id, $filepond->filepondable->id);
    }

    public function test_mediable_format()
    {
        $user = User::factory()->create();

        $filepond = Filepond::create([
            'uuid' => 'format-test-uuid',
            'file_name' => 'format-test.jpg',
            'filepondable_id' => $user->id,
            'filepondable_type' => get_class($user),
            'role' => 'test',
            'locale' => 'en',
        ]);

        $format = $filepond->mediableFormat();

        $this->assertIsArray($format);
        $this->assertArrayHasKey('uuid', $format);
        $this->assertArrayHasKey('file_name', $format);
        $this->assertArrayHasKey('source', $format);
        $this->assertEquals($filepond->uuid, $format['uuid']);
        $this->assertEquals('format-test.jpg', $format['file_name']);
    }

    public function test_boot_events()
    {
        Event::fake([
            FilepondCreated::class,
            FilepondUpdated::class,
            FilepondDeleted::class,
        ]);

        $user = User::factory()->create();

        // Test created event
        $filepond = Filepond::create([
            'uuid' => 'event-test-uuid',
            'file_name' => 'event-test.jpg',
            'filepondable_id' => $user->id,
            'filepondable_type' => get_class($user),
            'role' => 'test',
            'locale' => 'en',
        ]);

        Event::assertDispatched(FilepondCreated::class, function ($event) use ($filepond) {
            return $event->model->id === $filepond->id;
        });

        // Test updated event
        $filepond->update(['file_name' => 'updated-event-test.jpg']);

        Event::assertDispatched(FilepondUpdated::class, function ($event) use ($filepond) {
            return $event->model->id === $filepond->id;
        });

        // Test deleted event
        $filepond->delete();

        Event::assertDispatched(FilepondDeleted::class, function ($event) use ($filepond) {
            return $event->model->id === $filepond->id;
        });
    }

    public function test_has_timestamps()
    {
        $user = User::factory()->create();

        $filepond = Filepond::create([
            'uuid' => 'timestamp-test-uuid',
            'file_name' => 'timestamp-test.jpg',
            'filepondable_id' => $user->id,
            'filepondable_type' => get_class($user),
            'role' => 'test',
            'locale' => 'en',
        ]);

        $this->assertTrue($filepond->timestamps);
        $this->assertNotNull($filepond->created_at);
        $this->assertNotNull($filepond->updated_at);
    }

    public function test_extends_model()
    {
        $filepond = new Filepond;
        $this->assertInstanceOf(\Unusualify\Modularity\Entities\Model::class, $filepond);
    }
}
