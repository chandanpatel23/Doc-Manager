<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('documents', function (Blueprint $table) {
            $table->string('document_type')->nullable()->after('title');
            $table->string('order_no')->nullable()->after('document_type');
            $table->date('order_date')->nullable()->after('order_no');
        });
    }

    public function down()
    {
        Schema::table('documents', function (Blueprint $table) {
            $table->dropColumn(['document_type', 'order_no', 'order_date']);
        });
    }
};
