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
        Schema::create('registration_answers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('registration_id')->constrained()->cascadeOnDelete();
            $table->foreignId('event_question_id')->nullable()->constrained()->nullOnDelete();
            $table->string('question_label_snapshot');
            $table->string('question_key_snapshot');
            $table->string('question_type_snapshot');
            $table->longText('answer_text')->nullable();
            $table->json('answer_json')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('registration_answers');
    }
};
