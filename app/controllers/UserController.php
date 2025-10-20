<?php
namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Models\Order;
use App\Models\Ticket;

class UserController extends Controller
{
    public function profile()
    {
        if (!Auth::check()) {
            return $this->redirect('/login');
        }

        return $this->view('user/profile', ['user' => Auth::user()]);
    }

    public function orders()
    {
        if (!Auth::check()) {
            return $this->redirect('/login');
        }

        $orderModel = new Order();
        return $this->view('user/orders', [
            'orders' => $orderModel->byUser(Auth::user()['id']),
            'activeOrder' => null,
            'activeOrderItems' => [],
        ]);
    }

    public function tickets()
    {
        if (!Auth::check()) {
            return $this->redirect('/login');
        }

        $ticketModel = new Ticket();
        return $this->view('user/tickets', [
            'tickets' => $ticketModel->byUser(Auth::user()['id']),
        ]);
    }
}
