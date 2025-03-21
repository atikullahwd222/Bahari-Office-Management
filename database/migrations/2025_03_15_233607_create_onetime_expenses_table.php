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
        Schema::create('onetime_expenses', function (Blueprint $table) {
            $table->id();
            $table->string('company_uid');
            $table->string('purpose');
            $table->string('pay_to');
            $table->decimal('amount', 10, 2);
            $table->date('date');
            $table->text('description')->nullable();
            $table->enum('payment_status', ['pending', 'paid', 'cancelled'])->default('pending');
            $table->timestamps();

            $table->foreign('company_uid')
                ->references('company_uid')
                ->on('company_settings')
                ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('onetime_expenses');
    }
};
