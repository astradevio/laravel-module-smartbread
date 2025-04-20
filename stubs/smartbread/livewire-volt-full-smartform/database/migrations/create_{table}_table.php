<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('{table}', function (Blueprint $table) {

            $table->uuid('id')->primary();
            $table->string('name')->index();
            $table->timestampsTz();
            $table->softDeletesTz();

            /**
             * other examples: see https://laravel.com/docs/12.x/migrations form more information
             *
             *
             * $table->string('field')->index()->nullable();
             * $table->autoIncrement();
             * ->comment('my comment')
             * ->default($value)
             * decimal
             * double
             * integer
             * char
             * ipAddress
             * macAddress
             * enum
             * dateTime
             * dateTimeTz
             * date
             * time
             * timeTz
             * json
             * text
             * uuidMorphs
             * nullableUuidMorphs
             */
                        
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('{table}');
    }
};
