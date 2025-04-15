<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('todo', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->string('title')->required;
            $table->string('assignee');
            $table->date('due_date');
            $table->decimal('time_tracked');
            $table->enum('status', ['pending', 'open', 'in_progress', 'completed']); 
            $table->enum('priority', ['low', 'medium', 'high']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('todo');
    }
};
