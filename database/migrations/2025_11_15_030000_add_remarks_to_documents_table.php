<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('documents', function (Blueprint $table) {
            if (! Schema::hasColumn('documents', 'remarks')) {
                $table->text('remarks')->nullable()->after('notes');
            }
        });
    }

    public function down()
    {
        Schema::table('documents', function (Blueprint $table) {
            if (Schema::hasColumn('documents', 'remarks')) {
                $table->dropColumn('remarks');
            }
        });
    }
};
