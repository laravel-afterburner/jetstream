<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('roles', 'show_in_directory_council')) {
            Schema::table('roles', function (Blueprint $table) {
                $table->boolean('show_in_directory_council')->default(false)->after('max_members');
            });
        }

        DB::table('roles')
            ->whereIn('slug', ['president', 'vice_president', 'treasurer', 'secretary', 'council_member'])
            ->update(['show_in_directory_council' => true]);
    }

    public function down(): void
    {
        Schema::table('roles', function (Blueprint $table) {
            $table->dropColumn('show_in_directory_council');
        });
    }
};
