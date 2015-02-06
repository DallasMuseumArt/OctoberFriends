<?php namespace DMA\Friends\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;
use RainLab\User\Models\User;

class FixBarcodes extends Migration {

    /** 
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {   
        User::chunk(500, function($users) {
            foreach($users as $user) {
                if (strlen($user->barcode_id) > 10) {
                    $user->barcode_id = substr($user->barcode_id, 0, 9);
                    $user->forceSave();
                }
            }
        });

    }   


    /** 
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {}

}

