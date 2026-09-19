<?php
namespace Core;

abstract class Controller
{
    // Gives very controller a view fuction
    protected function view(string $view, array $data = []): void
    {
        extract($data);
        require __DIR__ . "/../Views/$view.php";
    }
}