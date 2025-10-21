<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Models\Page;

class PageController extends Controller
{
    public function show(string $slug)
    {
        $pageModel = new Page();
        $page = $pageModel->findBySlug($slug);

        if (!$page) {
            http_response_code(404);
            return $this->view('errors/404', [], 'store');
        }

        return $this->view('store/page', [
            'page' => $page,
        ]);
    }
}
