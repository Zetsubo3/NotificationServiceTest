<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('notification_status_logs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('notification_id');
            $table->enum('status', ['queued', 'sent', 'delivered', 'failed']);
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->foreign('notification_id')
                ->references('id')
                ->on('notifications')
                ->onDelete('cascade');

            $table->index('notification_id');
            $table->index('status');
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('notification_status_logs');
    }
};
