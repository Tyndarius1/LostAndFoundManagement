<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('claim_requests', function (Blueprint $table) {
            $table->text('staff_notes')->nullable()->after('proof_image_path');

            $table->string('pickup_code')->nullable()->after('staff_notes');
            $table->timestamp('pickup_code_expires_at')->nullable()->after('pickup_code');
        });
    }

    public function down(): void
    {
        Schema::table('claim_requests', function (Blueprint $table) {
            $table->dropColumn([
                'staff_notes',
                'pickup_code',
                'pickup_code_expires_at',
            ]);
        });
    }
};

