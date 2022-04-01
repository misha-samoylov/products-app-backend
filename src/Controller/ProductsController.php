<?php
namespace App\Controller;

use Symfony\Component\HttpFoundation\Response;

class ProductsController
{
    public function show(): Response
    {
        return new Response(
            '<html><body>123</body></html>'
        );
    }
}
