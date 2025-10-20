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
        Auth::check();
        return $this->view('user/profile', ['user' => Auth::user()]);
    }

    public function orders()
    {
        Auth::check();
        $orderModel = new Order();
        return $this->view('user/orders', [
            'orders' => $orderModel->byUser(Auth::user()['id']),
            'activeOrder' => null,
        ]);
    }

    public function tickets()
    {
        Auth::check();
        $ticketModel = new Ticket();
        return $this->view('user/tickets', [
            'tickets' => $ticketModel->byUser(Auth::user()['id']),
        ]);
    }
}
