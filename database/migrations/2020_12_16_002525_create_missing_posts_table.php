<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMissingPostsTable extends Migration
{
  /**
   * Run the migrations.
   *
   * @return void
   */
  public function up()
  {
    Schema::create('missing_posts', function (Blueprint $table) {
      $table->id();
      $table->integer('user_id');
      $table->integer('activity_id');
      $table->enum('missing_type', ['Missing_Person', 'Run_Away', 'Endanger_Run_Away', 'Family_Abduction', 'Medical_Fragile_Missing']);
      $table->enum('badge_awarded', ['Pending', 'Awarded']);
      $table->string('duo_location');
      $table->boolean('is_draft');
      $table->boolean('has_tattoo');
      $table->string('asset_path1');
      $table->string('asset_path2');
      $table->string('asset_path3');
      $table->string('asset_path4');
      $table->string('asset_path5');
      $table->string('fullname');
      $table->string('aka');
      $table->timestamp('dob');
      $table->float('height_ft');
      $table->float('height_cm');
      $table->float('weight_kg');
      $table->float('weight_lb');
      $table->enum('sex', ['Male', 'Female']);
      $table->enum('hair', ['Black', 'Blond', 'Wave', 'Yellow', 'White']);
      $table->enum('race', ['Yellow', 'White', 'Black']);
      $table->enum('eye', ['Black', 'Blue', 'Brown', 'Yellow']);
      $table->boolean('medical_condition');
      $table->timestamp('missing_since');
      $table->string('missing_location_zip');
      $table->string('missing_location_street');
      $table->string('missing_location_city');
      $table->string('missing_location_country');
      $table->string('missing_location_state');
      $table->text('circumstance');
      $table->string('contact_email');
      $table->string('contact_phone_number1');
      $table->string('contact_phone_number2');
      $table->string('verification_report_path');
      $table->string('verification_groupchat_link');
      $table->string('company_name');
      $table->string('company_image_path');
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
    Schema::dropIfExists('missing_posts');
  }
}
