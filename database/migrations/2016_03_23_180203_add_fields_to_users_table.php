<?php
/**
 *
 * @Ananaskelly
 */
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddFieldsToUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->integer('default_game_type_id')->unsigned();
            $table->foreign('default_game_type_id')->references('id')->on('game_types')->onDelete('cascade');
            $table->bool('default_game_private');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['default_game_type_id', 'default_game_private']);
        });
    }
}
