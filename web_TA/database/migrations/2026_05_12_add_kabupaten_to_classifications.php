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
        Schema::table('classifications', function (Blueprint $table) {
            $table->string('kabupaten')->nullable()->after('location_address');
            $table->string('kecamatan')->nullable()->after('kabupaten');
            $table->string('kelurahan')->nullable()->after('kecamatan');
        });

        Schema::table('classification_histories', function (Blueprint $table) {
            $table->string('kabupaten')->nullable()->after('location_address');
            $table->string('kecamatan')->nullable()->after('kabupaten');
            $table->string('kelurahan')->nullable()->after('kecamatan');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('classifications', function (Blueprint $table) {
            $table->dropColumn(['kabupaten', 'kecamatan', 'kelurahan']);
        });

        Schema::table('classification_histories', function (Blueprint $table) {
            $table->dropColumn(['kabupaten', 'kecamatan', 'kelurahan']);
        });
    }
};
