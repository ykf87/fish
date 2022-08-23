<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('nickname');
            $table->string('email')->unique();
            $table->string('avatar')->nullable();
            $table->string('phone')->nullable();
            $table->string('password');
            $table->unsignedInteger("singleid")->default(1);
            $table->unsignedInteger("integral")->default(0);
            $table->string('invitation_code')->nullable()->unique();
            $table->string('parent_invite')->nullable();
            $table->string('relation')->nullable();
            $table->smallInteger('status')->default(1);
            $table->ipAddress('register_ip')->comment('注册时的 IP 地址');
            $table->ipAddress('last_ip')->nullable()->comment('最后登陆的 IP 地址');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('users');
    }
}
