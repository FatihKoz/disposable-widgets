<?php

namespace Modules\DisposableTools\Http\Controllers;

use App\Contracts\Controller;
// use Carbon\Carbon;
// use Modules\DisposableTools\Models\Disposable_WhazzUp;
use GuzzleHttp\Client as GuzzleClient;
// use Illuminate\Support\Facades\Log;

class DisposableWhazzUpController extends Controller
{

  public function __construct(GuzzleClient $httpClient)
  {
    $this->httpClient = $httpClient;
  }

  // IVAO
  public function ivao()
  {
    // ToDo : Create the method to fetch IVAO data with controller,route and cron
  }

  // VATSIM
  public function vatsim()
  {
    // ToDo : Create the method to fetch VATSIM data with controller,route and cron
  }

  // POSCON
  public function poscon()
  {
    // ToDo : Create the method to fetch POSCON data with controller,route and cron
  }

}
