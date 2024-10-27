<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateVectorsTable extends Migration
{
    public function up()
    {
        Schema::create('vectors', function (Blueprint $table) {
            $table->id();
            $table->string('file_name');
            $table->json('embedding'); // Para armazenar os vetores como JSON
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('vectors');
    }
}
