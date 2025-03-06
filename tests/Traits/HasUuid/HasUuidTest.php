<?php

namespace Unusualify\Modularity\Tests\Traits\HasUuid;

use Unusualify\Modularity\Tests\TestCase;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Foundation\Testing\RefreshDatabase;

class HasUuidTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();



        Schema::create('uuid_models', function (Blueprint $table) {
            $table->uuid('id')->primary();
        });
        Schema::create('uuid_model_integer_id_exceptions', function (Blueprint $table) {
            $table->unsignedBigInteger('uuid');
        });

        Schema::create('uuid_model_column_not_exist_exceptions', function (Blueprint $table) {
            $table->uuid('name');
        });

    }

    /** @test */
    public function uuid_column_type_exception()
    {

        $this->expectExceptionMessage('Column "uuid" is not proper, because the column type is "integer" in Unusualify\Modularity\Tests\Traits\HasUuid\UuidModelIntegerIdException');

        new UuidModelIntegerIdException();

    }

    /** @test */
    public function uuid_column_not_exist_exception(){

        $this->expectExceptionMessage('Column uuid not found in Unusualify\Modularity\Tests\Traits\HasUuid\UuidColumnNotExistException');

        new UuidColumnNotExistException();
    }


}
