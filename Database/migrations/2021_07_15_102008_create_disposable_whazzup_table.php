<?php

use App\Contracts\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDisposableWhazzupTable extends Migration
{
  public function up()
  {
    if (!Schema::hasTable('disposable_whazzup')) {
      // Create Disposable WhazzUp Table
      Schema::create('disposable_whazzup', function (Blueprint $table) {
        $table->increments('id');
        $table->string('network', 50)->nullable();
        $table->mediumText('pilots')->nullable();
        $table->mediumText('atcos')->nullable();
        $table->mediumText('observers')->nullable();
        $table->mediumText('servers')->nullable();
        $table->mediumText('voiceservers')->nullable();
        $table->mediumText('rawdata')->nullable();
        $table->timestamps();
        $table->index('id');
        $table->unique('id');
      });
    }
  }
}
