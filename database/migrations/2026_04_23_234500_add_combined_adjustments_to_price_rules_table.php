<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('price_rules', function (Blueprint $table) {
            $table->decimal('percent_adjustment', 10, 2)->default(0)->after('amount');
            $table->decimal('fixed_adjustment', 10, 2)->default(0)->after('percent_adjustment');
            $table->decimal('override_price', 10, 2)->nullable()->after('fixed_adjustment');
        });

        DB::table('price_rules')
            ->where('operation_type', 'percent')
            ->update(['percent_adjustment' => DB::raw('amount')]);

        DB::table('price_rules')
            ->where('operation_type', 'fixed')
            ->update(['fixed_adjustment' => DB::raw('amount')]);

        DB::table('price_rules')
            ->where('operation_type', 'override')
            ->update(['override_price' => DB::raw('amount')]);
    }

    public function down(): void
    {
        Schema::table('price_rules', function (Blueprint $table) {
            $table->dropColumn([
                'percent_adjustment',
                'fixed_adjustment',
                'override_price',
            ]);
        });
    }
};
