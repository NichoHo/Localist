<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('role')->default('owner');
            $table->string('google_id')->nullable();
        });

        Schema::create('categories', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('icon')->nullable();
            $table->text('description')->nullable();
            $table->timestamps();
        });

        Schema::create('cities', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('region')->nullable();
            $table->decimal('lat', 10, 6)->nullable();
            $table->decimal('lng', 10, 6)->nullable();
            $table->timestamps();
        });

        Schema::create('plans', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->unsignedInteger('price_monthly')->default(0);
            $table->unsignedInteger('max_photos')->default(3);
            $table->boolean('allows_website')->default(false);
            $table->unsignedInteger('priority_rank')->default(0);
            $table->timestamps();
        });

        Schema::create('businesses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('name');
            $table->string('slug')->unique();
            $table->foreignId('category_id')->constrained();
            $table->foreignId('city_id')->constrained();
            $table->text('description')->nullable();
            $table->string('address')->nullable();
            $table->decimal('lat', 10, 6)->nullable();
            $table->decimal('lng', 10, 6)->nullable();
            $table->string('phone')->nullable();
            $table->string('website')->nullable();
            $table->string('email')->nullable();
            $table->json('hours')->nullable();
            $table->foreignId('plan_id')->constrained();
            $table->string('status')->default('published')->index();
            $table->timestamp('featured_until')->nullable();
            $table->unsignedInteger('views_count')->default(0);
            $table->timestamps();

            $table->index(['city_id', 'category_id']);
        });

        Schema::create('leads', function (Blueprint $table) {
            $table->id();
            $table->foreignId('business_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('email');
            $table->string('phone')->nullable();
            $table->text('message');
            $table->timestamp('read_at')->nullable();
            $table->timestamps();
        });

        Schema::create('media', function (Blueprint $table) {
            $table->id();
            $table->foreignId('business_id')->constrained()->cascadeOnDelete();
            $table->string('path');
            $table->string('alt')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::create('redirects', function (Blueprint $table) {
            $table->id();
            $table->string('from_path')->unique();
            $table->string('to_path');
            $table->unsignedSmallInteger('status_code')->default(301);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('redirects');
        Schema::dropIfExists('media');
        Schema::dropIfExists('leads');
        Schema::dropIfExists('businesses');
        Schema::dropIfExists('plans');
        Schema::dropIfExists('cities');
        Schema::dropIfExists('categories');
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['role', 'google_id']);
        });
    }
};
