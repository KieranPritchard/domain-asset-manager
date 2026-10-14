<?php
namespace Core;

require_once __DIR__ . "/../Views/components/ui/toast.php";

abstract class Controller
{
    // Gives very controller a view fuction
    protected function view(string $view, array $data = []): void
    {
        require __DIR__ . "/../Views/$view.php";
    }

    protected function redirectWithToast(string $location, string $type, string $title, string $message = ""): void
    {
        \flashToast($type, $title, $message);
        header("Location: $location");
        exit;
    }
}