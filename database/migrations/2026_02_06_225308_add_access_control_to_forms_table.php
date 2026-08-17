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
        if (! Schema::hasTable('forms')) {
            return;
        }

        Schema::table('forms', function (Blueprint $table) {
            if (!Schema::hasColumn('forms', 'requires_token')) {
                $table->boolean('requires_token')->default(false)->after('recaptcha');
            }
            if (!Schema::hasColumn('forms', 'access_token')) {
                $table->string('access_token')->nullable()->after('requires_token');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (! Schema::hasTable('forms')) {
            return;
        }

        Schema::table('forms', function (Blueprint $table) {
            if (Schema::hasColumn('forms', 'requires_token')) {
                $table->dropColumn('requires_token');
            }
            if (Schema::hasColumn('forms', 'access_token')) {
                $table->dropColumn('access_token');
            }
        });
    }
};
