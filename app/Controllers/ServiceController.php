<?php

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Core\Database;

class ServiceController extends Controller {
    public function index(): void {
        $this->view('services.index');
    }

    public function request(): void {
        if (!Auth::verifyCsrf($this->input('_csrf', ''))) $this->back();

        $db = Database::getInstance();
        $db->insert('service_requests', [
            'user_id'      => Auth::id(),
            'name'         => trim($this->input('name', '')),
            'email'        => trim($this->input('email', '')),
            'phone'        => trim($this->input('phone', '')),
            'company'      => trim($this->input('company', '')),
            'service_type' => $this->input('service_type', 'other'),
            'title'        => trim($this->input('title', '')),
            'description'  => trim($this->input('description', '')),
            'budget_range' => $this->input('budget_range', ''),
            'deadline'     => $this->input('deadline') ?: null,
            'status'       => 'new',
        ]);

        setFlash('success', "Votre demande a été soumise ! Nous vous contacterons sous 24h.");
        $this->redirect('/services');
    }
}
