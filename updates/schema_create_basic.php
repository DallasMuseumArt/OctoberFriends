<?php

namespace DMA\Friends\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class CreateBasicTables extends Migration
{
    public function up()
    {
        Schema::create('friends_badges', function($table)
        {
            $table->engine = 'InnoDB';
            $table->increments('id');
            $table->integer('wordpress_id');
            $table->string('title');
            $table->text('description')->nullable();
            $table->text('excerpt')->nullable();
            $table->text('congratulations_text')->nullable();
            $table->timestamps();
            $table->boolean('is_published')->default(false);
            $table->boolean('is_archived')->default(false);
            $table->integer('maximum_earnings')->default(0);
            $table->integer('points')->default(0);
            $table->integer('image_id')->nullable();
            $table->string('earned_by');
            $table->boolean('sequential')->default(false);
            $table->boolean('show_earners')->default(false);
            $table->boolean('hidden')->default(false);
            $table->integer('time_between_steps_min')->nullable();
            $table->integer('maximium_time')->nullable();
            $table->timestamp('date_begin')->nullable();
            $table->timestamp('date_end')->nullable();
            
        });

        Schema::create('friends_rewards', function($table) {
            $table->engine = 'InnoDB';
            $table->increments('id');
            $table->integer('wordpress_id');
            $table->string('title');
            $table->text('description')->nullable();
            $table->text('excerpt')->nullable();
            $table->timestamps();
            $table->boolean('is_published')->default(false);
            $table->boolean('is_archived')->default(false);
            $table->integer('points')->default(0);
            $table->integer('image_id')->nullable();
            $table->string('barcode')->nullable();
            $table->timestamp('date_begin')->nullable();
            $table->timestamp('date_end')->nullable();
            $table->integer('days_valid')->nullable();
            $table->integer('inventory')->nullable();
            $table->boolean('hidden')->default(false);
            $table->text('fine_print')->nullable();
            $table->boolean('enable_email')->default(false);
            $table->text('redemption_email')->nullable();
        });

        Schema::create('friends_reward_badge', function($table) {
            $table->engine = 'InnoDB';
            $table->increments('id');
            $table->integer('reward_id');
            $table->integer('badge_id');
            $table->unique(['reward_id', 'badge_id']);
        });

        Schema::create('friends_activities', function($table) {
            $table->engine = 'InnoDB';
            $table->increments('id');
            $table->integer('wordpress_id');
            $table->string('title');
            $table->text('description')->nullable();
            $table->text('excerpt')->nullable();
            $table->timestamps();
            $table->boolean('is_published')->default(false);
            $table->boolean('is_archived')->default(false);
            $table->integer('points')->default(0);
            $table->integer('image_id')->nullable();
            $table->string('activity_code')->nullable();
            $table->integer('activity_lockout')->nullable();
            $table->timestamp('date_begin')->nullable();
            $table->timestamp('date_end')->nullable();
        });

        Schema::create('friends_usermeta', function($table) {
            $table->increments('user_id');
            $table->foreign('user_id')
                ->references('id')
                ->on('users')
                ->onDelete('cascade');
            $table->integer('points');
            $table->string('first_name');
            $table->string('last_name');
            $table->boolean('email_optin');
            $table->smallInteger('current_member');
            $table->integer('current_member_number');
        });

        Schema::create('friends_activity_log', function($table) {
            $table->increments('id');
            $table->integer('user_id');
            $table->string('site_id')->nullable();
            $table->string('action');
            $table->longText('message');
            $table->string('object_type');
            $table->integer('object_id')->nullable();
            $table->string('wordpress_object_id')->default('')->nullable();
            $table->integer('points_earned')->default(0)->nullable();
            $table->integer('total_points')->default(0)->nullable();
            $table->timestamp('timestamp');
            $table->string('timezone');
        });
    }

    public function down()
    {
        Schema::drop('friends_badges');
        Schema::drop('friends_rewards');
        Schema::drop('friends_reward_badge');
        Schema::drop('friends_activities');
        Schema::drop('friends_usermeta');
        Schema::drop('friends_activity_log');
    }
}
