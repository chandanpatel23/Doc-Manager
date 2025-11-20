<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('documents', function (Blueprint $table) {
            // Add indexes for search performance
            $table->index('title');
            $table->index('filename');
            $table->index('document_type');
            // For LIKE queries, these indexes help but full-text search would be better
            // However, basic indexes still improve performance significantly
        });
    }

    public function down()
    {
        Schema::table('documents', function (Blueprint $table) {
            $table->dropIndex(['title']);
            $table->dropIndex(['filename']);
            $table->dropIndex(['document_type']);
        });
    }
};
