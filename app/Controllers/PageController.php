<?php

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Core\Locale;

class PageController extends Controller {
    public function contact(): void {
        $this->view('contact.index');
    }

    public function sendContact(): void {
        if (!Auth::verifyCsrf($this->input('_csrf', ''))) $this->back();
        // TODO: send email
        setFlash('success', "Message envoyé ! Nous vous répondrons dans les plus brefs délais.");
        $this->redirect('/contact');
    }

    public function about(): void {
        $this->view('about.index');
    }

    public function howItWorks(): void {
        $this->view('about.index');
    }

    public function switchLocale(string $locale): void {
        if (!in_array($locale, Locale::SUPPORTED, true)) {
            $this->redirect('/');
        }
        Locale::set($locale);
        $redirect = $this->input('redirect', '');
        if (is_string($redirect) && str_starts_with($redirect, '/') && !str_starts_with($redirect, '//')) {
            $this->redirect($redirect);
        }
        $this->redirect('/');
    }
}
