<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Models\Blog;

class BlogController extends Controller
{
    public function index()
    {
        $blogModel = new Blog();
        return $this->view('store/blog', ['posts' => $blogModel->allPublished()]);
    }

    public function show(string $slug)
    {
        $blogModel = new Blog();
        $post = $blogModel->findBySlug($slug);
        if (!$post) {
            http_response_code(404);
            return $this->view('errors/404');
        }

        return $this->view('store/post', ['post' => $post]);
    }
}
