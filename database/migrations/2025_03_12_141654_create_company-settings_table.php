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
        Schema::create('company_settings', function (Blueprint $table) {
            $table->id();
            $table->string('company_name')->default('BahariHost');
            $table->string('company_email')->default('support@baharihost.com');
            $table->string('company_phone')->default('+8801726708442');
            $table->string('company_address')->default('Nobodoy, Bangladesh');
            $table->string('company_city')->default('Dhaka');
            $table->string('company_state')->default('Dhaka');
            $table->string('company_logo')->default('assets/img/company_logo/logo.png');
            $table->string('company_favicon')->default('assets/img/company_favicon/favicon.ico');
            $table->string('company_website')->default('https://baharihost.com');
            $table->string('company_facebook')->default('https://facebook.com/baharihost');
            $table->uuid('company_uid')->unique()->index();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('company_settings');
    }
};
