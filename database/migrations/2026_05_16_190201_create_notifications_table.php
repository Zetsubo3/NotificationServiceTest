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
        Schema::create('notifications', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('recipient_id');
            $table->enum('channel', ['sms', 'email']);
            $table->enum('priority', ['high', 'low']);
            $table->text('message');
            $table->enum('status', ['queued', 'sent', 'delivered', 'failed'])->default('queued'); // денормализация для быстрой фильтрации
            $table->string('external_id')->nullable();
            $table->unsignedSmallInteger('attempts')->default(0);
            $table->timestamps();

            $table->foreign('recipient_id')
                ->references('id')
                ->on('recipients')
                ->onDelete('cascade');

            $table->index('recipient_id');
            $table->index('status');
            $table->index('priority');
            $table->index('created_at');
            $table->index(['recipient_id', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('notifications');
    }
};
