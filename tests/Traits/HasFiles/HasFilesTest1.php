<?php

namespace Unusualify\Modularity\Tests\Traits\HasFiles;

use Unusualify\Modularity\Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Unusualify\Modularity\Entities\Traits\HasFiles;


class HasFilesTest extends TestCase {

    use RefreshDatabase;



    protected function setup(): void
    {

        parent::setup();

        //dd(Schema::getAllTables());
            $filesTable = 'file_models';
            $fileablesTable = 'fileable_models';

            Schema::create($filesTable, function (Blueprint $table) {
                $table->{modularityIncrementsMethod()}('id');
                $table->timestamps();
                $table->softDeletes();
                $table->text('uuid');
                $table->text('filename')->nullable();
                $table->integer('size')->unsigned();
            });

            Schema::create($fileablesTable, function (Blueprint $table) use ($filesTable) {
                $table->{modularityIncrementsMethod()}('id');
                $table->timestamps();
                $table->softDeletes();
                $table->{modularityIntegerMethod()}('file_model_id')->unsigned();
                $table->foreign('file_model_id', 'fk_files_file_model_id')->references('id')->on($filesTable)->onDelete('cascade')->onUpdate('cascade');
                $table->uuidMorphs('fileable');
                $table->string('role')->nullable();
                $table->string('locale', 6)->index();
            });

            Schema::create('posts', function (Blueprint $table) {
                $table->id();
                $table->string('title');
                $table->timestamps();

            });

            $files = [
                [
                   'uuid' => '1234-5678',
                    'filename' => 'example.jpg',
                    'size' => 1024,
                ],
                [
                    'uuid' => '1234-5679',
                    'filename' => 'example2.jpg',
                    'size' => 512,
                ],
            ];

            foreach ($files as $file) {
                FileModel::create($file);
            }

            Post::create(['title' => 'Test Post']);

    }


    /** @test */


    public function a_model_can_attach_and_retrieve_a_file()
    {

        $post = Post::find(1);
        $file = FileModel::find(1);

        $post->files()->attach($file->id, ['role' => 'thumbnail', 'locale' => 'en']);
        $this->assertEquals(1, $post->files()->count());

        // Check if file() retrieves the correct URL
        $expectedUrl = \Unusualify\Modularity\Services\FileLibrary\FileService::getUrl('1234-5678');

        $this->assertEquals($expectedUrl, $post->file('thumbnail', 'en'));

        // Check if fileObject() retrieves the correct file model
        $retrievedFile = $post->fileObject('thumbnail', 'en');
        $this->assertNotNull($retrievedFile);
        $this->assertEquals('example.jpg', $retrievedFile->filename);
    }



    /** @test */
    public function it_returns_all_files_for_a_given_role()
    {
        $post = Post::find(1);

        $file1 = FileModel::find(1);
        $file2 = FileModel::find(2);

        $post->files()->attach($file1->id, ['role' => 'gallery', 'locale' => 'en']);
        $post->files()->attach($file2->id, ['role' => 'gallery', 'locale' => 'en']);
        //dd($post->files()->wherePivot('role', 'gallery')->get()->toArray());

        $urls = $post->filesList('gallery', 'en');

        $this->assertCount(2, $urls);
        $this->assertEquals(
            \Unusualify\Modularity\Services\FileLibrary\FileService::getUrl('1234'),
            $urls[0]
        );
        $this->assertEquals(
            \Unusualify\Modularity\Services\FileLibrary\FileService::getUrl('5678'),
            $urls[1]
        );
    }


    /** @test */

    public function it_respects_locale_and_fallback_locale()
    {
        config(['translatable.use_property_fallback' => true]);
        config(['translatable.fallback_locale' => 'fr']);

        $post = Post::find(1);

        $fileFr = FileModel::create(['uuid' => '1111', 'filename' => 'file-fr.jpg', 'size' => 500]);
        $post->files()->attach($fileFr->id, ['role' => 'cover', 'locale' => 'fr']);

        // Requesting in a non-existent locale (e.g., 'de') should fallback to 'fr'
        $retrievedFile = $post->fileObject('cover', 'de');

        $this->assertNotNull($retrievedFile);
        $this->assertEquals('file-fr.jpg', $retrievedFile->filename);
    }

}



class Post extends Model {

    use HasFiles;

    protected $fillable = ['title'];

}

