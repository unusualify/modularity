<?php

namespace Unusualify\Modularity\Tests\Models;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Unusualify\Modularity\Entities\TemporaryFilepond;
use Unusualify\Modularity\Tests\ModelTestCase;

class TemporaryFilepondTest extends ModelTestCase
{
    use RefreshDatabase;

    public function test_get_table_temporary_filepond()
    {
        $temporaryFilepond = new TemporaryFilepond;
        $this->assertEquals(
            modularityConfig('tables.filepond_temporaries', 'temporary_fileponds'),
            $temporaryFilepond->getTable()
        );
    }

    public function test_fillable_attributes()
    {
        $expectedFillable = [
            'file_name',
            'input_role',
            'folder_name',
        ];

        $temporaryFilepond = new TemporaryFilepond;
        $this->assertEquals($expectedFillable, $temporaryFilepond->getFillable());
    }

    public function test_create_temporary_filepond()
    {
        $temporaryFilepond = TemporaryFilepond::create([
            'file_name' => 'test-image.jpg',
            'input_role' => 'avatar',
            'folder_name' => 'temp_uploads_123',
        ]);

        $this->assertEquals('test-image.jpg', $temporaryFilepond->file_name);
        $this->assertEquals('avatar', $temporaryFilepond->input_role);
        $this->assertEquals('temp_uploads_123', $temporaryFilepond->folder_name);
    }

    public function test_update_temporary_filepond()
    {
        $temporaryFilepond = TemporaryFilepond::create([
            'file_name' => 'original-file.jpg',
            'input_role' => 'document',
            'folder_name' => 'temp_folder_1',
        ]);

        $temporaryFilepond->update([
            'file_name' => 'updated-file.jpg',
            'input_role' => 'image',
            'folder_name' => 'temp_folder_2',
        ]);

        $this->assertEquals('updated-file.jpg', $temporaryFilepond->file_name);
        $this->assertEquals('image', $temporaryFilepond->input_role);
        $this->assertEquals('temp_folder_2', $temporaryFilepond->folder_name);
    }

    public function test_delete_temporary_filepond()
    {
        $temporaryFilepond1 = TemporaryFilepond::create([
            'file_name' => 'file1.jpg',
            'input_role' => 'avatar',
            'folder_name' => 'temp_1',
        ]);

        $temporaryFilepond2 = TemporaryFilepond::create([
            'file_name' => 'file2.jpg',
            'input_role' => 'document',
            'folder_name' => 'temp_2',
        ]);

        $this->assertCount(2, TemporaryFilepond::all());

        $temporaryFilepond2->delete();

        $this->assertFalse(TemporaryFilepond::all()->contains('id', $temporaryFilepond2->id));
        $this->assertTrue(TemporaryFilepond::all()->contains('id', $temporaryFilepond1->id));
        $this->assertCount(1, TemporaryFilepond::all());
    }

