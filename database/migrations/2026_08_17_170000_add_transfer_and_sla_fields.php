<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tickets', function (Blueprint $table) {
            $table->foreignId('transferred_from_category_id')->nullable()->constrained('service_categories')->nullOnDelete();
            $table->string('transfer_reason')->nullable();
        });

        Schema::table('locations', function (Blueprint $table) {
            $table->text('running_text')->nullable();
            $table->integer('sla_waiting_threshold_minutes')->default(15);
        });
    }

    public function down(): void
    {
        Schema::table('tickets', function (Blueprint $table) {
            $table->dropForeign(['transferred_from_category_id']);
            $table->dropColumn(['transferred_from_category_id', 'transfer_reason']);
        });

        Schema::table('locations', function (Blueprint $table) {
            $table->dropColumn(['running_text', 'sla_waiting_threshold_minutes']);
        });
    }
};
