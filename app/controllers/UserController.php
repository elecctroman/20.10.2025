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
            'user' => Auth::user(),
        ]);
    }

    public function balance()
    {
        if (!Auth::check()) {
            return $this->redirect('/login');
        }

        return $this->view('user/balance', [
            'user' => Auth::user(),
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
            'user' => Auth::user(),
        ]);
    }

    public function sessions()
    {
        if (!Auth::check()) {
            return $this->redirect('/login');
        }

        $user = Auth::user();
        $logs = database()->prepare('SELECT * FROM audit_logs WHERE user_id = :id ORDER BY created_at DESC LIMIT 10');
        $logs->execute(['id' => $user['id']]);

        return $this->view('user/sessions', [
            'user' => $user,
            'logs' => $logs->fetchAll(),
        ]);
    }
}
