<?php
    namespace App\Controllers;

    use Core\Controller;
    use App\Models\Domains;

    // Domains controller
    class DomainsController extends Controller
    {
        // Method to show the view
        public function show(array $parems = []):void
        {
            // Shows the page
            $this->view("domains/show");
        }
    }
?>