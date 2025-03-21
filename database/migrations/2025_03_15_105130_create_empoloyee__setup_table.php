<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('employee_setup', function (Blueprint $table) {
            $table->id();
            $table->string('company_uid');
            $table->unsignedBigInteger('employee_id');
            $table->date('due_date');
            $table->decimal('salary', 10, 2);
            $table->string('remarks')->nullable();
            $table->boolean('status')->default(true);
            $table->timestamps();

            $table->foreign('employee_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('company_uid')->references('company_uid')->on('company_settings')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('employee_setup');
    }
};
