<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use CodeIgniter\HTTP\ResponseInterface;
use App\Models\PriceHistoryModel;

class PriceHistoryController extends BaseController {

    // Read-only endpoint for price history.
    //
    // Note: the "Price History" tab on admin/menus is populated
    // directly by BarbequeController::index() (it needs both the
    // menu list and the history in one page load), so this
    // controller isn't in that request path. It's kept as a small,
    // correct standalone endpoint in case a dedicated
    // /admin/price-history route is added later, and it intentionally
    // no longer duplicates the menu item store()/update() logic that
    // used to live here by mistake — that logic belongs to, and
    // already lives in, BarbequeController.

    public function index() {

        $priceHistory = new PriceHistoryModel();
        $data['price_history_list'] = $priceHistory->getHistoryWithItemNames();

        return view( 'html1/admin/menus.php', $data );

    }

}