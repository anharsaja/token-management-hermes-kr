<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('provider_balances', function (Blueprint $table) {
            $table->id();
            $table->string('provider', 100);
            $table->decimal('balance', 10, 2);
            $table->string('currency', 10)->default('USD');
            $table->date('last_updated_at');
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('provider_balances');
    }
};
