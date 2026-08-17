<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('price_rules', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('scope_type', 20)->default('global');
            $table->unsignedBigInteger('scope_id')->nullable();
            $table->string('operation_type', 20)->default('percent');
            $table->decimal('amount', 10, 2)->default(0);
            $table->string('rounding_type', 30)->default('none');
            $table->decimal('rounding_step', 10, 2)->nullable();
            $table->unsignedInteger('priority')->default(100);
            $table->boolean('is_active')->default(true);
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['scope_type', 'scope_id']);
            $table->index(['is_active', 'priority']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('price_rules');
    }
};
