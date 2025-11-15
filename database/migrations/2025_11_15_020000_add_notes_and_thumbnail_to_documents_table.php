<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('documents', function (Blueprint $table) {
            if (! Schema::hasColumn('documents', 'notes')) {
                $table->text('notes')->nullable()->after('size');
            }

            if (! Schema::hasColumn('documents', 'thumbnail')) {
                $table->string('thumbnail')->nullable()->after('filename');
            }
        });
    }

    public function down()
    {
        Schema::table('documents', function (Blueprint $table) {
            if (Schema::hasColumn('documents', 'notes')) {
                $table->dropColumn('notes');
            }

            if (Schema::hasColumn('documents', 'thumbnail')) {
                $table->dropColumn('thumbnail');
            }
        });
    }
};
