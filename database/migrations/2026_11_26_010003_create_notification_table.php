<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('notification', function (Blueprint $table) {
            $table->increments('id_notification');
            $table->string('type');
            $table->string('notifiable_type', 100);
            $table->unsignedInteger('notifiable_id');
            $table->json('data');
            $table->timestamp('lu_at')->nullable();
            $table->timestamps();

            $table->index(['notifiable_type', 'notifiable_id']);
            $table->index('lu_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('notification');
    }
};