    public function test_extends_base_model()
    {
        $temporaryFilepond = new TemporaryFilepond;
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Model::class, $temporaryFilepond);
    }

    public function test_has_timestamps()
    {
        $temporaryFilepond = TemporaryFilepond::create([
            'file_name' => 'timestamp-test.jpg',
            'input_role' => 'test',
            'folder_name' => 'temp_test',
        ]);

        $this->assertTrue($temporaryFilepond->timestamps);
        $this->assertNotNull($temporaryFilepond->created_at);
        $this->assertNotNull($temporaryFilepond->updated_at);
    }

    public function test_temporary_filepond_different_roles()
    {
        $avatarFile = TemporaryFilepond::create([
            'file_name' => 'avatar.jpg',
            'input_role' => 'avatar',
            'folder_name' => 'avatars_temp',
        ]);

        $documentFile = TemporaryFilepond::create([
            'file_name' => 'document.pdf',
            'input_role' => 'document',
            'folder_name' => 'documents_temp',
        ]);

        $attachmentFile = TemporaryFilepond::create([
            'file_name' => 'attachment.zip',
            'input_role' => 'attachment',
            'folder_name' => 'attachments_temp',
        ]);

        $this->assertEquals('avatar', $avatarFile->input_role);
        $this->assertEquals('document', $documentFile->input_role);
        $this->assertEquals('attachment', $attachmentFile->input_role);
    }

    public function test_temporary_filepond_folder_organization()
    {
        $file1 = TemporaryFilepond::create([
            'file_name' => 'file1.jpg',
            'input_role' => 'image',
            'folder_name' => 'session_abc123',
        ]);

        $file2 = TemporaryFilepond::create([
            'file_name' => 'file2.jpg',
            'input_role' => 'image',
            'folder_name' => 'session_def456',
        ]);

        // Test that files can be organized by folder
        $sessionAbc123Files = TemporaryFilepond::where('folder_name', 'session_abc123')->get();
        $sessionDef456Files = TemporaryFilepond::where('folder_name', 'session_def456')->get();

        $this->assertCount(1, $sessionAbc123Files);
        $this->assertCount(1, $sessionDef456Files);
        $this->assertEquals($file1->id, $sessionAbc123Files->first()->id);
        $this->assertEquals($file2->id, $sessionDef456Files->first()->id);
    }

    public function test_temporary_filepond_file_types()
    {
        $imageFile = TemporaryFilepond::create([
            'file_name' => 'image.jpg',
            'input_role' => 'image',
            'folder_name' => 'temp_images',
        ]);

        $pdfFile = TemporaryFilepond::create([
            'file_name' => 'document.pdf',
            'input_role' => 'document',
            'folder_name' => 'temp_docs',
        ]);

        $videoFile = TemporaryFilepond::create([
            'file_name' => 'video.mp4',
            'input_role' => 'media',
            'folder_name' => 'temp_media',
        ]);

        // Test different file extensions
        $this->assertStringEndsWith('.jpg', $imageFile->file_name);
        $this->assertStringEndsWith('.pdf', $pdfFile->file_name);
        $this->assertStringEndsWith('.mp4', $videoFile->file_name);
    }

    public function test_query_by_input_role()
    {
        TemporaryFilepond::create([
            'file_name' => 'avatar1.jpg',
            'input_role' => 'avatar',
            'folder_name' => 'temp_1',
        ]);

        TemporaryFilepond::create([
            'file_name' => 'avatar2.jpg',
            'input_role' => 'avatar',
            'folder_name' => 'temp_2',
        ]);

        TemporaryFilepond::create([
            'file_name' => 'document1.pdf',
            'input_role' => 'document',
            'folder_name' => 'temp_3',
        ]);

        $avatarFiles = TemporaryFilepond::where('input_role', 'avatar')->get();
        $documentFiles = TemporaryFilepond::where('input_role', 'document')->get();

        $this->assertCount(2, $avatarFiles);
        $this->assertCount(1, $documentFiles);
    }

    public function test_temporary_filepond_cleanup_scenario()
    {
        // Simulate temporary files that might need cleanup
        $oldFile = TemporaryFilepond::create([
            'file_name' => 'old_temp_file.jpg',
            'input_role' => 'temp',
            'folder_name' => 'cleanup_test',
        ]);

        $recentFile = TemporaryFilepond::create([
            'file_name' => 'recent_temp_file.jpg',
            'input_role' => 'temp',
            'folder_name' => 'cleanup_test',
        ]);

        // Simulate cleanup by folder
        $filesToCleanup = TemporaryFilepond::where('folder_name', 'cleanup_test')->get();
        $this->assertCount(2, $filesToCleanup);

        // Simulate deletion of old files
        TemporaryFilepond::where('folder_name', 'cleanup_test')->delete();

        $remainingFiles = TemporaryFilepond::where('folder_name', 'cleanup_test')->get();
        $this->assertCount(0, $remainingFiles);
    }

    public function test_unique_folder_names()
    {
        $folder1Files = collect();
        $folder2Files = collect();

        // Create files in different folders
        for ($i = 1; $i <= 3; $i++) {
            $folder1Files->push(TemporaryFilepond::create([
                'file_name' => "file{$i}_folder1.jpg",
                'input_role' => 'test',
                'folder_name' => 'unique_folder_1',
            ]));

            $folder2Files->push(TemporaryFilepond::create([
                'file_name' => "file{$i}_folder2.jpg",
                'input_role' => 'test',
                'folder_name' => 'unique_folder_2',
            ]));
        }

        $folder1Results = TemporaryFilepond::where('folder_name', 'unique_folder_1')->get();
        $folder2Results = TemporaryFilepond::where('folder_name', 'unique_folder_2')->get();

        $this->assertCount(3, $folder1Results);
        $this->assertCount(3, $folder2Results);

        // Verify files are in correct folders
        foreach ($folder1Results as $file) {
            $this->assertEquals('unique_folder_1', $file->folder_name);
            $this->assertStringContainsString('folder1', $file->file_name);
        }

        foreach ($folder2Results as $file) {
            $this->assertEquals('unique_folder_2', $file->folder_name);
            $this->assertStringContainsString('folder2', $file->file_name);
        }
    }
}
