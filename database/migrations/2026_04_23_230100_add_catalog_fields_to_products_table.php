<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            if (! Schema::hasColumn('products', 'product_category_id')) {
                $table->foreignId('product_category_id')->nullable()->after('id')->constrained('product_categories')->nullOnDelete();
            }
            if (! Schema::hasColumn('products', 'barcode')) {
                $table->string('barcode')->nullable()->after('slug');
            }
            if (! Schema::hasColumn('products', 'brand')) {
                $table->string('brand')->nullable()->after('barcode');
            }
            if (! Schema::hasColumn('products', 'unit')) {
                $table->string('unit', 20)->nullable()->after('brand');
            }
            if (! Schema::hasColumn('products', 'package_size')) {
                $table->string('package_size')->nullable()->after('unit');
            }
            if (! Schema::hasColumn('products', 'store_price')) {
                $table->decimal('store_price', 10, 2)->default(0)->after('description');
            }
            if (! Schema::hasColumn('products', 'online_price')) {
                $table->decimal('online_price', 10, 2)->default(0)->after('store_price');
            }
            if (! Schema::hasColumn('products', 'stock_quantity')) {
                $table->decimal('stock_quantity', 12, 3)->default(0)->after('stock');
            }
            if (! Schema::hasColumn('products', 'sale_channel')) {
                $table->string('sale_channel', 20)->default('online')->after('stock_quantity');
            }
            if (! Schema::hasColumn('products', 'position')) {
                $table->unsignedInteger('position')->default(0)->after('sale_channel');
            }
            if (! Schema::hasColumn('products', 'summary')) {
                $table->text('summary')->nullable()->after('description');
            }
            if (! Schema::hasColumn('products', 'tags')) {
                $table->json('tags')->nullable()->after('summary');
            }
            if (! Schema::hasColumn('products', 'hashtags')) {
                $table->json('hashtags')->nullable()->after('tags');
            }
            if (! Schema::hasColumn('products', 'nutrition_facts')) {
                $table->json('nutrition_facts')->nullable()->after('technical_specs');
            }
            if (! Schema::hasColumn('products', 'energy_kcal')) {
                $table->decimal('energy_kcal', 8, 2)->nullable()->after('nutrition_facts');
            }
            if (! Schema::hasColumn('products', 'ingredients')) {
                $table->text('ingredients')->nullable()->after('energy_kcal');
            }
            if (! Schema::hasColumn('products', 'allergen_info')) {
                $table->text('allergen_info')->nullable()->after('ingredients');
            }
            if (! Schema::hasColumn('products', 'content_status')) {
                $table->string('content_status', 20)->default('draft')->after('allergen_info');
            }
            if (! Schema::hasColumn('products', 'last_imported_at')) {
                $table->timestamp('last_imported_at')->nullable()->after('content_status');
            }
        });

        if (! $this->indexExists('products', 'products_barcode_unique')) {
            Schema::table('products', function (Blueprint $table) {
                $table->unique('barcode');
            });
        }

        if (! $this->indexExists('products', 'products_sale_channel_is_published_index')) {
            Schema::table('products', function (Blueprint $table) {
                $table->index(['sale_channel', 'is_published']);
            });
        }

        if (! $this->indexExists('products', 'products_product_category_id_position_index')) {
            Schema::table('products', function (Blueprint $table) {
                $table->index(['product_category_id', 'position']);
            });
        }

        DB::table('products')
            ->whereNull('store_price')
            ->orWhere('store_price', 0)
            ->update([
                'store_price' => DB::raw('price'),
                'online_price' => DB::raw('price'),
                'stock_quantity' => DB::raw('stock'),
            ]);
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropConstrainedForeignId('product_category_id');
            $table->dropUnique('products_barcode_unique');
            $table->dropIndex('products_sale_channel_is_published_index');
            $table->dropIndex('products_product_category_id_position_index');
            $table->dropColumn([
                'barcode',
                'brand',
                'unit',
                'package_size',
                'store_price',
                'online_price',
                'stock_quantity',
                'sale_channel',
                'position',
                'summary',
                'tags',
                'hashtags',
                'nutrition_facts',
                'energy_kcal',
                'ingredients',
                'allergen_info',
                'content_status',
                'last_imported_at',
            ]);
        });
    }

    protected function indexExists(string $table, string $indexName): bool
    {
        if (DB::getDriverName() !== 'mysql') {
            if (method_exists(Schema::getFacadeRoot(), 'getIndexes')) {
                return collect(Schema::getIndexes($table))
                    ->contains(fn (array $index): bool => ($index['name'] ?? null) === $indexName);
            }

            return false;
        }

        $database = DB::getDatabaseName();

        return DB::table('information_schema.statistics')
            ->where('table_schema', $database)
            ->where('table_name', $table)
            ->where('index_name', $indexName)
            ->exists();
    }
};
