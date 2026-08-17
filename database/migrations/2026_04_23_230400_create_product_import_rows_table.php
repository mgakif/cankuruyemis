<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('product_import_rows', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_import_batch_id')->constrained()->cascadeOnDelete();
            $table->unsignedInteger('row_number');
            $table->string('barcode')->nullable();
            $table->string('stock_name')->nullable();
            $table->foreignId('product_id')->nullable()->constrained()->nullOnDelete();
            $table->string('action', 20)->default('skip');
            $table->string('status', 20)->default('pending');
            $table->text('message')->nullable();
            $table->json('raw_payload')->nullable();
            $table->timestamps();

            $table->index(['product_import_batch_id', 'row_number']);
            $table->index(['barcode', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('product_import_rows');
    }
};
